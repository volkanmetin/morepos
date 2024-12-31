<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Enums\PaymentMethod;
use App\Enums\SettingKey;
use App\Services\SettingService;
use Illuminate\Validation\Rule;
use App\Enums\SaleStatus;

class SaleController extends Controller
{
    protected $couponService;
    protected $settingService;

    public function __construct(CouponService $couponService, SettingService $settingService)
    {
        $this->couponService = $couponService;
        $this->settingService = $settingService;
    }

    public function index()
    {
        $sales = Sale::with(['customer', 'items.product', 'items.variant'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Sales/Index', [
            'sales' => $sales
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'uuid' => 'required|string|exists:sales,uuid',
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => ['required', 'string', Rule::in(PaymentMethod::values())],
            'coupon_id' => 'nullable|exists:coupons,id',
            'manual_discount' => 'nullable|array',
            'manual_discount.type' => ['required_with:manual_discount', Rule::in(['percentage', 'fixed'])],
            'manual_discount.amount' => 'required_with:manual_discount|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'discount_amount' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Bekleyen satışı bul
            $sale = Sale::where('uuid', $validated['uuid'])
                ->where('status', SaleStatus::PENDING)
                ->firstOrFail();

            // Stok kontrolü
            foreach ($validated['items'] as $item) {
                if ($item['variant_id']) {
                    $variant = ProductVariant::with('stocks')->find($item['variant_id']);
                    $totalStock = $variant->stocks->sum('quantity');
                    
                    if ($totalStock < $item['quantity']) {
                        throw new \Exception("Yetersiz stok: {$variant->name}");
                    }
                } else {
                    $product = Product::with(['variants.stocks'])->find($item['product_id']);
                    $totalStock = $product->variants->sum(function ($variant) {
                        return $variant->stocks->sum('quantity');
                    });
                    
                    if ($totalStock < $item['quantity']) {
                        throw new \Exception("Yetersiz stok: {$product->name}");
                    }
                }
            }

            // Satışı güncelle
            $sale->update([
                'payment_method' => $validated['payment_method'],
                'coupon_id' => $validated['coupon_id'] ?? null,
                'manual_discount' => $validated['manual_discount'] ?? null,
                'subtotal' => $validated['subtotal'],
                'tax_amount' => $validated['tax_amount'],
                'discount_amount' => $validated['discount_amount'],
                'total' => $validated['total'],
                'status' => SaleStatus::COMPLETED,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Önceki satış detaylarını sil
            $sale->items()->delete();

            // Yeni satış detaylarını oluştur
            foreach ($validated['items'] as $item) {
                $product = Product::with(['category', 'brand'])->find($item['product_id']);
                $variant = $item['variant_id'] ? ProductVariant::with(['attributeValues.attributeGroup'])->find($item['variant_id']) : null;
                
                // Ürün ve varyant verilerinin anlık görüntüsünü al
                $productSnapshot = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category?->name,
                    'brand' => $product->brand?->name,
                    'sale_price' => $item['sale_price'] ?? 0,
                    'discounted_price' => $item['discounted_price'] ?? 0,
                ];

                $variantSnapshot = null;
                if ($variant) {
                    $variantSnapshot = [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'sku' => $variant->sku,
                        'barcode' => $variant->barcode,
                        'sale_price' => $item['sale_price'] ?? 0,
                        'discounted_price' => $item['discounted_price'] ?? 0,
                        'attributes' => $variant->attributeValues->map(function ($av) {
                            return [
                                'group' => $av->attributeGroup->name,
                                'value' => $av->value
                            ];
                        })->toArray()
                    ];
                }

                // Satış detayını oluştur
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                    'product_snapshot' => $productSnapshot ?? null,
                    'variant_snapshot' => $variantSnapshot ?? null
                ]);

                // Stok güncelle
                if ($item['variant_id']) {
                    $variant = ProductVariant::find($item['variant_id']);
                    $stock = $variant->stocks->first();
                    if ($stock) {
                        $stock->decrement('quantity', $item['quantity']);
                    }
                } else {
                    $product = Product::find($item['product_id']);
                    $variant = $product->variants->first();
                    if ($variant) {
                        $stock = $variant->stocks->first();
                        if ($stock) {
                            $stock->decrement('quantity', $item['quantity']);
                        }
                    }
                }
            }

            // Kupon kullanımını güncelle
            if (isset($validated['coupon_id'])) {
                $coupon = Coupon::find($validated['coupon_id']);
                if ($coupon) {
                    $this->couponService->markAsUsed($coupon, $sale->id);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Satış başarıyla tamamlandı',
                'sale' => $sale->load(['customer', 'items.product', 'items.variant'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'items.product', 'items.variant']);
        
        return Inertia::render('Sales/Show', [
            'sale' => $sale
        ]);
    }

    public function updateCart(Request $request)
    {
        $validated = $request->validate([
            'uuid' => 'required|string|exists:sales,uuid',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
            'payment_method' => ['required', 'string', Rule::in(PaymentMethod::values())],
            'coupon_id' => 'nullable|exists:coupons,id',
            'manual_discount' => 'nullable|array',
            'manual_discount.type' => ['required_with:manual_discount', Rule::in(['percentage', 'fixed'])],
            'manual_discount.amount' => 'required_with:manual_discount|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'discount_amount' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $sale = Sale::where('uuid', $validated['uuid'])
                ->where('status', SaleStatus::PENDING)
                ->firstOrFail();

            // Satışı güncelle
            $sale->update([
                'payment_method' => $validated['payment_method'],
                'coupon_id' => $validated['coupon_id'] ?? null,
                'manual_discount' => $validated['manual_discount'] ?? null,
                'subtotal' => $validated['subtotal'],
                'tax_amount' => $validated['tax_amount'],
                'discount_amount' => $validated['discount_amount'],
                'total' => $validated['total']
            ]);

            // Önceki satış detaylarını sil
            $sale->items()->delete();

            // Yeni satış detaylarını oluştur
            foreach ($validated['items'] as $item) {
                $product = Product::with(['category', 'brand'])->find($item['product_id']);
                $variant = $item['variant_id'] ? ProductVariant::with(['attributeValues.attributeGroup'])->find($item['variant_id']) : null;
                
                // Ürün ve varyant verilerinin anlık görüntüsünü al
                $productSnapshot = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category?->name,
                    'brand' => $product->brand?->name,
                    'sale_price' => $product->sale_price,
                    'discounted_price' => $product->discounted_price,
                ];

                $variantSnapshot = null;
                if ($variant) {
                    $variantSnapshot = [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'sku' => $variant->sku,
                        'barcode' => $variant->barcode,
                        'sale_price' => $variant->sale_price,
                        'discounted_price' => $variant->discounted_price,
                        'attributes' => $variant->attributeValues->map(function ($av) {
                            return [
                                'group' => $av->attributeGroup->name,
                                'value' => $av->value
                            ];
                        })->toArray()
                    ];
                }

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['total'],
                    'product_snapshot' => $productSnapshot ?? null,
                    'variant_snapshot' => $variantSnapshot ?? null
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sepet güncellendi',
                'sale' => $sale->load(['items.product', 'items.variant'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
} 
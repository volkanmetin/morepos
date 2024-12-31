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
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
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
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => ['required', 'string', Rule::in(PaymentMethod::values())],
            'coupon_id' => 'nullable|exists:coupons,id',
            'manual_discount' => 'nullable|array',
            'subtotal' => 'required|numeric|min:0',
            'discount_amount' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

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

            // Satış oluştur
            $sale = Sale::create([
                'tenant_id' => auth()->user()->tenant_id,
                'customer_id' => $validated['customer_id'],
                'payment_method' => $validated['payment_method'],
                'coupon_id' => $validated['coupon_id'] ?? null,
                'manual_discount' => $validated['manual_discount'] ?? null,
                'subtotal' => $validated['subtotal'],
                'tax_rate' => 18, // KDV oranı
                'tax_amount' => $validated['tax_amount'],
                'discount_amount' => $validated['discount_amount'],
                'total' => $validated['total'],
                'status' => 'completed'
            ]);

            // Satış detaylarını oluştur
            foreach ($validated['items'] as $item) {
                $product = Product::with(['category', 'brand'])->find($item['product_id']);
                $variant = $item['variant_id'] ? ProductVariant::with(['attributeValues.attributeGroup'])->find($item['variant_id']) : null;
                
                // Ürün ve varyant verilerinin anlık görüntüsünü al
                $productSnapshot = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category?->name,
                    'brand' => $product->brand?->name,
                ];

                $variantSnapshot = $variant ? [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'attributes' => $variant->attributeValues->map(function ($av) {
                        return [
                            'group' => $av->attributeGroup->name,
                            'value' => $av->value
                        ];
                    })
                ] : null;

                // Satış detayını oluştur
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                    'product_snapshot' => $productSnapshot,
                    'variant_snapshot' => $variantSnapshot
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
            if ($validated['coupon_id']) {
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
} 
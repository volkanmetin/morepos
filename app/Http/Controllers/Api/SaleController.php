<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Enums\SaleStatus;
use App\Enums\SettingKey;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use App\Http\Resources\SaleResource;

class SaleController extends Controller
{
    protected $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function getPendingSales()
    {
        try {

            $tenant = Tenant::where('database', 'more-test')->firstOrFail();
            $tenant->makeCurrent();
            
            $sales = Sale::with(['items.product', 'items.variant'])
                ->where('status', SaleStatus::PENDING)
                ->latest()
                ->limit(20)
                ->get();

            ray(SaleResource::collection($sales));

            return response()->json([
                'success' => true,
                'sales' => SaleResource::collection($sales)
            ]);

        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function addItem(Request $request, $uuid)
    {
        ray($uuid, $request->all());
        $validated = $request->validate([
            'barcode' => 'required|string'
        ]);

        DB::beginTransaction();
        try {

            $tenant = Tenant::where('database', 'more-test')->firstOrFail();
            $tenant->makeCurrent();
            

            // Satışı bul ve durumunu kontrol et
            $sale = Sale::where('uuid', $uuid)
                ->where('status', SaleStatus::PENDING)
                ->firstOrFail();

            // Barkoda göre ürün varyantını bul
            $variant = ProductVariant::where('barcode', $validated['barcode'])->first();
            
            if (!$variant) {
                throw new \Exception('Ürün bulunamadı');
            }

            //$product = Product::findOrFail($variant->product_id);

            $product = Product::with(['category', 'brand'])->findOrFail($variant->product_id);
                
                // Ürün ve varyant verilerinin anlık görüntüsünü al
                $productSnapshot = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category?->name,
                    'brand' => $product->brand?->name,
                    'sale_price' => $product->sale_price,
                    'discounted_price' => $product->discounted_price,
                    'stock' => $product->variants->sum(function ($variant) {
                        return $variant->stocks->sum('quantity');
                    })
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
                        'stocks' => $variant->stocks->map(function ($stock) {
                            return [
                                'warehouse_id' => $stock->warehouse_id,
                                'quantity' => $stock->quantity
                            ];
                        })->toArray(),
                        'attributes' => $variant->attributeValues->map(function ($av) {
                            return [
                                'group' => $av->attributeGroup->name,
                                'value' => $av->value
                            ];
                        })->toArray()
                    ];
                }

            // Mevcut sepet öğesini kontrol et
            $saleItem = SaleItem::where('sale_id', $sale->id)
                ->where('product_id', $product->id)
                ->where('variant_id', $variant->id)
                ->first();

            $price = $variant->sale_price ?? $product->sale_price;

            if ($saleItem) {
                // Mevcut öğeyi güncelle
                $saleItem->update([
                    'quantity' => $saleItem->quantity + 1,
                    'total' => ($saleItem->quantity + 1) * $price,
                    'product_snapshot' => $productSnapshot ?? null,
                    'variant_snapshot' => $variantSnapshot ?? null
                ]);
            } else {
                // Yeni öğe ekle
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'quantity' => 1,
                    'price' => $price,
                    'total' => $price,
                    'product_snapshot' => $productSnapshot ?? null,
                    'variant_snapshot' => $variantSnapshot ?? null
                ]);
            }

            // Satış toplamlarını güncelle
            $subtotal = $sale->items->sum('total');
            $taxRate = $this->settingService->get(SettingKey::TAX_RATE, 0);
            $taxAmount = $subtotal * ($taxRate / 100);

            $sale->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ürün sepete eklendi',
                'sale' => $sale->load(['items.product', 'items.variant'])
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
} 
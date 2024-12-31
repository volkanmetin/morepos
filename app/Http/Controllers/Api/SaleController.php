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
            $sales = Sale::with(['items.product', 'items.variant'])
                ->where('status', SaleStatus::PENDING)
                ->latest()
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'sales' => $sales
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'uuid' => 'required|string|exists:sales,uuid',
            'barcode' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            // Satışı bul ve durumunu kontrol et
            $sale = Sale::where('uuid', $validated['uuid'])
                ->where('status', SaleStatus::PENDING)
                ->firstOrFail();

            // Barkoda göre ürün varyantını bul
            $variant = ProductVariant::where('barcode', $validated['barcode'])->first();
            
            if (!$variant) {
                throw new \Exception('Ürün bulunamadı');
            }

            $product = Product::findOrFail($variant->product_id);

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
                    'total' => ($saleItem->quantity + 1) * $price
                ]);
            } else {
                // Yeni öğe ekle
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'quantity' => 1,
                    'price' => $price,
                    'total' => $price
                ]);
            }

            // Satış toplamlarını güncelle
            $subtotal = $sale->items->sum('total');
            $taxRate = $this->settingService->get(SettingKey::TAX_RATE, 0);
            $taxAmount = $subtotal * ($taxRate / 100);

            $sale->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ürün sepete eklendi',
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
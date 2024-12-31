<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

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
            'payment_method' => 'required|string',
            'coupon_id' => 'nullable|exists:coupons,id',
            'manual_discount' => 'nullable|array',
            'subtotal' => 'required|numeric|min:0',
            'discount_amount' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        ray($validated);

        try {
            DB::beginTransaction();

            $sale = Sale::create([
                'customer_id' => $validated['customer_id'],
                'payment_method' => $validated['payment_method'],
                'coupon_id' => $validated['coupon_id'] ?? null,
                'manual_discount' => $validated['manual_discount'] ?? null,
                'subtotal' => $validated['subtotal'],
                'discount_amount' => $validated['discount_amount'],
                'tax_amount' => $validated['tax_amount'],
                'total' => $validated['total'],
            ]);

            foreach ($validated['items'] as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                ]);
            }

            if ($validated['coupon_id']) {
                $coupon = Coupon::find($validated['coupon_id']);
                if ($coupon) {
                    $this->couponService->markAsUsed($coupon);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'sale' => $sale->load(['customer', 'items.product', 'items.variant'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
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
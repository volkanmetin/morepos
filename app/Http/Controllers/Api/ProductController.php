<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\Tenant;

class ProductController extends Controller
{
    public function findByBarcode(string $barcode)
    {
        $tenant = Tenant::where('database', 'more-test')->firstOrFail();
        $tenant->makeCurrent();

        $variant = ProductVariant::where('barcode', $barcode)->with('product')->firstOrFail();

        ray([
            'product' => $variant->product,
            'variant' => $variant,
        ]);

        return response()->json([
            'product' => $variant->product,
            'variant' => $variant,
        ]);
    }
}

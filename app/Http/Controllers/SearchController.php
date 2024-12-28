<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Warehouse;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $products = Product::query()
            ->where('name', 'like', '%' . $request->input('query') . '%')
            ->orWhere('id', 'like', '' . $request->input('query') . '%')
            ->with(['variants' => function ($query) {
                $query->with(['stocks']);
            }])
            ->with(['category'])
            ->get()
            ->map(function ($product) {
                $product->stock = $product->variants->sum(function ($variant) {
                    return $variant->stocks->sum('quantity');
                });
                $product->image = $product->image;
                return $product;
            });

        return response()->json($products);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

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

    public function products(Request $request)
    {
        ray($request->all());
        $query = Product::query()
            ->with(['variants.attributeValues.attributeGroup', 'variants.stocks.warehouse']);

        if ($request->has('query') && !empty($request->query('query'))) {
            $searchTerm = $request->query('query');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->has('category') && !empty($request->query('category'))) {
            $query->where('category_id', $request->query('category'));
        }

        if ($request->has('brand') && !empty($request->query('brand'))) {
            $query->where('brand_id', $request->query('brand'));
        }

        $products = $query
            ->take(50)
            ->latest()
            ->get()
            ->map(function ($product) {
                $product->image = $product->image;

                if ($product->variants->count() > 0) {
                    $firstVariant = $product->variants->first();
                    $product->sale_price = $firstVariant->sale_price && $firstVariant->sale_price > 0 ? $firstVariant->sale_price : $product->sale_price;
                    $product->discounted_price = $firstVariant->discounted_price && $firstVariant->discounted_price > 0 ? $firstVariant->discounted_price : $product->discounted_price;
                }

                $product->stock = $product->variants->sum(function ($variant) {
                    return $variant->stocks->sum('quantity');
                });

                $product->variants = $product->variants->map(function ($variant) use ($product) {
                    $variantPrice = $variant->discounted_price && $variant->discounted_price > 0 ? $variant->discounted_price : $variant->sale_price;
                    $variant->price = $variantPrice > 0 ? $variantPrice : ($product->discounted_price && $product->discounted_price > 0 ? $product->discounted_price : $product->sale_price);
                    return $variant;
                });

                return $product;
            });

        ray($products);

        return response()->json([
            'data' => $products,
            'categories' => Category::select('id', 'name')->get(),
            'brands' => Brand::select('id', 'name')->get(),
        ]);
    }
}

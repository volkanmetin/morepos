<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
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

        $products = $query->take(50)->get();

        return response()->json([
            'data' => $products,
            'categories' => Category::select('id', 'name')->get(),
            'brands' => Brand::select('id', 'name')->get(),
        ]);
    }
}

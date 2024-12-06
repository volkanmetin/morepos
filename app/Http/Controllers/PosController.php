<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index()
    {
        return inertia('Pos/Index');
    }

    public function getProducts(Request $request)
    {
        $query = Product::query();

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('name', 'like', "%{$searchTerm}%")
                ->orWhere('description', 'like', "%{$searchTerm}%");
        }

        if ($request->has('category') && $request->input('category') !== '') {
            $query->where('category_id', $request->input('category'));
        }

        if ($request->has('vendor') && $request->input('vendor') !== '') {
            $query->where('vendor_id', $request->input('vendor'));
        }

        if ($request->has('brand') && $request->input('brand') !== '') {
            $query->where('brand_id', $request->input('brand'));
        }

        $query->with([
            'variants.attributeValues.attributeGroup' => function ($query) {
                $query->select('id', 'name');
            },
            'variants.stocks' => function ($query) {
                $query->select('id', 'product_variant_id', 'quantity', 'warehouse_id')
                    ->with('warehouse:id,name');
            },
        ]);

        $products = $query->paginate(20);

        return response()->json([
            'data' => ProductResource::collection($products),
            'categories' => Category::all()->map(function ($category) {
                return [
                    'value' => $category->id,
                    'label' => $category->name,
                ];
            }),
            'vendors' => Vendor::all()->map(function ($vendor) {
                return [
                    'value' => $vendor->id,
                    'label' => $vendor->name,
                ];
            }),
            'brands' => Brand::all()->map(function ($brand) {
                return [
                    'value' => $brand->id,
                    'label' => $brand->name,
                ];
            }),
        ]);
    }
}

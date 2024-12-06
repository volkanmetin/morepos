<?php

namespace App\Http\Controllers;

use App\Datatable\ProductDatatable;
use App\Http\Requests\StoreProductRequest;
use App\Models\AttributeGroup;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Vendor;
use App\Models\Warehouse;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(ProductDatatable $datatable): Response
    {
        $attributeGroups = AttributeGroup::with('values')->get();
        $categories = Category::all();
        $vendors = Vendor::all();
        $brands = Brand::all();

        return inertia('Product/Index', [
            'columns' => $datatable->html()->getColumns(),
            'attributeGroups' => $attributeGroups,
            'categories' => $categories,
            'vendors' => $vendors,
            'brands' => $brands,
        ]);
    }

    public function getData(ProductDatatable $datatable, Request $request)
    {
        return $datatable->ajax();
    }

    public function show($id)
    {
        $product = Product::where('id', $id)
            ->with(['category', 'vendor', 'brand', 'media'])
            ->firstOrFail();

        $variants = $product->variants()
            ->with([
                'attributeValues.attributeGroup',
                'stocks.warehouse',
                'media'
            ])
            ->get();

        return Inertia::render('Product/Show', [
            'product' => $product,
            'warehouses' => Warehouse::all(),
            'variants' => $variants
        ]);
    }

    public function create()
    {
        $variantOptions = AttributeGroup::select('id', 'name', 'type')->with([
            'values' => function ($query) {
                $query->select('id', 'attribute_group_id', 'value', 'code', 'color_code', 'sort');
            },
        ])->get();
        //return $variantOptions;
        $categories = Category::all();
        $warehouses = Warehouse::all();
        $vendors = Vendor::all();
        $brands = Brand::all();

        return inertia('Product/Create', [
            'categories' => $categories,
            'warehouses' => $warehouses,
            'variantOptions' => $variantOptions,
            'vendors' => $vendors,
            'brands' => $brands,
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        ray($request->all());

        $product = Product::create($request->only('name', 'category_id', 'purchase_price', 'sale_price', 'discounted_price', 'vendor_id', 'brand_id'));

        foreach ($request->variants as $variantData) {

            $variant = $product->variants()->create([
                'purchase_price' => ($variantData['purchase_price'] && $variantData['purchase_price'] > 0) ? $variantData['purchase_price'] : $product->purchase_price,
                'sale_price' => ($variantData['sale_price'] && $variantData['sale_price'] > 0) ? $variantData['sale_price'] : $product->sale_price,
                'discounted_price' => ($variantData['discounted_price'] && $variantData['discounted_price'] > 0) ? $variantData['discounted_price'] : $product->discounted_price,
            ]);

            // Varyanta resim ekleme
            if (isset($variantData['image']) && $variantData['image']) {
                $media = $variant->addMedia(storage_path('app/public/temp').'/'.$variantData['image'])
                    ->toMediaCollection('images');

                $media->copy($product, 'images');
            }

            // Varyant özelliklerini ekle
            foreach ($variantData['variant'] as $id => $value) {
                $attrValue = AttributeValue::where('id', $id)->first();
                if ($attrValue) {
                    $variant->attributeValues()->attach($attrValue->id);
                }
            }

            $variant->generateSku();
            $variant->generateBarcode();
            $variant->save();

            if (isset($variantData['stock'])) {
                foreach ($variantData['stock'] as $warehouseId => $stock) {
                    $warehouse = Warehouse::find($warehouseId);
                    Stock::create([
                        'product_variant_id' => $variant->id,
                        'warehouse_id' => $warehouse->id,
                        'quantity' => $stock,
                    ]);
                }
            }
        }

        return response()->json(['message' => 'Product created successfully']);
    }

    public function update(Request $request)
    {
        Product::find(request('id'))->update($request->validate([
            'name' => 'required',
            'category_id' => 'required',
        ]));
    }

    public function destroy()
    {
        DB::beginTransaction();
        try {
            ProductVariant::where('product_id', request('id'))->get()->pluck('id')->each(function ($id) {
                Stock::where('product_variant_id', $id)->delete();
            });
            ProductVariant::where('product_id', request('id'))->delete();
            Product::destroy(request('id'));
            DB::commit();

            return response()->json(['message' => 'Product deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return response()->json(['message' => 'Product deleted failed']);
        }
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:20480',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $tempPath = storage_path('app/public/temp');

            if (! file_exists($tempPath)) {
                mkdir($tempPath, 0777, true);
            }

            $image->move($tempPath, $imageName);

            $imageUrl = asset('storage/temp/'.$imageName);

            return response()->json([
                'message' => 'Görsel başarıyla yüklendi',
                'name' => $imageName,
                'url' => $imageUrl,
            ]);
        }

        return response()->json(['message' => 'Görsel yüklenirken bir hata oluştu'], 400);
    }
}

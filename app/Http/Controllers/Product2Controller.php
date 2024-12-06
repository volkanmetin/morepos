<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\AttributeGroup;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Inertia\Inertia;

class Product2Controller extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index()
    {
        $products = Product::with(['category', 'variants.attributeValues.attributeGroup'])->get();
        $categories = Category::all();
        $attributeGroups = AttributeGroup::with('values')->get();

        return Inertia::render('Products/Index', [
            'initialProducts' => $products,
            'categories' => $categories,
            'attributeGroups' => $attributeGroups,
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->except('variants'));

        foreach ($request->variants as $variantData) {
            $variant = $product->variants()->create([
                'sku' => $variantData['sku'],
                'price' => $variantData['price'],
            ]);

            foreach ($variantData['attributes'] as $groupId => $valueId) {
                $variant->attributeValues()->attach($valueId);
            }
        }

        return redirect()->route('products.index')
            ->with('message', 'Ürün başarıyla oluşturuldu.');
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->except('variants'));

        // Mevcut varyantları sil
        $product->variants()->delete();

        // Yeni varyantları ekle
        foreach ($request->variants as $variantData) {
            $variant = $product->variants()->create([
                'sku' => $variantData['sku'],
                'price' => $variantData['price'],
            ]);

            foreach ($variantData['attributes'] as $groupId => $valueId) {
                $variant->attributeValues()->attach($valueId);
            }
        }

        return redirect()->route('products.index')
            ->with('message', 'Ürün başarıyla güncellendi.');
    }

    public function destroy(Product $product)
    {
        $product->variants()->delete();
        $product->delete();

        return redirect()->route('products.index')
            ->with('message', 'Ürün başarıyla silindi.');
    }

    public function show($id)
    {
        $detailedProduct = $this->productService->getProductWithVariantsAndStock($id);

        return $detailedProduct;
    }
}

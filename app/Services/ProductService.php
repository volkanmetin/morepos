<?php

namespace App\Services;

use App\Models\AttributeGroup;
use App\Models\AttributeValue;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    private string $cachePrefix = 'product_';

    private int $cacheDuration = 3600; // 1 saat

    public function getById(int $id): Product
    {
        $cacheKey = $this->cachePrefix.$id;

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($id) {
            return Product::findOrFail($id);
        });
    }

    public function delete(int $id): bool
    {
        $product = $this->getById($id);
        $this->clearProductCache($product);

        ProductVariant::where('product_id', $product->id)->get()->pluck('id')->each(function ($id) {
            Stock::where('product_variant_id', $id)->delete();
        });
        ProductVariant::where('product_id', $product->id)->delete();

        return $product->delete();
    }

    public function addProduct(array $data): Product
    {
        $product = Product::create($data);
        $this->cacheProduct($product);

        return $product;
    }

    public function getProductByColumn(string $column, $value): Product
    {
        $cacheKey = $this->cachePrefix.$column.'_'.$value;

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($column, $value) {
            return Product::where($column, $value)->firstOrFail();
        });
    }

    public function updateProduct(int $id, array $data): Product
    {
        $product = $this->getProductById($id);
        $product->update($data);

        $this->clearProductCache($product);
        $this->cacheProduct($product);

        return $product;
    }

    public function getProductWithVariantsAndStock(int $id): array
    {
        $cacheKey = $this->cachePrefix.'detailed_'.$id;

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($id) {
            $product = Product::with(['variants.attributeValues.attributeGroup', 'variants.stocks.warehouse'])
                ->findOrFail($id);

            $detailedProduct = $product->toArray();
            $detailedProduct['variants'] = $this->formatVariantsWithStock($product->variants);

            return $detailedProduct;
        });
    }

    private function formatVariantsWithStock(Collection $variants): array
    {
        return $variants->map(function ($variant) {
            $formattedVariant = [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'barcode' => $variant->barcode,
                'price' => $variant->price,
                'attributes' => $variant->attributeValues->mapWithKeys(function ($attributeValue) {
                    return [$attributeValue->attributeGroup->name => $attributeValue->value];
                }),
                'stocks' => $variant->stocks->map(function ($stock) {
                    return [
                        'warehouse_name' => $stock->warehouse->name,
                        'quantity' => $stock->quantity,
                    ];
                }),
            ];

            return $formattedVariant;
        })->toArray();
    }

    public function getAllAttributeGroupsAndValues(): array
    {
        $cacheKey = $this->cachePrefix.'all_attributes';

        return Cache::remember($cacheKey, $this->cacheDuration, function () {
            return AttributeGroup::with('values')->get()->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'values' => $group->values->pluck('value', 'id'),
                ];
            })->toArray();
        });
    }

    public function addProductWithVariants(array $productData, array $variants): Product
    {
        $product = Product::create($productData);

        foreach ($variants as $variantData) {
            $variant = $product->variants()->create([
                'sku' => $variantData['sku'],
                'price' => $variantData['price'],
            ]);

            if (isset($variantData['attributes'])) {
                $attributeValueIds = AttributeValue::whereIn('id', $variantData['attributes'])->pluck('id');
                $variant->attributeValues()->attach($attributeValueIds);
            }

            if (isset($variantData['stocks'])) {
                foreach ($variantData['stocks'] as $stockData) {
                    $variant->stocks()->create($stockData);
                }
            }
        }

        $this->clearProductCache($product);
        $this->cacheProduct($product);

        return $product;
    }

    private function cacheProduct(Product $product): void
    {
        $cacheKey = $this->cachePrefix.$product->id;
        Cache::put($cacheKey, $product, $this->cacheDuration);
    }

    private function clearProductCache(Product $product): void
    {
        $cacheKey = $this->cachePrefix.$product->id;
        Cache::forget($cacheKey);

        $this->clearDetailedProductCache($product);

        foreach ($product->getAttributes() as $column => $value) {
            $columnCacheKey = $this->cachePrefix.$column.'_'.$value;
            Cache::forget($columnCacheKey);
        }
    }

    private function clearDetailedProductCache(Product $product): void
    {
        $cacheKey = $this->cachePrefix.'detailed_'.$product->id;
        Cache::forget($cacheKey);
    }
}

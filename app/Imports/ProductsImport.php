<?php

namespace App\Imports;

use App\Models\AttributeGroup;
use App\Models\AttributeValue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Product;
use Illuminate\Support\Arr;
use App\Models\Category;
use App\Models\ProductVariantAttribute;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Jobs\CopyVariantImageFromRemoteToLocal;

class ProductsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $products = [];
        foreach ($rows as $row) {
            
            //dd($row);
            //dump($row);

            $products[$row['urun_grup_id']]['slug'] = $row['slug'];
            $products[$row['urun_grup_id']]['name'] = $row['isim'];
            $products[$row['urun_grup_id']]['description'] = $row['aciklama'];
            $products[$row['urun_grup_id']]['purchase_price'] = $row['alis_fiyati'] ?? 0;
            $products[$row['urun_grup_id']]['sale_price'] = $row['satis_fiyati'] ?? 0;
            $products[$row['urun_grup_id']]['discounted_price'] = $row['indirimli_fiyati'] ?? 0;
            $products[$row['urun_grup_id']]['tags'] = $row['etiketler'] ? explode(';', $row['etiketler']) : [];
            $products[$row['urun_grup_id']]['categories'] = $row['kategoriler'] ? explode('>', $row['kategoriler']) : [];
            $products[$row['urun_grup_id']]['category'] = Arr::last($products[$row['urun_grup_id']]['categories']);

            $variants = [];
            foreach (range(1, 10) as $index) {
                if (isset($row['varyant_tip_'.$index]) && $row['varyant_tip_'.$index] && $row['varyant_tip_'.$index] != '') {
                    $varyant = $row['varyant_tip_'.$index];

                    $attrGroup = AttributeGroup::where('name', strtolower($varyant))->first();
                    if (! $attrGroup) {
                        dd($varyant, $index, strtolower($varyant), $row);
                    }

                    if (strtolower($row['varyant_deger_'.$index]) == 'bebe mavi') {
                        $row['varyant_deger_'.$index] = 'BBM';
                    } elseif (strtolower($row['varyant_deger_'.$index]) == 'haki yeşil') {
                        $row['varyant_deger_'.$index] = 'HKY';
                    } elseif (strtolower($row['varyant_deger_'.$index]) == 'xs/s') {
                        $row['varyant_deger_'.$index] = 'XSS';
                    } elseif (strtolower($row['varyant_deger_'.$index]) == 'm/l') {
                        $row['varyant_deger_'.$index] = 'ML';
                    } elseif (strtolower($row['varyant_deger_'.$index]) == 's/m') {
                        $row['varyant_deger_'.$index] = 'SM';
                    }

                    $attrValue = AttributeValue::where('attribute_group_id', $attrGroup->id)
                        ->where(function ($query) use ($row, $index) {
                            $query->where('value', strtolower($row['varyant_deger_'.$index]))
                                ->orWhere('code', strtolower($row['varyant_deger_'.$index]));
                        })
                        ->first();

                    if (! $attrValue) {
                        dd($varyant, $index, strtolower($row['varyant_deger_'.$index]), $row);
                    }

                    $variants[$attrGroup->id] = $attrValue->id;
                }
            }

            if (str_contains($row['resim_url'], ';')) {
                $images = explode(';', $row['resim_url']);
                $row['resim_url'] = $images[0];
            }

            $products[$row['urun_grup_id']]['variants'][$row['varyant_id']] = [
                'name' => $row['isim'],
                'image' => $row['resim_url'],
                'purchase_price' => $row['variant_alis_fiyati'] ?? 0,
                'sale_price' => $row['variant_satis_fiyati'] ?? 0,
                'discounted_price' => $row['variant_indirimli_fiyati'] ?? 0,
                'stock' => $row['stoktr_istanbul_kahraman'] ?? $row['stokkktc_ana_depo'] ?? 0,
                'attributes' => $variants,
            ];
        }
        
        //dd($products);

        $products = array_slice($products, 0, 20, true);

        foreach ($products as $externalId => $productData) {

            if (Product::where('external_id', $externalId)->exists()) {
                continue;
            }

            $category = Category::where(['name' => $productData['category']])->firstOrFail();
            $productData['external_id'] = $externalId;
            $productData['category_id'] = $category->id;
            $product = Product::create(Arr::only($productData, ['name', 'description', 'purchase_price', 'sale_price', 'discounted_price', 'external_id', 'category_id']));

            $createdVariantIds = [];
            $photoAddedVariantIds = [];
            foreach ($productData['variants'] as $extId => $variantData) {
                
                $variant = $product->variants()->create([
                    'purchase_price' => $variantData['purchase_price'] ?? null,
                    'sale_price' => $variantData['sale_price'] ?? null,
                    'discounted_price' => $variantData['discounted_price'] ?? null,
                    'external_id' => $extId,
                ]);

                $createdVariantIds[] = $variant->id;

                // Varyant özelliklerini ekle
                foreach ($variantData['attributes'] as $groupId => $attrValueId) {
                    $attrValue = AttributeValue::where('id', $attrValueId)->firstOrFail();
                    ProductVariantAttribute::create([
                        'product_variant_id' => $variant->id,
                        'attribute_group_id' => $groupId,
                        'attribute_value_id' => $attrValueId,
                    ]);
                }

                $variant->generateSku();
                $variant->generateBarcode();
                $variant->save();

                ray($variantData['image']);

                if (isset($variantData['image']) && $variantData['image']) {
                    if (! in_array($variant->id, $photoAddedVariantIds)) {
                        CopyVariantImageFromRemoteToLocal::dispatch($variantData['image'], $variant->id);
                        $photoAddedVariantIds[] = $variant->id;
                    }
                }

                Stock::create([
                    'product_variant_id' => $variant->id,
                    'warehouse_id' => Warehouse::first()->id,
                    'quantity' => $variantData['stock'] ?? 0,
                ]);
            }
        }
    }

    public function headingRow(): int
    {
        return 1;
    }
}

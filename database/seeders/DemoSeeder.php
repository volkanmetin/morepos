<?php

namespace Database\Seeders;

use App\Actions\Fortify\CreateNewUser;
use App\Enums\AttributeGroupType;
use App\Enums\Size;
use App\Models\AttributeGroup;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariantAttribute;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Stock;
use App\Models\Tenant;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = app(CreateNewUser::class)
            ->create([
                'name' => 'Admin',
                'email' => 'test@test.com',
                'password' => '12341234',
                'password_confirmation' => '12341234',
            ]);

        $tenant1 = Tenant::create([
            'name' => 'More Türkiye',
            'domain' => 'more-tr.morepos.test',
            'database' => 'more-tr',
        ]);

        $user->tenants()->attach($tenant1);

        $tenant1->makeCurrent();

        $tenant2 = Tenant::create([
            'name' => 'More Kıbrıs',
            'domain' => 'more-cy.morepos.test',
            'database' => 'more-cy',
        ]);
        $user->tenants()->attach($tenant2);

        $tenant3 = Tenant::create([
            'name' => 'More [TEST]',
            'domain' => 'more-test.morepos.test',
            'database' => 'more-test',
        ]);
        $user->tenants()->attach($tenant3);

        // Kategoriler
        $categories = [
            ['code' => 'UST', 'name' => 'Üst Giyim', 'description' => 'Kadın üst giyim ürünleri'],
            ['code' => 'ALT', 'name' => 'Alt Giyim', 'description' => 'Kadın alt giyim ürünleri'],
            ['code' => 'DIS', 'name' => 'Dış Giyim', 'description' => 'Kadın dış giyim ürünleri'],
            ['code' => 'ELB', 'name' => 'Elbise', 'description' => 'Kadın elbise ürünleri'],
            ['code' => 'TKM', 'name' => 'Takım', 'description' => 'Kadın takım ürünleri'],
            ['code' => 'TLM', 'name' => 'Tulum', 'description' => 'Kadın tulum ürünleri'],
            ['code' => 'DGR', 'name' => 'Diğer', 'description' => 'Diğer kadın giyim ürünleri'],
        ];

        foreach ($categories as $category) {
            $tenant1->makeCurrent();
            Category::create($category);

            $tenant2->makeCurrent();
            Category::create($category);

            $tenant3->makeCurrent();
            Category::create($category);
        }

        // Alt kategoriler
        $subCategories = [
            ['code' => 'ATL', 'name' => 'Atlet', 'description' => 'Rahat ve hafif kadın atletleri', 'parent_id' => 1],
            ['code' => 'BLZ', 'name' => 'Bluz', 'description' => 'Şık ve zarif kadın bluzları', 'parent_id' => 1],
            ['code' => 'CKT', 'name' => 'Ceket', 'description' => 'Tarz sahibi kadın ceketleri', 'parent_id' => 3],
            ['code' => 'CRP', 'name' => 'Crop', 'description' => 'Modaya uygun kadın crop üstleri', 'parent_id' => 1],
            ['code' => 'ETK', 'name' => 'Etek', 'description' => 'Çeşitli stil ve boyda kadın etekleri', 'parent_id' => 2],
            ['code' => 'GML', 'name' => 'Gömlek', 'description' => 'Klasik ve modern kadın gömlekleri', 'parent_id' => 1],
            ['code' => 'HRK', 'name' => 'Hırka', 'description' => 'Sıcak tutan şık kadın hırkaları', 'parent_id' => 3],
            ['code' => 'JEN', 'name' => 'Jean', 'description' => 'Rahat ve dayanıklı kadın jean pantolonları', 'parent_id' => 2],
            ['code' => 'KBN', 'name' => 'Kaban', 'description' => 'Soğuk havalar için şık kadın kabanları', 'parent_id' => 3],
            ['code' => 'KZK', 'name' => 'Kazak / Triko', 'description' => 'Yumuşak ve sıcak tutan kadın kazak ve trikoları', 'parent_id' => 1],
            ['code' => 'KMN', 'name' => 'Kimono / Pareo', 'description' => 'Plaj ve günlük kullanım için şık kadın kimono ve pareoları', 'parent_id' => 3],
            ['code' => 'MNT', 'name' => 'Mont', 'description' => 'Soğuk havalar için kalın ve sıcak tutan kadın montları', 'parent_id' => 3],
            ['code' => 'PNT', 'name' => 'Pantolon', 'description' => 'Her tarza uygun kadın pantolonları', 'parent_id' => 2],
            ['code' => 'SWT', 'name' => 'Sweatshirt', 'description' => 'Rahat ve spor tarzda kadın sweatshirtleri', 'parent_id' => 1],
            ['code' => 'TYT', 'name' => 'Tayt', 'description' => 'Esnek ve rahat kadın taytları', 'parent_id' => 2],
            ['code' => 'TSH', 'name' => 'Tshirt', 'description' => 'Günlük kullanım için rahat kadın tişörtleri', 'parent_id' => 1],
            ['code' => 'YLK', 'name' => 'Yelek', 'description' => 'Şık ve kullanışlı kadın yelekleri', 'parent_id' => 3],
            ['code' => 'SRT', 'name' => 'Şort', 'description' => 'Yaz sezonu için rahat kadın şortları', 'parent_id' => 2],
        ];

        foreach ($subCategories as $subCategory) {
            $tenant1->makeCurrent();
            Category::create($subCategory);

            $tenant2->makeCurrent();
            Category::create($subCategory);

            $tenant3->makeCurrent();
            Category::create($subCategory);
        }

        // Depolar
        $warehouses = [
            ['name' => 'İstanbul Depo', 'address' => 'Kartal İstanbul, Türkiye'],
            ['name' => 'Kıbrıs Depo', 'address' => 'Girne, KKTC'],
            ['name' => 'Test Depo', 'address' => 'Test, Test'],
        ];

        $tenant1->makeCurrent();
        Warehouse::create($warehouses[0]);

        $tenant2->makeCurrent();
        Warehouse::create($warehouses[0]);

        $tenant3->makeCurrent();
        Warehouse::create($warehouses[0]);

        // Özellik Grupları ve Değerleri
        $tenant1->makeCurrent();
        $colorGroup = AttributeGroup::create(['name' => 'Renk', 'type' => AttributeGroupType::COLOR->value]);
        $sizeGroup = AttributeGroup::create(['name' => 'Beden', 'type' => AttributeGroupType::SELECT->value]);

        $tenant2->makeCurrent();
        $colorGroup = AttributeGroup::create(['name' => 'Renk', 'type' => AttributeGroupType::COLOR->value]);
        $sizeGroup = AttributeGroup::create(['name' => 'Beden', 'type' => AttributeGroupType::SELECT->value]);

        $tenant3->makeCurrent();
        $colorGroup = AttributeGroup::create(['name' => 'Renk', 'type' => AttributeGroupType::COLOR->value]);
        $sizeGroup = AttributeGroup::create(['name' => 'Beden', 'type' => AttributeGroupType::SELECT->value]);

        $colors = [
            ['code' => 'CKR', 'name' => 'Çok Renkli', 'color_code' => '#77e9ad'],
            ['code' => 'SYH', 'name' => 'Siyah', 'color_code' => '#000000'],
            ['code' => 'BYZ', 'name' => 'Beyaz', 'color_code' => '#FFFFFF'],
            ['code' => 'KRM', 'name' => 'Kırmızı', 'color_code' => '#FF0000'],
            ['code' => 'MAV', 'name' => 'Mavi', 'color_code' => '#0000FF'],
            ['code' => 'YSL', 'name' => 'Yeşil', 'color_code' => '#008000'],
            ['code' => 'GRI', 'name' => 'Gri', 'color_code' => '#808080'],
            ['code' => 'BEJ', 'name' => 'Bej', 'color_code' => '#F5F5DC'],
            ['code' => 'MOR', 'name' => 'Mor', 'color_code' => '#800080'],
            ['code' => 'TRN', 'name' => 'Turuncu', 'color_code' => '#FFA500'],
            ['code' => 'LCT', 'name' => 'Lacivert', 'color_code' => '#000080'],
            ['code' => 'KHV', 'name' => 'Kahverengi', 'color_code' => '#A52A2A'],
            ['code' => 'FUS', 'name' => 'Fuşya', 'color_code' => '#FF00FF'],
            ['code' => 'PMB', 'name' => 'Pembe', 'color_code' => '#FFC0CB'],
            ['code' => 'LAC', 'name' => 'Lila', 'color_code' => '#C8A2C8'],
            ['code' => 'SRI', 'name' => 'Sarı', 'color_code' => '#FFFF00'],
            ['code' => 'YMS', 'name' => 'Yumurta sarısı', 'color_code' => '#FFD700'],
            ['code' => 'EKR', 'name' => 'Ekru', 'color_code' => '#F3E5AB'],
            ['code' => 'HRD', 'name' => 'Hardal', 'color_code' => '#FFDB58'],
            ['code' => 'BRD', 'name' => 'Bordo', 'color_code' => '#800020'],
            ['code' => 'KRC', 'name' => 'Kırçıllı', 'color_code' => '#D3D3D3'],
            ['code' => 'ANT', 'name' => 'Antrasit', 'color_code' => '#808080'],
            ['code' => 'BBM', 'name' => 'Bebe Mavisi', 'color_code' => '#E0FFFF'],
            ['code' => 'HKY', 'name' => 'Haki Yeşili', 'color_code' => '#9F9F5F'],
            ['code' => 'KRM', 'name' => 'Krem', 'color_code' => '#F0E68C'],
            ['code' => 'VZN', 'name' => 'Vizon', 'color_code' => '#ebc8b2'],
        ];

        $sort = 1;
        foreach ($colors as $color) {

            AttributeValue::create([
                'attribute_group_id' => $colorGroup->id,
                'value' => $color['name'],
                'color_code' => $color['color_code'],
                'code' => $color['code'],
                'sort' => $sort++,
            ]);
        }

        $sizes = Size::cases();

        $sort = 1;
        foreach ($sizes as $size) {
            AttributeValue::create(['attribute_group_id' => $sizeGroup->id, 'value' => $size->name(), 'code' => $size->value, 'sort' => $sort++]);
        }

        // Ürünler ve Varyantlar
        /*
        $products = [
            [
                'name' => 'Şık Bluz',
                'description' => 'Zarif ve rahat günlük bluz',
                'category_id' => 2,
                'variants' => [
                    [
                        'sale_price' => 129.99,
                        'attributes' => ['Beyaz', 'Small'],
                    ],
                    [
                        'sale_price' => 129.99,
                        'attributes' => ['Siyah', 'Small'],
                    ],
                    [
                        'sale_price' => 129.99,
                        'attributes' => ['Siyah', 'Medium'],
                    ],
                    [
                        'sale_price' => 129.99,
                        'attributes' => ['Siyah', 'Large'],
                    ],
                ]
            ],
            [
                'name' => 'Skinny Jean',
                'description' => 'Yüksek bel skinny jean',
                'category_id' => 2,
                'variants' => [
                    [
                        'attributes' => ['Mavi', 'Small'],
                    ],
                    [
                        'attributes' => ['Siyah', 'Medium'],
                    ],
                ]
            ],
            // Diğer ürünler...
        ];

        $createdVariantIds = [];

        foreach ($products as $productData) {
            $product = Product::create([
                'name' => $productData['name'],
                'description' => $productData['description'],
                'category_id' => $productData['category_id'],
            ]);

            foreach ($productData['variants'] as $variantData) {
                $variant = $product->variants()->create([
                    'purchase_price' => $variantData['purchase_price'] ?? null,
                    'sale_price' => $variantData['sale_price'] ?? null,
                    'discounted_price' => $variantData['discounted_price'] ?? null,
                ]);

                $createdVariantIds[] = $variant->id;

                // Varyant özelliklerini ekle
                foreach ($variantData['attributes'] as $attributeValue) {
                    $attrValue = AttributeValue::where('value', $attributeValue)->first();
                    if ($attrValue) {
                        ProductVariantAttribute::create([
                            'product_variant_id' => $variant->id,
                            'attribute_group_id' => $attrValue->attribute_group_id,
                            'attribute_value_id' => $attrValue->id,
                        ]);
                    }
                }

                $variant->generateSku();
                $variant->generateBarcode();
                $variant->save();

                // Her varyant için stok oluştur
                foreach (Warehouse::all() as $warehouse) {
                    Stock::create([
                        'product_variant_id' => $variant->id,
                        'warehouse_id' => $warehouse->id,
                        'quantity' => rand(10, 100),
                    ]);
                }
            }
        }
        */

        // Müşteriler
        $customers = [
            ['name' => 'Zeynep Yılmaz', 'email' => 'zeynep@example.com', 'phone' => '5551234567', 'address' => 'İstanbul, Türkiye'],
            ['name' => 'Ayşe Demir', 'email' => 'ayse@example.com', 'phone' => '5559876543', 'address' => 'Ankara, Türkiye'],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        // Satışlar ve Satış Detayları
        /*
        $sales = [
            [
                'customer_id' => 1,
                'total_amount' => 329.98,
                'sale_date' => '2024-09-04',
                'status' => 'completed',
                'details' => [
                    ['product_variant_id' => $createdVariantIds[0], 'quantity' => 1, 'price' => 129.99],
                    ['product_variant_id' => $createdVariantIds[2], 'quantity' => 1, 'price' => 199.99],
                ]
            ],
            [
                'customer_id' => 2,
                'total_amount' => 329.98,
                'sale_date' => '2024-09-04',
                'status' => 'pending',
                'details' => [
                    ['product_variant_id' => $createdVariantIds[1], 'quantity' => 1, 'price' => 129.99],
                    ['product_variant_id' => $createdVariantIds[3], 'quantity' => 1, 'price' => 199.99],
                ]
            ],
        ];

        foreach ($sales as $saleData) {
            $sale = Sale::create([
                'customer_id' => $saleData['customer_id'],
                'total_amount' => $saleData['total_amount'],
                'sale_date' => $saleData['sale_date'],
                'status' => $saleData['status'],
            ]);

            foreach ($saleData['details'] as $detailData) {
                SaleDetail::create(array_merge($detailData, ['sale_id' => $sale->id]));
            }
        }
        */
    }
}

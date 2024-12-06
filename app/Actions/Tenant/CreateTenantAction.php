<?php

namespace App\Actions\Tenant;

use App\Enums\AttributeGroupType;
use App\Enums\Size;
use App\Models\AttributeGroup;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Hash;

class CreateTenantAction
{
    public function execute(array $data, ?User $user = null): Tenant
    {
        if (is_null($user)) {
            $user = User::create([
                'name' => $data['user'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
        }

        $tenant = Tenant::create([
            'name' => $data['name'],
            'domain' => $data['domain'],
            'database' => $data['database'],
        ]);

        $user->tenants()->attach($tenant);

        $tenant->makeCurrent();

        $this->createCategories();
        $this->createAttributes();

        if (isset($data['warehouses'])) {
            foreach ($data['warehouses'] as $warehouse) {
                Warehouse::create($warehouse);
            }
        }

        return $tenant;
    }

    private function createCategories(): void
    {
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
            Category::create($category);
        }

        // Alt kategoriler
        $subCategories = [
            ['code' => 'ATL', 'name' => 'Atlet', 'description' => 'Rahat ve hafif kadın atletleri', 'parent' => 'UST'],
            ['code' => 'BLZ', 'name' => 'Bluz', 'description' => 'Şık ve zarif kadın bluzları', 'parent' => 'UST'],
            ['code' => 'CKT', 'name' => 'Ceket', 'description' => 'Tarz sahibi kadın ceketleri', 'parent' => 'UST'],
            ['code' => 'CRP', 'name' => 'Crop', 'description' => 'Modaya uygun kadın crop üstleri', 'parent' => 'UST'],
            ['code' => 'ETK', 'name' => 'Etek', 'description' => 'Çeşitli stil ve boyda kadın etekleri', 'parent' => 'ALT'],
            ['code' => 'GML', 'name' => 'Gömlek', 'description' => 'Klasik ve modern kadın gömlekleri', 'parent' => 'UST'],
            ['code' => 'HRK', 'name' => 'Hırka', 'description' => 'Sıcak tutan şık kadın hırkaları', 'parent' => 'UST'],
            ['code' => 'JEN', 'name' => 'Jean', 'description' => 'Rahat ve dayanıklı kadın jean pantolonları', 'parent' => 'ALT'],
            ['code' => 'KBN', 'name' => 'Kaban', 'description' => 'Soğuk havalar için şık kadın kabanları', 'parent' => 'UST'],
            ['code' => 'KZK', 'name' => 'Kazak / Triko', 'description' => 'Yumuşak ve sıcak tutan kadın kazak ve trikoları', 'parent' => 'UST'],
            ['code' => 'KMN', 'name' => 'Kimono / Pareo', 'description' => 'Plaj ve günlük kullanım için şık kadın kimono ve pareoları', 'parent' => 'UST'],
            ['code' => 'MNT', 'name' => 'Mont', 'description' => 'Soğuk havalar için kalın ve sıcak tutan kadın montları', 'parent' => 'UST'],
            ['code' => 'PNT', 'name' => 'Pantolon', 'description' => 'Her tarza uygun kadın pantolonları', 'parent' => 'ALT'],
            ['code' => 'SWT', 'name' => 'Sweatshirt', 'description' => 'Rahat ve spor tarzda kadın sweatshirtleri', 'parent' => 'UST'],
            ['code' => 'TYT', 'name' => 'Tayt', 'description' => 'Esnek ve rahat kadın taytları', 'parent' => 'UST'],
            ['code' => 'TSH', 'name' => 'Tshirt', 'description' => 'Günlük kullanım için rahat kadın tişörtleri', 'parent' => 'UST'],
            ['code' => 'YLK', 'name' => 'Yelek', 'description' => 'Şık ve kullanışlı kadın yelekleri', 'parent' => 'UST'],
            ['code' => 'SRT', 'name' => 'Şort', 'description' => 'Yaz sezonu için rahat kadın şortları', 'parent' => 'ALT'],
        ];

        foreach ($subCategories as $subCategory) {
            $subCategory['parent_id'] = Category::where('code', $subCategory['parent'])->first()->id;
            unset($subCategory['parent']);
            Category::create($subCategory);
        }
    }

    private function createAttributes(): void
    {
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
    }
}

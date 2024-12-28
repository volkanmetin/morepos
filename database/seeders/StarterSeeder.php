<?php

namespace Database\Seeders;

use App\Actions\Tenant\CreateTenantAction;
use Illuminate\Database\Seeder;

class StarterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $action = new CreateTenantAction;

        $tenant = $action->execute([
            'name' => 'More TEST',
            'domain' => 'more-test.' . config('app.domain'),
            'database' => 'more-test',
            'user' => 'Admin TEST',
            'email' => 'test@test.com',
            'password' => '12341234',
            'warehouses' => [
                [
                    'name' => 'Depo TEST',
                    'address' => 'Depo TEST Adresi',
                ],
            ],
        ]);

    }
}

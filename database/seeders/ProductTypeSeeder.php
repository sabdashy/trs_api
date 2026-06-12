<?php

namespace Database\Seeders;

use App\Models\ProductType;
use Illuminate\Database\Seeder;

class ProductTypeSeeder extends Seeder
{
    /**
     * Seed the product_types table (master data).
     */
    public function run(): void
    {
        // Isian sesuai form: name
        $productTypes = [
            [
                'name' => '',
            ],
            // [
            //     'name' => '',
            // ],
        ];

        foreach ($productTypes as $productType) {
            ProductType::updateOrCreate(
                ['name' => $productType['name']],
                $productType
            );
        }
    }
}

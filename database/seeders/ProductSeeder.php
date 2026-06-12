<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed the products table (master data).
     */
    public function run(): void
    {
        // Isian sesuai form: product_type_id, product_code, name, purchase_price, selling_price, description
        $products = [
            [
                'product_type_id' => '1', // id dari product_types
                'product_code' => '',
                'name' => '',
                'purchase_price' => 0,
                'selling_price' => 0,
                'description' => '',
            ],
            // [
            //     'product_type_id' => '',
            //     'product_code' => '',
            //     'name' => '',
            //     'purchase_price' => 0,
            //     'selling_price' => 0,
            //     'description' => '',
            // ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['product_code' => $product['product_code']],
                $product
            );
        }
    }
}

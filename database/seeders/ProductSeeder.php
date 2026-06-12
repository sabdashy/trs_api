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
                'product_code' => 'prdFumeHoodProSafeaire',
                'name' => 'ProSafeaire Fume Hood (Lemari Asam)',
                'purchase_price' => 30000000,
                'selling_price' => 47000000,
                'description' => 'Lemari asam (Fume Hood) berkualitas tinggi standar ISO',
            ],
            [
                'product_type_id' => '1',
                'product_code' => 'prdLaminarAirFlow',
                'name' => 'ProSafeaire Laminar Air Flow',
                'purchase_price' => 27000000,
                'selling_price' => 45000000,
                'description' => 'Meja kerja steril untuk inokulasi mikrobiologi',
            ],
            [
                'product_type_id' => '1',
                'product_code' => 'prdFumeHoodScrubberProSafeaire',
                'name' => 'ProSafeaire Fume Hood Wet Scrubber System',
                'purchase_price' => 19000000,
                'selling_price' => 24000000,
                'description' => 'Sistem penyaring udara buangan lemari asam untuk menjaga kualitas udara lingkungan',
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['product_code' => $product['product_code']],
                $product
            );
        }
    }
}

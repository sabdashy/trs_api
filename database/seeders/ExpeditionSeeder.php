<?php

namespace Database\Seeders;

use App\Models\Expedition;
use Illuminate\Database\Seeder;

class ExpeditionSeeder extends Seeder
{
    /**
     * Seed the expeditions table (master data).
     */
    public function run(): void
    {
        // Isian sesuai form: name, code, description, is_active
        $expeditions = [
            [
                'name' => 'JNE',
                'code' => 'JNE',
                'description' => 'Jalur Nugraha Ekakurir',
                'is_active' => true,
            ],
            [
                'name' => 'JNT Express',
                'code' => 'JNT Express',
                'description' => 'J&T Express',
                'is_active' => true,
            ],
            [
                'name' => 'JNT Cargo',
                'code' => 'JNT Cargo',
                'description' => 'J&T Cargo',
                'is_active' => false,
            ],
            [
                'name' => 'SiCepat',
                'code' => 'SCP',
                'description' => 'SiCepat Express',
                'is_active' => true,
            ],
            [
                'name' => 'TIKI',
                'code' => 'Tiki',
                'description' => 'Titipan Kilat',
                'is_active' => true,
            ],
        ];

        foreach ($expeditions as $expedition) {
            Expedition::updateOrCreate(
                ['name' => $expedition['name']],
                $expedition
            );
        }
    }
}

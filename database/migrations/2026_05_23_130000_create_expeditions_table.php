<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master ekspedisi (kurir pengiriman). Sebelumnya hardcoded di frontend
     * (JNT/JNE/SiCepat), sekarang jadi master data yang owner bisa kelola.
     *
     * Field:
     *  - name (required, unique)  — nama lengkap kurir
     *  - code (nullable, unique)  — singkatan untuk display kompak
     *  - description (nullable)   — catatan tambahan
     *  - is_active (default true) — owner bisa disable kurir tanpa hapus
     *                               (delete bisa rusak transaksi yang masih FK)
     *
     * Seed 3 default ekspedisi (JNE, JNT, SiCepat) supaya dropdown di transaksi
     * langsung ada isinya tanpa user perlu setup manual.
     */
    public function up(): void
    {
        Schema::create('expeditions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->nullable()->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default 3 ekspedisi populer di Indonesia
        DB::table('expeditions')->insert([
            [
                'name' => 'JNE',
                'code' => 'JNE',
                'description' => 'Jalur Nugraha Ekakurir',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'JNT',
                'code' => 'JNT',
                'description' => 'J&T Express',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SiCepat',
                'code' => 'SCP',
                'description' => 'SiCepat Express',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('expeditions');
    }
};

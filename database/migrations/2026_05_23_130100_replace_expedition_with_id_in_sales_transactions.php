<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replace kolom `expedition` (string hardcoded) jadi `expedition_id` (FK ke master).
     *
     * Data lama dianggap dummy testing — akan di-clear sebelum production.
     * Untuk transaksi yang sudah ada saat migrate jalan, expedition_id akan NULL
     * (karena belum mapping). User bisa edit transaksi & assign expedition.
     *
     * onDelete('restrict') = tidak boleh hapus expedition kalau masih ada transaksi
     * yang refer ke sana. Lebih aman untuk audit.
     */
    public function up(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            // Tambah expedition_id (nullable supaya migrate tidak fail untuk data lama)
            $table->foreignId('expedition_id')
                ->nullable()
                ->after('expedition')
                ->constrained('expeditions')
                ->restrictOnDelete();
        });

        // Drop kolom string lama (data dummy, OK di-buang)
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->dropColumn('expedition');
        });
    }

    public function down(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            // Reverse: tambah kembali kolom expedition string
            $table->string('expedition')->nullable()->after('status');
        });

        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->dropForeign(['expedition_id']);
            $table->dropColumn('expedition_id');
        });
    }
};

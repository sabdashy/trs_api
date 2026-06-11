<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan FK customer_id ke sales_transactions.
     *
     * NULLABLE di DB supaya data lama (yang dibuat sebelum kolom ini ada)
     * tidak melanggar constraint. Tapi di validation backend, customer_id
     * akan REQUIRED untuk transaksi baru.
     *
     * onDelete('restrict') = tidak boleh hapus customer kalau masih punya transaksi.
     * Lebih aman untuk audit/historical record.
     */
    public function up(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->foreignId('customer_id')
                ->nullable()
                ->after('product_id')
                ->constrained('customers')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
    }
};

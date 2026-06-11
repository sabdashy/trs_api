<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan index ke kolom yang sering di-query untuk performance saat data besar.
     *
     * Catatan: Foreign key (product_id, customer_id, sales_transaction_id, dst)
     * sudah otomatis ada index dari Laravel's foreignId(). Yang ditambah di sini
     * adalah kolom NON-FK yang sering muncul di WHERE / ORDER BY clause.
     *
     * Kolom yang di-index:
     *
     * sales_transactions:
     *   - transaction_date — dipakai filter date range di:
     *       • TransactionPage filter
     *       • HistoryPage filter
     *       • FinancialReport ?start_date=&end_date=
     *       • Dashboard analytics monthly-sales/profit/top-products
     *   - status — dipakai filter active/history:
     *       • index() WHERE status NOT IN (completed, cancelled)
     *       • history() WHERE status IN (completed, cancelled)
     *       • Frontend status badge filter
     *
     * Trade-off index:
     *   ✅ Query SELECT lebih cepat (~10x lebih cepat di data 10k+)
     *   ⚠️  INSERT/UPDATE sedikit lebih lambat (tipis, negligible untuk PT TRS)
     *   ⚠️  Sedikit makan storage (puluhan KB per index untuk data 10k)
     *
     * Untuk PT TRS scale (puluhan-ratusan transaksi/bulan), benefit > cost.
     */
    public function up(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->index('transaction_date', 'idx_transactions_date');
            $table->index('status', 'idx_transactions_status');
        });
    }

    public function down(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transactions_date');
            $table->dropIndex('idx_transactions_status');
        });
    }
};

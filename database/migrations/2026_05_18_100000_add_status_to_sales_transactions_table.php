<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom `status` ke sales_transactions untuk track lifecycle:
     *   quotation → pre_order → processing → shipping → completed
     *                                                  ↘
     *                                                  cancelled
     *
     * Aturan: cancel boleh dari status manapun KECUALI completed.
     * Default saat transaksi baru: 'quotation'.
     */
    public function up(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->enum('status', [
                'quotation',
                'pre_order',
                'processing',
                'shipping',
                'completed',
                'cancelled',
            ])->default('quotation')->after('transaction_date');
        });
    }

    public function down(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

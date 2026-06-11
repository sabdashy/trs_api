<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan info ekspedisi & nomor resi ke sales_transactions.
     *
     * - expedition: brand kurir (JNT/JNE/SiCepat). Required di store/update validation,
     *   tapi NULLABLE di DB supaya migrasi tidak fail untuk data lama yang sudah ada.
     *   Saat ini hardcoded ke 3 ekspedisi; future: pakai master data table.
     *
     * - tracking_number: nomor resi dari kurir. Wajib diisi saat status → shipping.
     *   Nullable di DB karena baru terisi setelah transition ke shipping.
     */
    public function up(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->string('expedition')->nullable()->after('status');
            $table->string('tracking_number')->nullable()->after('expedition');
        });
    }

    public function down(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->dropColumn(['expedition', 'tracking_number']);
        });
    }
};

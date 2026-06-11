<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel master customer.
     *
     * Untuk MVP standalone (sebelum integrasi e-commerce):
     *  - Customer di-input manual oleh finance/owner
     *  - Setelah e-commerce terintegrasi (post-skripsi), data customer akan
     *    auto-create dari order yang masuk
     *
     * Field minimal: name (wajib) + phone (wajib kontak primer).
     * Email & address optional supaya flexible untuk berbagai jenis customer.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone'); // nomor HP/WA (kontak primer)
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_booking_id')->constrained('service_bookings')->cascadeOnDelete();
            $table->string('kode_referensi')->unique(); // cth: PAY-001 atau kode servis/booking
            $table->string('nama_pelanggan');
            $table->decimal('jumlah_tagihan', 12, 2);
            $table->string('metode_pembayaran')->default('Tunai'); // Tunai, Transfer, E-Wallet
            $table->enum('status_pembayaran', ['Belum Bayar', 'Menunggu Verifikasi', 'Lunas'])->default('Belum Bayar');
            $table->timestamp('dibayar_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
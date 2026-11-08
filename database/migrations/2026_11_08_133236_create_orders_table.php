<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke Customer (User yang memesan)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Relasi ke Kurir (Yoga, Rafi, atau Mail - opsional saat awal dipesan)
            $table->foreignId('courier_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Status Alur Pesanan
            // Options: pending, diproses, dalam_pengiriman, selesai, dibatalkan
            $table->string('status')->default('pending');
            
            // Rincian Pembayaran & Pengiriman
            $table->decimal('total_price', 12, 2);
            $table->text('shipping_address');
            $table->string('payment_proof')->nullable(); // Foto/Bukti Transfer
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detail_pemesanan_penerimaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detail_pemesanan_id')->constrained('detail_pemesanan')->cascadeOnDelete();
            $table->foreignId('detail_penerimaan_id')->constrained('detail_penerimaan_barang')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('harga');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
            $table->index('detail_pemesanan_id');
            $table->index('detail_penerimaan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_penerimaan_pemesanans');
    }
};

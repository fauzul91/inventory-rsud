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
        Schema::create('detail_penerimaan_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerimaan_id')->constrained('penerimaans')->onDelete('cascade');
            $table->string('nama_barang');
            $table->foreignId('satuan_id')->constrained('satuans')->onDelete('restrict'); 
            $table->integer('quantity')->default(1);
            $table->integer('harga');
            $table->integer('total_harga'); 
            $table->boolean('is_layak');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_penerimaan_barangs');
    }
};

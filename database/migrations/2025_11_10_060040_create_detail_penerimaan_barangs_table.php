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
        Schema::create('detail_penerimaan_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerimaan_id')->constrained('penerimaans')->onDelete('cascade');
            $table->foreignId('stok_id')->constrained('stoks')->onDelete('restrict');
            $table->integer('quantity')->default(1);
            $table->decimal('harga', 15, 2);
            $table->decimal('total_harga', 20, 2);
            $table->boolean('is_layak')->nullable();
            $table->boolean('is_paid')->nullable();
            $table->timestamps();
            $table->index('created_at');
            $table->index('is_paid');
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

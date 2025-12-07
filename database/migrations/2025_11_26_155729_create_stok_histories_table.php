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
        Schema::create('stok_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stok_id')->constrained('stoks')->onDelete('cascade');
            $table->year('year');
            $table->decimal('quantity', 10, 2);
            $table->decimal('used_qty', 10, 2)->default(0);
            $table->decimal('remaining_qty', 10, 2)->default(0);
            $table->string('source')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->index(['stok_id', 'remaining_qty']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_histories');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_sheep', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('sheep_id');
            $table->decimal('price', 12, 2);
            $table->decimal('real_price', 12, 2)->nullable();
            $table->timestamps();

            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('sheep_id')->references('id')->on('sheep')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_sheep');
    }
};

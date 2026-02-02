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
        Schema::create('case_monitors', function (Blueprint $table) {
            $table->id();
            // sheep_id foreign key
            $table->unsignedBigInteger('sheep_id');
            $table->foreign('sheep_id')->references('id')->on('sheep')->onDelete('cascade');
            // current_status_id 
            $table->unsignedBigInteger('current_status_id');
            $table->foreign('current_status_id')->references('id')->on('statuses')->onDelete('cascade');
            // next_status_id
            $table->unsignedBigInteger('next_status_id')->nullable();
            $table->foreign('next_status_id')->references('id')->on('statuses')->onDelete('set null');
            $table->date('date_monitored')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_monitors');
    }
};

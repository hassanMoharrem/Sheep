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
        Schema::create('sheep', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique(); // رقم التعريف مثل #11
        $table->enum('gender', ['male', 'female']);
        $table->date('birth_date');
    
        // العلاقات
        $table->foreignId('breed_id')->constrained('breeds')->onDelete('cascade');
        $table->foreignId('current_status_id')->constrained('statuses')->onDelete('cascade');
        $table->foreignId('health_status_id')->constrained('statuses')->onDelete('cascade');
        $table->foreignId('next_status_id')->constrained('statuses')->onDelete('cascade');
        $table->boolean('is_active')->default(true);
        // ربط المولود بالأم (Self-referencing relationship)
        $table->unsignedBigInteger('mother_id')->nullable();
        $table->foreign('mother_id')->references('id')->on('sheep')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sheep');
    }
};

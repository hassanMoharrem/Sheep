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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
    $table->foreignId('sheep_id')->constrained('sheep')->onDelete('cascade');
    // نوع الإجراء القادم
    $table->foreignId('action_type_id')->constrained('statuses')->onDelete('cascade');
    $table->date('scheduled_date'); // التاريخ الذي سيظهر فيه الإجراء في الجدول
    $table->enum('status', ['pending', 'completed'])->default('pending');
    $table->string('result')->nullable(); // نتيجة المستخدم (تم، حامل، حايل)
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};

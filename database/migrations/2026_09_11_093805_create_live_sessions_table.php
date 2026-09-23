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
    Schema::create('live_sessions', function (Blueprint $table) {
        $table->id();
        $table->string('course_id')->unique();   // id ou slug du cours
        $table->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete();
        $table->boolean('is_live')->default(false);
        $table->string('room_name')->nullable();
        $table->timestamp('started_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_sessions');
    }
};

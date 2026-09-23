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
        Schema::create('video_splits', function (Blueprint $table) {
    $table->id();
    $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
    $table->string('original_path');
    $table->unsignedInteger('segment_minutes'); // 10, 12, 15, 20
    $table->enum('status', ['en_attente', 'en_cours', 'termine', 'echec'])->default('en_attente');
    $table->text('error_message')->nullable();
    $table->timestamps();
});
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_splits');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['video', 'quiz', 'devoir', 'tp', 'td', 'document']);
            $table->string('duration')->nullable();
            $table->string('video_url')->nullable();
            $table->string('document_url')->nullable();
            $table->json('quiz_questions')->nullable(); // [{question, options[], correct_index}]
            $table->text('instructions')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sequences');
    }
};
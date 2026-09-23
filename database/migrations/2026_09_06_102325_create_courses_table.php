<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('subject');
            $table->string('category');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('level', ['debutant', 'intermediaire', 'avance']);
            $table->string('diploma');
            $table->string('duration_full');
            $table->string('duration_alternance');
            $table->string('image')->nullable();
            $table->string('image_alt')->nullable();
            $table->boolean('popular')->default(false);
            $table->string('badge')->nullable();
            $table->boolean('is_free')->default(false);
            $table->boolean('has_certificate')->default(true);
            $table->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
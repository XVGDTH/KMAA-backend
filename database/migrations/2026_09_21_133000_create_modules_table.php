<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')
                  ->constrained('courses')
                  ->cascadeOnDelete();

            $table->string('title');                     // "Analyse financière"
            $table->text('description')->nullable();     // optionnel : résumé du module
            $table->unsignedInteger('position')->default(0);

            $table->timestamps();

            // Un cours ne peut pas avoir 2 modules à la même position
            $table->unique(['course_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
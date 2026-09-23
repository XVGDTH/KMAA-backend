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
    Schema::create('masterclasses', function (Blueprint $table) {
        $table->id();
        $table->string('category')->default('MASTERCLASS');
        $table->string('title');
        $table->string('subtitle')->nullable();
        $table->text('description')->nullable();
        $table->string('date_label')->nullable();     // "Samedi 12 septembre 2026"
        $table->string('time_label')->nullable();     // "18:00 – 20:00"
        $table->string('timezone')->default('GMT');
        $table->unsignedInteger('participants')->default(0);
        $table->unsignedInteger('capacity')->default(500);
        $table->string('language')->default('Français');
        $table->string('status')->default('upcoming'); // upcoming|live|replay
        $table->string('youtube_id')->nullable();      // vidéo affichée par défaut

        // Intervenant
        $table->string('speaker_name')->nullable();
        $table->string('speaker_role')->nullable();
        $table->string('speaker_organization')->nullable();
        $table->text('speaker_bio')->nullable();
        $table->string('speaker_image')->nullable();
        $table->json('speaker_stats')->nullable();     // [{value,label}]

        // Sections dynamiques
        $table->json('sessions')->nullable();          // [{time,duration,title,description,status}]
        $table->json('videos')->nullable();            // [{id,title,speaker,duration,category,description}]
        $table->json('resources')->nullable();         // [{icon,title,type,size}]
        $table->json('quiz')->nullable();              // [{question,options[],correct}]

        $table->boolean('published')->default(true);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('masterclasses');
}
};

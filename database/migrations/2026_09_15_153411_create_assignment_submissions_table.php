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
    Schema::create('assignment_submissions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('sequence_id')->constrained('sequences')->cascadeOnDelete();
        $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
        $table->string('file_path')->nullable();          // /storage/... → relatif
        $table->text('comment')->nullable();              // commentaire de l'apprenant
        $table->string('status')->default('soumis');      // soumis|corrige|retourne
        $table->unsignedTinyInteger('grade')->nullable(); // note /20
        $table->text('instructor_feedback')->nullable();  // correction personnalisée
        $table->timestamps();

        $table->unique(['user_id', 'sequence_id']);       // 1 soumission par apprenant/séquence
    });
}

public function down(): void
{
    Schema::dropIfExists('assignment_submissions');
}
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sequences', function (Blueprint $table) {
            // ------------------------------------------------------
            // Rattachement FLEXIBLE :
            //   - chapter_id rempli                         → séquence de chapitre
            //   - module_id rempli ET chapter_id null       → quiz de fin de module
            //   - course_id rempli ET module_id null        → examen final
            // ------------------------------------------------------
            $table->foreignId('module_id')
                  ->nullable()
                  ->after('chapter_id')
                  ->constrained('modules')
                  ->cascadeOnDelete();

            $table->foreignId('course_id')
                  ->nullable()
                  ->after('module_id')
                  ->constrained('courses')
                  ->cascadeOnDelete();

            // ------------------------------------------------------
            // Options de QUIZ (utilisées seulement si type = 'quiz')
            // ------------------------------------------------------
            $table->unsignedInteger('passing_score')
                  ->nullable()
                  ->after('type')
                  ->comment('Score minimum en % pour valider (null = pas bloquant)');

            $table->boolean('is_blocking')
                  ->default(false)
                  ->after('passing_score')
                  ->comment('Si true, l\'apprenant doit réussir ce quiz pour continuer');

            $table->boolean('is_final')
                  ->default(false)
                  ->after('is_blocking')
                  ->comment('Si true = examen final du cours');

            // ------------------------------------------------------
            // Options de CAS PRATIQUE (utilisées seulement si type = 'cas_pratique')
            // ------------------------------------------------------
            $table->timestamp('deadline')
                  ->nullable()
                  ->after('instructions')
                  ->comment('Date limite de rendu');

            $table->boolean('allow_file_upload')
                  ->default(false)
                  ->after('deadline')
                  ->comment('Autoriser l\'apprenant à téléverser un fichier');
        });

        // ------------------------------------------------------
        // Étendre l'enum 'type' de sequences
        // On passe en VARCHAR pour être souple (plus besoin d'enum rigide)
        // ------------------------------------------------------
        DB::statement("
            ALTER TABLE sequences
            MODIFY COLUMN type VARCHAR(50) NOT NULL
        ");

        // Ajouter un index sur (course_id, is_final) pour retrouver l'examen final vite
        Schema::table('sequences', function (Blueprint $table) {
            $table->index(['course_id', 'is_final'], 'idx_sequences_final');
            $table->index(['module_id', 'type'], 'idx_sequences_module_type');
        });
    }

    public function down(): void
    {
        Schema::table('sequences', function (Blueprint $table) {
            $table->dropIndex('idx_sequences_final');
            $table->dropIndex('idx_sequences_module_type');

            $table->dropForeign(['module_id']);
            $table->dropForeign(['course_id']);

            $table->dropColumn([
                'module_id',
                'course_id',
                'passing_score',
                'is_blocking',
                'is_final',
                'deadline',
                'allow_file_upload',
            ]);
        });

        // ⚠️ Remettre l'ancien enum si tu veux un rollback propre
        DB::statement("
            ALTER TABLE sequences
            MODIFY COLUMN type ENUM(
                'video','quiz','devoir','tp','td','document'
            ) NOT NULL
        ");
    }
};
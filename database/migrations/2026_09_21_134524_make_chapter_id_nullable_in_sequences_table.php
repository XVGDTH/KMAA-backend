<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ------------------------------------------------------
        // 1. Supprimer la foreign key existante sur chapter_id
        //    (sinon on ne peut pas modifier la colonne)
        // ------------------------------------------------------
        Schema::table('sequences', function (Blueprint $table) {
            $table->dropForeign(['chapter_id']);
        });

        // ------------------------------------------------------
        // 2. Modifier chapter_id pour le rendre NULLABLE
        //    et chaîner cascadeOnDelete via une nouvelle FK
        // ------------------------------------------------------
        Schema::table('sequences', function (Blueprint $table) {
            $table->foreignId('chapter_id')
                  ->nullable()
                  ->change();

            $table->foreign('chapter_id')
                  ->references('id')
                  ->on('chapters')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sequences', function (Blueprint $table) {
            $table->dropForeign(['chapter_id']);
        });

        Schema::table('sequences', function (Blueprint $table) {
            $table->foreignId('chapter_id')
                  ->nullable(false)
                  ->change();

            $table->foreign('chapter_id')
                  ->references('id')
                  ->on('chapters')
                  ->cascadeOnDelete();
        });
    }
};
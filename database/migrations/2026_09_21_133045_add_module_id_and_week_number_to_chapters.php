<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            // Rattachement à un module (nullable pour ne pas casser les chapitres existants)
            $table->foreignId('module_id')
                  ->nullable()
                  ->after('course_id')
                  ->constrained('modules')
                  ->nullOnDelete();

            // Numéro de semaine AFFICHÉ (ex: "Semaine 1") — c'est un simple label
            // Le formateur peut le laisser vide s'il ne veut pas afficher de semaine
            $table->unsignedInteger('week_number')
                  ->nullable()
                  ->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->dropColumn(['module_id', 'week_number']);
        });
    }
};
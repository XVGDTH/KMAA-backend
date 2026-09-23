<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Le champ "diploma" (Select Certificat/Attestation) remplace
            // désormais "certificate_type" — un seul champ au lieu de deux.
            if (Schema::hasColumn('courses', 'certificate_type')) {
                $table->dropColumn('certificate_type');
            }

            // Le volume horaire n'est plus saisi au niveau du cours : il est
            // calculé automatiquement par apprenant (début → fin de parcours)
            // et stocké sur le certificat lui-même, pas sur le cours.
            if (Schema::hasColumn('courses', 'duration_hours')) {
                $table->dropColumn('duration_hours');
            }

            // Le nom du formateur vient de la relation instructor_id → users.
            // Plus besoin de le ressaisir manuellement.
            if (Schema::hasColumn('courses', 'instructor_name')) {
                $table->dropColumn('instructor_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->enum('certificate_type', ['certificate', 'attestation'])->default('certificate');
            $table->unsignedInteger('duration_hours')->nullable();
            $table->string('instructor_name')->nullable();
        });
    }
};
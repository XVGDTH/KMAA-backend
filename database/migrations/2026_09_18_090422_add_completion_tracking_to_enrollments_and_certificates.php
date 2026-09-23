<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (!Schema::hasColumn('enrollments', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('created_at');
            }
        });

        Schema::table('certificates', function (Blueprint $table) {
            if (!Schema::hasColumn('certificates', 'duration_hours')) {
                // Calculé une seule fois à la génération du document, puis figé
                // (le certificat ne doit pas changer rétroactivement si
                // l'apprenant continue d'utiliser son compte après coup).
                $table->unsignedInteger('duration_hours')->nullable()->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn('duration_hours');
        });
    }
};
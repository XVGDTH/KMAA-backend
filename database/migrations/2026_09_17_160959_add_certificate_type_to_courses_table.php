<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->enum('certificate_type', ['certificate', 'attestation'])
                ->default('certificate')
                ->after('slug');

            // Optionnels mais utilisés par les nouveaux templates — laisse
            // nullable, le PDF s'adapte gracieusement si vides.
            $table->unsignedInteger('duration_hours')->nullable()->after('certificate_type');
            $table->string('promotion_label')->nullable()->after('duration_hours');
            $table->string('instructor_name')->nullable()->after('promotion_label');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['certificate_type', 'duration_hours', 'promotion_label', 'instructor_name']);
        });
    }
};
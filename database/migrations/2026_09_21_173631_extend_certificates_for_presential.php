<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            // enrolment_id devient nullable (formations présentielle)
            $table->foreignId('enrollment_id')
                  ->nullable()
                  ->change();

            // Nouveau lien formation présentielle
            $table->foreignId('presential_training_id')
                  ->nullable()
                  ->after('enrollment_id')
                  ->constrained('presential_trainings')
                  ->nullOnDelete();

            // Snapshot du nom de l'élève
            $table->string('student_name')->nullable()->after('reference');

            // Snapshot du titre du cours / formation
            $table->string('course_title')->nullable()->after('student_name');

            // Révocation
            $table->boolean('revoked')->default(false)->after('issued_at');
            $table->text('revocation_reason')->nullable()->after('revoked');
            $table->timestamp('revoked_at')->nullable()->after('revocation_reason');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['presential_training_id']);
            $table->dropColumn([
                'presential_training_id',
                'student_name',
                'course_title',
                'revoked',
                'revocation_reason',
                'revoked_at',
            ]);

            $table->foreignId('enrollment_id')->nullable(false)->change();
        });
    }
};
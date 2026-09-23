<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presential_trainings', function (Blueprint $table) {
            $table->id();

            // --- Identité de la formation ---
            $table->string('title');
            $table->text('description')->nullable();

            // --- Période et durée ---
            $table->date('starts_at');
            $table->date('ends_at');
            $table->unsignedInteger('days_count');
            $table->unsignedInteger('hours_count')->nullable();

            // --- Lieu et formateur (snapshot — ne dépend pas de la table users) ---
            $table->string('location')->nullable();
            $table->string('instructor_name')->nullable();
            $table->string('instructor_role')->nullable();

            // --- Participants ---
            $table->unsignedInteger('expected_participants')->nullable();
            $table->unsignedInteger('registered_count')->default(0);

            // --- Lien public ---
            $table->string('slug')->unique();
            $table->string('registration_token', 64)->unique();
            $table->boolean('registration_open')->default(true);

            // --- Type de document généré (liste déroulante) ---
            $table->enum('certificate_type', ['certificate', 'attestation'])
                  ->default('attestation');

            // --- Créateur (formateur OU admin) ---
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            $table->index('registration_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presential_trainings');
    }
};
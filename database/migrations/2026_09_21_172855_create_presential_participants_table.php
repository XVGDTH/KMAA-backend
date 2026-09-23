<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presential_participants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('presential_training_id')
                  ->constrained('presential_trainings')
                  ->cascadeOnDelete();

            // --- Identité participant ---
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();

            // --- Suivi ---
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamp('certificate_sent_at')->nullable();

            // --- Certificat généré ---
            $table->foreignId('certificate_id')
                  ->nullable()
                  ->constrained('certificates')
                  ->nullOnDelete();

            // --- Révocation ---
            $table->boolean('revoked')->default(false);
            $table->text('revocation_reason')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            // Un participant ne peut s'inscrire qu'une fois par formation
            $table->unique(['presential_training_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presential_participants');
    }
};
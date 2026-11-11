<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();                    // "BULK15"
            $table->enum('type', ['percent', 'fixed']);          // % ou montant fixe
            $table->unsignedBigInteger('value');                 // 15 (=15%) ou 50000 (FCFA)
            $table->string('label');                             // "-15% entreprises"

            // Contraintes optionnelles
            $table->unsignedBigInteger('min_subtotal')->default(0);       // panier minimum
            $table->unsignedInteger('min_team_size')->default(1);          // nb apprenants mini
            $table->unsignedInteger('max_uses')->nullable();               // null = illimité
            $table->unsignedInteger('used_count')->default(0);

            // Validité
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
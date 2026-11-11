<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // ✅ Type de cours : "individual" (sujets) ou "enterprise" (formations pro)
            $table->enum('type', ['individual', 'enterprise'])
                ->default('individual')
                ->after('slug')
                ->comment('individual = sujet catalogue, enterprise = formation entreprise');

            // ✅ Champs spécifiques aux formations entreprises
            $table->enum('delivery_mode', ['en_ligne', 'presentiel', 'mixte'])
                ->nullable()
                ->after('language')
                ->comment('Mode de livraison entreprise');

            $table->string('hours_per_week')
                ->nullable()
                ->after('delivery_mode')
                ->comment('Rythme hebdomadaire ex: 6h/semaine');

            $table->unsignedInteger('modules_count')
                ->default(0)
                ->after('hours_per_week')
                ->comment('Nombre de modules (cache)');

            // ✅ Prix par défaut de l'abonnement entreprise
            $table->unsignedBigInteger('enterprise_base_price')
                ->nullable()
                ->after('modules_count')
                ->comment('Prix de base pour entreprise (FCFA)');

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn([
                'type',
                'delivery_mode',
                'hours_per_week',
                'modules_count',
                'enterprise_base_price',
            ]);
        });
    }
};
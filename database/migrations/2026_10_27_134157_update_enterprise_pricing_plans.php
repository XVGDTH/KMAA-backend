<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. On supprime les anciennes licences (car elles dépendent des plans)
        // Attention : cela supprime les achats de test existants.
        DB::table('enterprise_license_members')->delete();
        DB::table('enterprise_licenses')->delete();

        // 2. On supprime les anciens plans
        DB::table('enterprise_pricing_plans')->delete();

        // 3. On insère les nouveaux plans (Tes 3 formules)
        DB::table('enterprise_pricing_plans')->insert([
            [
                'id' => 1,
                'slug' => 'basique',
                'name' => 'Basique',
                'price_per_seat' => 360000,
                'min_seats' => 1,
                'max_seats' => 3,
                'badge' => null,
                'description' => 'Idéal pour les petites équipes',
                'features' => json_encode(["Accès complet aux vidéos", "Quiz de validation", "Support standard"]),
                'featured' => false,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'slug' => 'standard',
                'name' => 'Standard',
                'price_per_seat' => 324000,
                'min_seats' => 4,
                'max_seats' => 6,
                'badge' => 'Recommandé',
                'description' => 'Le meilleur équilibre pour progresser',
                'features' => json_encode(["Tout Basique", "Corrections de devoirs", "Sessions live avec le formateur", "Tableau de bord manager"]),
                'featured' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'slug' => 'premium',
                'name' => 'Premium',
                'price_per_seat' => 288000,
                'min_seats' => 7,
                'max_seats' => 10,
                'badge' => 'Meilleure offre',
                'description' => 'Pour une transformation d\'équipe complète',
                'features' => json_encode(["Tout Standard", "Coaching individuel", "Certificat accéléré", "Rapport de progression détaillé"]),
                'featured' => false,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        // En cas de rollback, on remet les anciens plans (facultatif, mais bonne pratique)
        DB::table('enterprise_pricing_plans')->delete();
    }
};
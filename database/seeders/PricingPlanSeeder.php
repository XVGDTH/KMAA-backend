<?php

namespace Database\Seeders;

use App\Models\PricingPlan;
use Illuminate\Database\Seeder;

class PricingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            ['name' => 'Basique', 'price' => 15000, 'badge' => null, 'featured' => false,
             'features' => ['Accès complet au cours', 'Certificat de réussite', 'Support par email'],
             'description' => 'L\'essentiel pour suivre le cours à ton rythme.'],
            ['name' => 'Standard', 'price' => 25000, 'badge' => 'Populaire', 'featured' => true,
             'features' => ['Tout ce qui est inclus dans Basique', 'Correction personnalisée des devoirs', 'Accès aux séances en direct'],
             'description' => 'Le meilleur équilibre entre autonomie et accompagnement.'],
            ['name' => 'Premium', 'price' => 40000, 'badge' => null, 'featured' => false,
             'features' => ['Tout ce qui est inclus dans Standard', 'Coaching individuel (2 séances)', 'Accès prioritaire aux nouvelles sessions'],
             'description' => 'Accompagnement renforcé pour accélérer ta progression.'],
            ['name' => 'Entreprise', 'price' => 75000, 'badge' => null, 'featured' => false,
             'features' => ['Tout ce qui est inclus dans Premium', "Jusqu'à 5 apprenants inclus", 'Rapport de progression pour le manager'],
             'description' => 'Forme ton équipe avec suivi managérial dédié.'],
        ];

        foreach ($plans as $plan) {
            PricingPlan::updateOrCreate(['name' => $plan['name']], $plan);
        }

        $this->command->info('✅ ' . count($plans) . ' formules de prix seedées.');
    }
}
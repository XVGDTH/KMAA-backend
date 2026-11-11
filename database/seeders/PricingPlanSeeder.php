<?php

namespace Database\Seeders;

use App\Models\PricingPlan;
use Illuminate\Database\Seeder;

class PricingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'        => 'Basique',
                'price'       => 50000,
                'badge'       => null,
                'price_note'  => 'Accès individuel',
                'description' => 'Accès aux vidéos et quiz',
                'featured'    => false,
                'features'    => [
                    'Accès complet aux vidéos',
                    'Quiz de validation',
                    'Support par email',
                ],
            ],
            [
                'name'        => 'Standard',
                'price'       => 150000,
                'badge'       => 'Recommandé',
                'price_note'  => 'Équipes de 2 à 5',
                'description' => 'Tout Basique + corrections',
                'featured'    => true,
                'features'    => [
                    'Tout Basique',
                    'Correction personnalisée des devoirs',
                    'Séances live',
                    'Jusqu\'à 5 apprenants',
                ],
            ],
            [
                'name'        => 'Premium',
                'price'       => 300000,
                'badge'       => null,
                'price_note'  => 'Équipes jusqu\'à 10',
                'description' => 'Tout Standard + coaching',
                'featured'    => false,
                'features'    => [
                    'Tout Standard',
                    'Coaching individuel',
                    'Priorité sur les sessions live',
                    'Jusqu\'à 10 apprenants',
                ],
            ],
            [
                'name'        => 'Entreprise',
                'price'       => 500000,
                'badge'       => null,
                'price_note'  => 'Grandes équipes',
                'description' => 'Tout Premium + rapport manager',
                'featured'    => false,
                'features'    => [
                    'Tout Premium',
                    'Jusqu\'à 20 apprenants',
                    'Rapport manager',
                    'Sessions intra-entreprise',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            PricingPlan::updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }

        $this->command->info('✅ ' . count($plans) . ' plans tarifaires créés');
    }
}
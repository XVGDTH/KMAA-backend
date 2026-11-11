<?php

namespace Database\Seeders;

use App\Models\EnterprisePricingPlan;
use Illuminate\Database\Seeder;

class EnterprisePricingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug'           => 'basique',
                'name'           => 'Basique',
                'price_per_seat' => 210000,
                'min_seats'      => 1,
                'max_seats'      => 5,
                'badge'          => null,
                'description'    => 'Accès individuel pour 1 à 5 apprenants.',
                'featured'       => false,
                'sort_order'     => 1,
                'features'       => [
                    'Accès complet aux vidéos',
                    'Quiz de validation',
                    'Certificat individuel',
                    'Support par email',
                ],
            ],
            [
                'slug'           => 'standard',
                'name'           => 'Standard',
                'price_per_seat' => 380000,
                'min_seats'      => 2,
                'max_seats'      => 5,
                'badge'          => 'Recommandé',
                'description'    => 'Le meilleur équilibre pour les équipes de 2 à 5.',
                'featured'       => true,
                'sort_order'     => 2,
                'features'       => [
                    'Tout Basique',
                    'Correction personnalisée des devoirs',
                    'Séances live avec le formateur',
                    'Tableau de bord équipe',
                ],
            ],
            [
                'slug'           => 'premium',
                'name'           => 'Premium',
                'price_per_seat' => 580000,
                'min_seats'      => 5,
                'max_seats'      => 10,
                'badge'          => null,
                'description'    => 'Accompagnement renforcé pour équipes jusqu\'à 10.',
                'featured'       => false,
                'sort_order'     => 3,
                'features'       => [
                    'Tout Standard',
                    'Coaching individuel (2 séances)',
                    'Priorité sur les sessions live',
                    'Rapport de progression détaillé',
                ],
            ],
            [
                'slug'           => 'entreprise',
                'name'           => 'Entreprise',
                'price_per_seat' => 1050000,
                'min_seats'      => 10,
                'max_seats'      => 100,
                'badge'          => null,
                'description'    => 'Solution complète pour grandes équipes.',
                'featured'       => false,
                'sort_order'     => 4,
                'features'       => [
                    'Tout Premium',
                    'Jusqu\'à 20 apprenants',
                    'Rapport manager personnalisé',
                    'Sessions intra-entreprise',
                    'Accompagnement dédié',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            EnterprisePricingPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('✅ ' . count($plans) . ' formules entreprise créées');
    }
}
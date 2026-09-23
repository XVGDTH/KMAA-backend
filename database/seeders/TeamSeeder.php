<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use App\Models\TeamSetting;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // MEMBRES (updateOrCreate sur 'name' → ré-exécutable)
        // ============================================================

        $members = [
            ['name' => 'Aminata Kaboré', 'role' => 'Directrice générale', 'department' => 'direction', 'featured' => true, 'position' => 1,
             'bio' => "15 ans d'expérience en stratégie et transformation des organisations au Burkina Faso et en Afrique de l'Ouest.",
             'image' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=800&q=80'],
            ['name' => 'Issouf Ouédraogo', 'role' => 'Directeur pédagogique', 'department' => 'formation', 'featured' => true, 'position' => 2,
             'bio' => "Architecte des parcours certifiants KMAA. Ancien responsable formation en cabinet d'audit.",
             'image' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=800&q=80'],
            ['name' => 'Fatou Zongo', 'role' => 'Associée conseil', 'department' => 'conseil', 'featured' => true, 'position' => 3,
             'bio' => 'Spécialiste en contrôle de gestion et pilotage de la performance pour les PME et institutions.',
             'image' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=800&q=80'],
            ['name' => 'Boureima Sawadogo', 'role' => 'Expert fiscalité & OHADA', 'department' => 'conseil', 'featured' => false, 'position' => 4,
             'bio' => 'Accompagne les entreprises sur la conformité fiscale, le droit des sociétés et les restructurations.',
             'image' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=800&q=80'],
            ['name' => 'Aïcha Ouédraogo', 'role' => 'Responsable formations certifiantes', 'department' => 'formation', 'featured' => false, 'position' => 5,
             'bio' => 'Conçoit et anime les parcours métier. Passionnée par la pédagogie active et le terrain.',
             'image' => 'https://images.unsplash.com/photo-1594744803329-e58b31de8bf5?w=800&q=80'],
            ['name' => 'Moussa Kaboré', 'role' => 'Consultant audit interne', 'department' => 'conseil', 'featured' => false, 'position' => 6,
             'bio' => "Missions d'audit, cartographie des risques et mise en place de dispositifs de contrôle interne.",
             'image' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=800&q=80'],
            ['name' => 'Salimata Traoré', 'role' => 'Coordinatrice apprenants', 'department' => 'support', 'featured' => false, 'position' => 7,
             'bio' => 'Point de contact privilégié des apprenants. Suivi, motivation et réussite des parcours.',
             'image' => 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=800&q=80'],
            ['name' => 'Ibrahim Traoré', 'role' => 'Formateur data & outils digitaux', 'department' => 'formation', 'featured' => false, 'position' => 8,
             'bio' => 'Excel avancé, Power BI, automatisation. Rend les outils accessibles aux non-techniciens.',
             'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&q=80'],
        ];

        foreach ($members as $member) {
            TeamMember::updateOrCreate(
                ['name' => $member['name']],
                [...$member, 'image_alt' => $member['name']]
            );
        }

        // ============================================================
        // TEXTES DE LA PAGE (une seule ligne)
        // ============================================================

        TeamSetting::updateOrCreate(['id' => 1], [
            'hero_eyebrow' => 'Les talents derrière KMAA',
            'hero_title' => "Une équipe d'experts,",
            'hero_title_accent' => 'une vision partagée',
            'hero_lead' => "Conseillers, formateurs et stratèges unis pour faire progresser les organisations et les parcours professionnels en Afrique de l'Ouest.",
            'stats' => [
                ['value' => '12+', 'label' => 'experts terrain'],
                ['value' => '3', 'label' => "années d'expérience moyenne"],
                ['value' => '100 %', 'label' => 'orientés résultats'],
            ],
            'culture_title' => 'Ce qui nous unit au-delà des métiers',
            'culture_text' => "Rigueur, proximité terrain et transmission. Chaque membre de l'équipe partage la même ambition : rendre l'expertise accessible, concrète et utile.",
            'culture_values' => [
                ['number' => '01', 'title' => 'Excellence terrain', 'text' => 'Des méthodes éprouvées, adaptées aux réalités locales.'],
                ['number' => '02', 'title' => 'Transmission', 'text' => 'Former pour autonomiser, pas seulement pour délivrer.'],
                ['number' => '03', 'title' => 'Impact mesurable', 'text' => 'Chaque mission vise des résultats concrets et durables.'],
            ],
            'cta_title' => "Envie de construire l'avenir avec nous ?",
            'cta_text' => "Nous accueillons régulièrement des talents en conseil, formation et support. Parlez-nous de votre parcours.",
        ]);

        $this->command->info('✅ Équipe (8 membres) + textes de la page seedés.');
    }
}
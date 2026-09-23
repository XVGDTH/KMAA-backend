<?php

namespace Database\Seeders;

use App\Models\JobOffer;
use Illuminate\Database\Seeder;

class JobOfferSeeder extends Seeder
{
    public function run(): void
    {
        $jobs = [
            [
                'title' => 'Consultant(e) en contrôle de gestion',
                'type' => 'cdi', 'location' => 'Ouagadougou', 'department' => 'Conseil',
                'excerpt' => 'Accompagnez nos clients PME et institutions dans le pilotage de leur performance financière.',
                'description' => "Vous intervenez auprès de PME et d'organisations pour structurer le contrôle de gestion, les budgets et les indicateurs de pilotage.\n\nVous travaillez en binôme avec les associés conseil et les équipes formation selon les missions.",
                'missions' => [
                    'Diagnostiquer les dispositifs de pilotage existants',
                    'Construire budgets, forecasts et tableaux de bord',
                    "Former les équipes clientes aux outils mis en place",
                    'Rédiger des livrables clairs et actionnables',
                ],
                'profile' => [
                    'Formation supérieure en finance / gestion',
                    'Expérience en contrôle de gestion ou audit (2 ans min.)',
                    "Maîtrise d'Excel et des outils de reporting",
                    'Aisance relationnelle et pédagogie',
                ],
                'tags' => ['Contrôle de gestion', 'Tableaux de bord', 'Client'],
                'featured' => true,
                'image' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=900&q=80',
                'image_alt' => 'Consultant contrôle de gestion',
                'published_at' => '2026-09-05',
            ],
            [
                'title' => 'Formateur(trice) comptabilité OHADA',
                'type' => 'cdi', 'location' => 'Ouagadougou / Hybride', 'department' => 'Formation',
                'excerpt' => 'Animez des parcours certifiants et transmettez une expertise terrain aux apprenants KMAA.',
                'description' => "Vous concevez et animez des modules de comptabilité générale et analytique adaptés au contexte OHADA.",
                'missions' => [
                    'Animer des sessions présentielles et distancielles',
                    'Mettre à jour les supports pédagogiques',
                    "Évaluer les apprenants et les accompagner",
                ],
                'profile' => [
                    'Expertise comptable OHADA solide',
                    "Expérience de formation ou d'animation",
                    'Capacité à vulgariser des notions techniques',
                ],
                'tags' => ['Pédagogie', 'OHADA', 'Formation'],
                'featured' => true,
                'image' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=900&q=80',
                'image_alt' => 'Formateur comptabilité',
                'published_at' => '2026-09-03',
            ],
            [
                'title' => 'Chargé(e) de partenariats entreprises',
                'type' => 'partenaire', 'location' => 'Ouagadougou', 'department' => 'Développement',
                'excerpt' => "Développez le réseau d'entreprises partenaires pour stages, recrutements et formations sur mesure.",
                'description' => "Vous êtes l'ambassadeur(trice) du réseau professionnel KMAA auprès des entreprises de la sous-région.",
                'missions' => [
                    'Prospecter et qualifier de nouveaux partenaires',
                    'Organiser des rendez-vous et forums entreprises',
                    'Suivre la satisfaction et les conventions en cours',
                ],
                'profile' => [
                    "Bon relationnel et goût du business development",
                    "Maîtrise des outils de suivi (CRM, tableurs)",
                    "Autonomie et sens de la représentation",
                ],
                'tags' => ['B2B', 'Réseau', 'Business development'],
                'featured' => true,
                'image' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?w=900&q=80',
                'image_alt' => 'Partenariats entreprises',
                'published_at' => '2026-09-01',
            ],
            [
                'title' => 'Auditeur(trice) junior',
                'type' => 'cdd', 'location' => 'Ouagadougou', 'department' => 'Audit',
                'excerpt' => "Participez aux missions d'audit interne et de cartographie des risques auprès de nos clients.",
                'description' => "Vous participez aux missions d'audit interne sous la supervision d'un associé conseil.",
                'missions' => [
                    'Réaliser les tests de contrôle sur le terrain',
                    'Documenter les constats et recommandations',
                    'Participer aux restitutions client',
                ],
                'profile' => [
                    'Formation en audit, finance ou comptabilité',
                    'Rigueur et sens de l\'organisation',
                    "Première expérience appréciée mais non exigée",
                ],
                'tags' => ['Audit', 'Risques', 'Terrain'],
                'featured' => false,
                'image' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=900&q=80',
                'image_alt' => 'Auditeur junior',
                'published_at' => '2026-08-28',
            ],
            [
                'title' => 'Stagiaire pédagogie digitale',
                'type' => 'stage', 'location' => 'Ouagadougou', 'department' => 'Academy',
                'excerpt' => "Contribuez à la conception de modules e-learning et au suivi des parcours en ligne.",
                'description' => "Vous rejoignez l'équipe Academy pour contribuer à la digitalisation des parcours KMAA.",
                'missions' => [
                    'Concevoir des supports e-learning à partir des contenus formateurs',
                    'Tester et améliorer les parcours en ligne',
                    "Suivre les indicateurs d'engagement des apprenants",
                ],
                'profile' => [
                    'Étudiant(e) en pédagogie, communication ou digital',
                    'Curiosité pour les outils numériques éducatifs',
                    'Aisance rédactionnelle en français',
                ],
                'tags' => ['E-learning', 'Contenu', 'Innovation'],
                'featured' => false,
                'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=900&q=80',
                'image_alt' => 'Stage pédagogie digitale',
                'published_at' => '2026-08-25',
            ],
            [
                'title' => 'Expert(e) fiscalité freelance',
                'type' => 'freelance', 'location' => 'Remote / Ouaga', 'department' => 'Conseil',
                'excerpt' => "Interventions ponctuelles sur missions fiscales, conformité et accompagnement d'entreprises.",
                'description' => "Rejoignez notre vivier d'experts indépendants pour des missions ponctuelles ou récurrentes.",
                'missions' => [
                    'Réaliser des revues fiscales et conformité',
                    'Accompagner des dossiers de restructuration',
                    'Répondre aux questions complexes des clients',
                ],
                'profile' => [
                    'Expertise fiscalité Burkina / OHADA',
                    "Expérience significative en cabinet ou direction fiscale",
                    'Sens du service client',
                ],
                'tags' => ['Fiscalité', 'Mission', 'Indépendant'],
                'featured' => false,
                'image' => 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=900&q=80',
                'image_alt' => 'Expert fiscalité freelance',
                'published_at' => '2026-08-20',
            ],
        ];

        foreach ($jobs as $job) {
            JobOffer::updateOrCreate(
                ['title' => $job['title']],
                [...$job, 'published' => true]
            );
        }

        $this->command->info('✅ ' . count($jobs) . " offres d'emploi seedées.");
    }
}
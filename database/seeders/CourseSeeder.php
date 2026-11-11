<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Module;
use App\Models\Sequence;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // Récupérer le formateur par défaut
        // ============================================================
        $instructor = User::where('email', 'formateur@kmaa.com')->first()
            ?? User::role('formateur')->first()
            ?? User::first();

        if (! $instructor) {
            $this->command->error('❌ Aucun utilisateur trouvé. Créez un admin/formateur d\'abord.');
            return;
        }

        $this->command->info("Formateur utilisé : {$instructor->name}");

        // ============================================================
        // LES 12 COURS (issus de sujet.ts + sujet-detail.ts)
        // ============================================================

        $coursesData = $this->getCoursesData();
        $totalCourses = 0;
        $totalModules = 0;
        $totalChapters = 0;
        $totalSequences = 0;

        foreach ($coursesData as $data) {
            $this->command->info("→ Création : {$data['title']}");

            // ==========================================================
            // 1. Créer/Mettre à jour le cours
            // ==========================================================
            $course = Course::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'title'              => $data['title'],
                    'description'        => $data['description'],
                    'long_description'   => $data['long_description'] ?? null,
                    'subject'            => $data['subject'],
                    'category'           => $data['category'],
                    'level'              => $data['level'],
                    'diploma'            => 'certificate',
                    'duration_full'      => $data['duration_full'],
                    'duration_alternance'=> $data['duration_alternance'],
                    'image'              => $data['image'],
                    'image_alt'          => $data['image_alt'],
                    'popular'            => $data['popular'] ?? false,
                    'badge'              => $data['badge'] ?? null,
                    'is_free'            => $data['is_free'] ?? false,
                    'has_certificate'    => $data['has_certificate'] ?? true,
                    'instructor_id'      => $instructor->id,
                    'price'              => $data['price'] ?? 0,
                    'provider'           => $data['provider'] ?? 'KMAA Academy',
                    'language'           => 'Français',
                    'start_date'         => $data['start_date'] ?? null,
                ]
            );

            $totalCourses++;

            // ==========================================================
            // 2. Créer les modules + chapitres + séquences
            // ==========================================================
            if (! empty($data['modules'])) {
                foreach ($data['modules'] as $modIndex => $moduleData) {
                    $module = Module::updateOrCreate(
                        [
                            'course_id' => $course->id,
                            'position'  => $modIndex,
                        ],
                        [
                            'title'       => $moduleData['title'],
                            'description' => $moduleData['description'] ?? null,
                        ]
                    );
                    $totalModules++;

                    // Chaque module a UN chapitre (on simplifie)
                    $chapter = Chapter::updateOrCreate(
                        [
                            'course_id' => $course->id,
                            'module_id' => $module->id,
                            'position'  => 0,
                        ],
                        [
                            'title'       => $moduleData['title'],
                            'week_number' => $modIndex + 1,
                        ]
                    );
                    $totalChapters++;

                    // ===================================================
                    // Séquences du chapitre (à partir des topics)
                    // ===================================================
                    $position = 0;
                    foreach (($moduleData['topics'] ?? []) as $topic) {
                        Sequence::updateOrCreate(
                            [
                                'chapter_id' => $chapter->id,
                                'position'   => $position,
                            ],
                            [
                                'title'      => $topic,
                                'type'       => 'document',
                                'duration'   => '10 min',
                                'position'   => $position,
                            ]
                        );
                        $totalSequences++;
                        $position++;
                    }
                }
            }

            // ==========================================================
            // 3. Créer un quiz final
            // ==========================================================
            Sequence::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'is_final'  => true,
                    'position'  => 0,
                ],
                [
                    'title'         => 'Examen final — ' . $course->title,
                    'type'          => 'quiz',
                    'passing_score' => 70,
                    'is_blocking'   => true,
                    'quiz_questions' => [
                        [
                            'question' => 'Question 1 du cours ' . $data['title'],
                            'type'     => 'qcm',
                            'options'  => ['Option A', 'Option B', 'Option C', 'Option D'],
                            'correctIndex' => 0,
                        ],
                    ],
                ]
            );
            $totalSequences++;
        }

        $this->command->info('');
        $this->command->info('════════════════════════════════════════');
        $this->command->info("✅ {$totalCourses} cours créés");
        $this->command->info("✅ {$totalModules} modules créés");
        $this->command->info("✅ {$totalChapters} chapitres créés");
        $this->command->info("✅ {$totalSequences} séquences créées");
        $this->command->info('════════════════════════════════════════');
    }

    // ================================================================
    // DONNÉES DES 12 COURS (fusion de sujet.ts + sujet-detail.ts)
    // ================================================================
    private function getCoursesData(): array
    {
        return [
            // ─────────────────────────────────────────
            // 1. AUDIT INTERNE
            // ─────────────────────────────────────────
            [
                'slug'    => 'audit-interne-fondamentaux',
                'title'   => 'Fondamentaux de l\'audit interne et gestion des risques',
                'subject' => 'Audit & Contrôle',
                'category'=> 'AUDIT',
                'level'   => 'intermediaire',
                'duration_full'       => '6 semaines',
                'duration_alternance' => '3h / semaine',
                'image'    => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1600&q=80',
                'image_alt'=> 'Audit interne',
                'badge'    => 'Nouveau',
                'popular'  => true,
                'price'    => 150000,
                'provider' => 'KMAA Academy',
                'start_date' => '7 Octobre 2026',
                'description' => 'Maîtrisez les techniques d\'audit interne pour identifier, évaluer et gérer les risques au sein des organisations burkinabè.',
                'long_description' => '<p>Cette formation intensive vous prépare à devenir un acteur clé de la gouvernance d\'entreprise. Vous apprendrez à mener des missions d\'audit interne conformément aux normes internationales (IIA) tout en tenant compte des spécificités du tissu économique local.</p>',
                'modules' => [
                    ['title' => 'Introduction à l\'audit interne', 'topics' => ['Définition et rôle de l\'audit interne', 'Les normes IIA', 'Le cadre légal au Burkina Faso']],
                    ['title' => 'Planification de la mission d\'audit', 'topics' => ['Prise de connaissance de l\'entité', 'Analyse des risques', 'Programme de travail']],
                    ['title' => 'Techniques d\'audit', 'topics' => ['Entretiens et questionnaires', 'Tests de conformité', 'Sondages statistiques']],
                    ['title' => 'Rédaction du rapport d\'audit', 'topics' => ['Structure du rapport', 'Formulation des recommandations', 'Suivi des actions correctives']],
                    ['title' => 'Audit des cycles spécifiques', 'topics' => ['Cycle achats-ventes', 'Cycle paie', 'Cycle trésorerie']],
                    ['title' => 'Évaluation finale', 'topics' => ['Cas pratique complet', 'Examen de certification', 'Feedback personnalisé']],
                ],
            ],

            // ─────────────────────────────────────────
            // 2. COMMISSARIAT AUX COMPTES
            // ─────────────────────────────────────────
            [
                'slug'    => 'commissariat-aux-comptes-ohada-isa',
                'title'   => 'Commissariat aux comptes : Normes OHADA et ISA',
                'subject' => 'Expertise Comptable',
                'category'=> 'COMPTABILITÉ',
                'level'   => 'avance',
                'duration_full'       => '8 semaines',
                'duration_alternance' => '4h / semaine',
                'image'    => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=1600&q=80',
                'image_alt'=> 'Commissariat aux comptes',
                'badge'    => 'Best-seller',
                'popular'  => true,
                'price'    => 250000,
                'provider' => 'KMAA Audit',
                'start_date' => '15 Novembre 2026',
                'description' => 'Devenez un expert du commissariat aux comptes et maîtrisez les normes OHADA et ISA.',
                'long_description' => '<p>Cette formation vous plonge au cœur des missions de commissariat aux comptes. Vous apprendrez à vérifier la conformité des comptes, à évaluer les risques et à rédiger des rapports d\'audit légaux.</p>',
                'modules' => [
                    ['title' => 'Cadre juridique du commissariat', 'topics' => ['Rôle et responsabilités', 'Nomination et révocation', 'Déontologie']],
                    ['title' => 'Normes ISA', 'topics' => ['ISA 200 à 299', 'ISA 300 à 499', 'ISA 500 à 599']],
                    ['title' => 'Évaluation des risques', 'topics' => ['Risque d\'audit', 'Seuil de signification', 'Approche par les risques']],
                    ['title' => 'Contrôle des comptes', 'topics' => ['Vérification des actifs', 'Vérification des passifs', 'Vérification des capitaux propres']],
                    ['title' => 'Rapport du commissaire', 'topics' => ['Types de rapports', 'Certification des comptes', 'Refus de certification']],
                    ['title' => 'Missions spéciales', 'topics' => ['Audit d\'acquisition', 'Audit de fusion', 'Audit de transformation']],
                    ['title' => 'Cas pratiques OHADA', 'topics' => ['Étude de cas réels', 'Simulation de mission', 'Rédaction du rapport']],
                    ['title' => 'Évaluation finale', 'topics' => ['Examen blanc', 'Correction collective', 'Certification']],
                ],
            ],

            // ─────────────────────────────────────────
            // 3. CONSOLIDATION & IFRS
            // ─────────────────────────────────────────
            [
                'slug'    => 'consolidation-comptes-ifrs',
                'title'   => 'Consolidation des comptes et normes IFRS',
                'subject' => 'Expertise Comptable',
                'category'=> 'COMPTABILITÉ',
                'level'   => 'avance',
                'duration_full'       => '10 semaines',
                'duration_alternance' => '5h / semaine',
                'image'    => 'https://images.unsplash.com/photo-1554224154-26032ffc0d07?auto=format&fit=crop&w=1600&q=80',
                'image_alt'=> 'Consolidation comptable',
                'badge'    => 'Certifiant',
                'popular'  => false,
                'price'    => 300000,
                'provider' => 'KMAA Academy',
                'start_date' => '5 Décembre 2026',
                'description' => 'Maîtrisez la consolidation des comptes et les normes internationales IFRS.',
                'long_description' => '<p>Apprenez à établir des comptes consolidés conformément aux normes IFRS.</p>',
                'modules' => [
                    ['title' => 'Introduction à la consolidation', 'topics' => ['Définition et objectifs', 'Cadre juridique OHADA', 'Périmètre de consolidation']],
                    ['title' => 'Méthodes de consolidation', 'topics' => ['Intégration globale', 'Intégration proportionnelle', 'Mise en équivalence']],
                    ['title' => 'Retraitements IFRS', 'topics' => ['Retraitements des comptes sociaux', 'Homogénéisation des méthodes', 'Conversion des devises']],
                    ['title' => 'Élimination des comptes réciproques', 'topics' => ['Élimination des créances et dettes', 'Élimination des produits et charges', 'Élimination des dividendes']],
                    ['title' => 'Partage des capitaux propres', 'topics' => ['Part du groupe', 'Intérêts minoritaires', 'Écarts d\'acquisition']],
                    ['title' => 'Consolidation et IFRS', 'topics' => ['IFRS 3', 'IFRS 10', 'IFRS 11']],
                    ['title' => 'Documents de synthèse consolidés', 'topics' => ['Bilan consolidé', 'Compte de résultat consolidé', 'Annexes']],
                    ['title' => 'Analyse financière des comptes consolidés', 'topics' => ['Ratios', 'Flux de trésorerie', 'Rentabilité']],
                    ['title' => 'Cas pratiques', 'topics' => ['Groupe simple', 'Groupe complexe', 'Cas OHADA']],
                    ['title' => 'Évaluation finale', 'topics' => ['Examen de certification', 'Cas complet', 'Remise des certificats']],
                ],
            ],

            // ─────────────────────────────────────────
            // 4. DIGITALISATION PME
            // ─────────────────────────────────────────
            [
                'slug'    => 'strategie-digitalisation-pme',
                'title'   => 'Stratégie de digitalisation pour les PME africaines',
                'subject' => 'Conseil & Stratégie',
                'category'=> 'STRATÉGIE',
                'level'   => 'debutant',
                'duration_full'       => '4 semaines',
                'duration_alternance' => '2h / semaine',
                'image'    => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1600&q=80',
                'image_alt'=> 'Digitalisation PME',
                'badge'    => 'Nouveau',
                'popular'  => false,
                'price'    => 100000,
                'provider' => 'KMAA Conseil',
                'start_date' => '1 Octobre 2026',
                'description' => 'Accompagnez la transformation digitale de votre entreprise.',
                'modules' => [
                    ['title' => 'Diagnostic digital', 'topics' => ['État des lieux', 'Analyse des besoins', 'Priorisation']],
                    ['title' => 'Outils digitaux', 'topics' => ['CRM', 'ERP', 'Réseaux sociaux']],
                    ['title' => 'Mise en œuvre', 'topics' => ['Plan d\'action', 'Budget', 'Formation des équipes']],
                    ['title' => 'Évaluation', 'topics' => ['KPI', 'Retour sur investissement', 'Ajustements']],
                ],
            ],

            // ─────────────────────────────────────────
            // 5. CONTRÔLE DE GESTION
            // ─────────────────────────────────────────
            [
                'slug'    => 'controle-gestion-performance',
                'title'   => 'Contrôle de gestion : Outils et pilotage de la performance',
                'subject' => 'Finance & Banque',
                'category'=> 'FINANCE',
                'level'   => 'intermediaire',
                'duration_full'       => '6 semaines',
                'duration_alternance' => '3h / semaine',
                'image'    => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1600&q=80',
                'image_alt'=> 'Contrôle de gestion',
                'badge'    => 'Certifiant',
                'popular'  => false,
                'price'    => 180000,
                'provider' => 'KMAA Academy',
                'start_date' => '10 Novembre 2026',
                'description' => 'Pilotez la performance de votre organisation avec des outils efficaces.',
                'modules' => [
                    ['title' => 'Introduction au contrôle de gestion', 'topics' => ['Définition et rôle', 'Positionnement dans l\'entreprise', 'Évolution']],
                    ['title' => 'Comptabilité analytique', 'topics' => ['Charges directes et indirectes', 'Méthodes de calcul des coûts', 'Coût de revient']],
                    ['title' => 'Budgets', 'topics' => ['Budget des ventes', 'Budget de production', 'Budget de trésorerie']],
                    ['title' => 'Tableaux de bord', 'topics' => ['Indicateurs de performance', 'Balanced Scorecard', 'Reporting']],
                    ['title' => 'Analyse des écarts', 'topics' => ['Écarts sur charges', 'Écarts sur produits', 'Actions correctives']],
                    ['title' => 'Évaluation finale', 'topics' => ['Cas pratique', 'Examen', 'Certification']],
                ],
            ],

            // ─────────────────────────────────────────
            // 6. FISCALITÉ
            // ─────────────────────────────────────────
            [
                'slug'    => 'fiscalite-entreprises-optimisation',
                'title'   => 'Fiscalité des entreprises : Optimisation et contentieux',
                'subject' => 'Fiscalité & Droit',
                'category'=> 'FISCALITÉ',
                'level'   => 'avance',
                'duration_full'       => '8 semaines',
                'duration_alternance' => '3h / semaine',
                'image'    => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=1600&q=80',
                'image_alt'=> 'Fiscalité entreprise',
                'badge'    => 'Best-seller',
                'popular'  => true,
                'price'    => 200000,
                'provider' => 'KMAA Juridique',
                'start_date' => '20 Novembre 2026',
                'description' => 'Maîtrisez la fiscalité des entreprises et gérez les contentieux.',
                'modules' => [
                    ['title' => 'Introduction à la fiscalité', 'topics' => ['Principes fondamentaux', 'Sources du droit fiscal', 'Organisation de l\'administration fiscale']],
                    ['title' => 'TVA', 'topics' => ['Champ d\'application', 'Territorialité', 'Déclarations']],
                    ['title' => 'Impôt sur les sociétés', 'topics' => ['Champ d\'application', 'Calcul du résultat fiscal', 'Taux et paiements']],
                    ['title' => 'Impôt sur le revenu', 'topics' => ['Traitements et salaires', 'Revenus fonciers', 'BIC et BNC']],
                    ['title' => 'Optimisation fiscale', 'topics' => ['Choix de la structure', 'Régimes fiscaux', 'Incitations fiscales']],
                    ['title' => 'Contentieux fiscal', 'topics' => ['Contrôle fiscal', 'Procédures de redressement', 'Recours']],
                    ['title' => 'Cas pratiques', 'topics' => ['Étude de cas', 'Simulation de contrôle', 'Rédaction de mémoires']],
                    ['title' => 'Évaluation finale', 'topics' => ['Examen blanc', 'Correction', 'Certification']],
                ],
            ],

            // ─────────────────────────────────────────
            // 7. EXCEL + POWER BI
            // ─────────────────────────────────────────
            [
                'slug'    => 'analyse-donnees-excel-power-bi',
                'title'   => 'Analyse de données avec Excel et Power BI',
                'subject' => 'Data & Tech',
                'category'=> 'DATA',
                'level'   => 'intermediaire',
                'duration_full'       => '5 semaines',
                'duration_alternance' => '3h / semaine',
                'image'    => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1600&q=80',
                'image_alt'=> 'Analyse de données',
                'badge'    => 'Best-seller',
                'popular'  => true,
                'price'    => 120000,
                'provider' => 'KMAA Tech',
                'start_date' => '5 Octobre 2026',
                'description' => 'Transformez vos données en décisions stratégiques.',
                'modules' => [
                    ['title' => 'Excel avancé', 'topics' => ['Tableaux croisés dynamiques', 'Fonctions avancées', 'Power Query']],
                    ['title' => 'Introduction à Power BI', 'topics' => ['Interface', 'Connexion aux données', 'Modélisation']],
                    ['title' => 'Visualisation de données', 'topics' => ['Types de graphiques', 'Mise en forme', 'Interactivité']],
                    ['title' => 'DAX', 'topics' => ['Mesures', 'Colonnes calculées', 'Fonctions temps']],
                    ['title' => 'Projet final', 'topics' => ['Construction d\'un tableau de bord', 'Storytelling', 'Présentation']],
                ],
            ],

            // ─────────────────────────────────────────
            // 8. CYBERSÉCURITÉ
            // ─────────────────────────────────────────
            [
                'slug'    => 'cybersecurite-dirigeants',
                'title'   => 'Cybersécurité pour les dirigeants et managers',
                'subject' => 'Data & Tech',
                'category'=> 'CYBERSÉCURITÉ',
                'level'   => 'debutant',
                'duration_full'       => '3 semaines',
                'duration_alternance' => '2h / semaine',
                'image'    => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1600&q=80',
                'image_alt'=> 'Cybersécurité',
                'badge'    => 'Nouveau',
                'popular'  => false,
                'price'    => 90000,
                'provider' => 'KMAA Tech',
                'start_date' => '15 Octobre 2026',
                'description' => 'Protégez votre organisation contre les cybermenaces.',
                'modules' => [
                    ['title' => 'Panorama des menaces', 'topics' => ['Types d\'attaques', 'Phishing', 'Rançongiciels']],
                    ['title' => 'Bonnes pratiques', 'topics' => ['Gestion des mots de passe', 'Sauvegardes', 'Mises à jour']],
                    ['title' => 'Plan de réponse', 'topics' => ['Détection', 'Réaction', 'Communication de crise']],
                ],
            ],

            // ─────────────────────────────────────────
            // 9. LEADERSHIP
            // ─────────────────────────────────────────
            [
                'slug'    => 'leadership-management-distance',
                'title'   => 'Leadership et Management d\'équipe à distance',
                'subject' => 'Management & RH',
                'category'=> 'MANAGEMENT',
                'level'   => 'intermediaire',
                'duration_full'       => '4 semaines',
                'duration_alternance' => '2h / semaine',
                'image'    => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1600&q=80',
                'image_alt'=> 'Management équipe',
                'badge'    => 'Best-seller',
                'popular'  => true,
                'price'    => 110000,
                'provider' => 'KMAA RH',
                'start_date' => '1 Novembre 2026',
                'description' => 'Développez vos compétences de leader pour manager à distance.',
                'modules' => [
                    ['title' => 'Les fondamentaux du leadership', 'topics' => ['Styles de leadership', 'Intelligence émotionnelle', 'Confiance']],
                    ['title' => 'Communication à distance', 'topics' => ['Outils collaboratifs', 'Réunions efficaces', 'Feedback']],
                    ['title' => 'Motivation et engagement', 'topics' => ['Reconnaissance', 'Objectifs SMART', 'Autonomie']],
                    ['title' => 'Gestion de la performance', 'topics' => ['Évaluation', 'Coaching', 'Développement des talents']],
                ],
            ],

            // ─────────────────────────────────────────
            // 10. CRÉATION ENTREPRISE
            // ─────────────────────────────────────────
            [
                'slug'    => 'creation-entreprise-individuelle',
                'title'   => 'Création et gestion d\'une entreprise individuelle',
                'subject' => 'Entrepreneuriat',
                'category'=> 'ENTREPRENEURIAT',
                'level'   => 'debutant',
                'duration_full'       => '6 semaines',
                'duration_alternance' => '3h / semaine',
                'image'    => 'https://images.unsplash.com/photo-1553877522-43269d4ea984?auto=format&fit=crop&w=1600&q=80',
                'image_alt'=> 'Entrepreneuriat',
                'badge'    => 'Nouveau',
                'popular'  => false,
                'price'    => 80000,
                'provider' => 'KMAA Conseil',
                'start_date' => '10 Octobre 2026',
                'description' => 'De l\'idée à la création : lancez votre entreprise.',
                'modules' => [
                    ['title' => 'L\'idée d\'entreprise', 'topics' => ['Identification de l\'opportunité', 'Étude de marché', 'Analyse concurrentielle']],
                    ['title' => 'Le business plan', 'topics' => ['Structure', 'Prévisions financières', 'Stratégie marketing']],
                    ['title' => 'Formalités juridiques', 'topics' => ['Choix du statut', 'Immatriculation', 'Obligations comptables']],
                    ['title' => 'Financement', 'topics' => ['Sources de financement', 'Dossier bancaire', 'Aides et subventions']],
                    ['title' => 'Gestion au quotidien', 'topics' => ['Comptabilité simplifiée', 'Gestion de trésorerie', 'Fiscalité']],
                    ['title' => 'Développement', 'topics' => ['Stratégie de croissance', 'Digitalisation', 'Réseautage']],
                ],
            ],

            // ─────────────────────────────────────────
            // 11. ANGLAIS PROFESSIONNEL (sans détail)
            // ─────────────────────────────────────────
            [
                'slug'    => 'anglais-professionnel-finance-audit',
                'title'   => 'Anglais professionnel : Finance et Audit',
                'subject' => 'Langues & Soft Skills',
                'category'=> 'LANGUES',
                'level'   => 'debutant',
                'duration_full'       => '8 semaines',
                'duration_alternance' => '2h / semaine',
                'image'    => 'https://images.unsplash.com/photo-1543269865-cbf427effbad?auto=format&fit=crop&w=1600&q=80',
                'image_alt'=> 'Anglais professionnel',
                'badge'    => null,
                'popular'  => false,
                'price'    => 80000,
                'provider' => 'KMAA Langues',
                'start_date' => '1 Novembre 2026',
                'description' => 'Maîtrisez l\'anglais professionnel pour les métiers de la finance et de l\'audit.',
                'modules' => [
                    ['title' => 'Business English Basics', 'topics' => ['Vocabulary', 'Grammar essentials', 'Business expressions']],
                    ['title' => 'Finance terminology', 'topics' => ['Financial statements', 'Audit vocabulary', 'Accounting terms']],
                    ['title' => 'Professional communication', 'topics' => ['Emails', 'Reports', 'Presentations']],
                ],
            ],

            // ─────────────────────────────────────────
            // 12. GESTION SANTÉ (sans détail)
            // ─────────────────────────────────────────
            [
                'slug'    => 'gestion-administrative-sante',
                'title'   => 'Gestion administrative des structures de santé',
                'subject' => 'Santé & Administration',
                'category'=> 'SANTÉ',
                'level'   => 'debutant',
                'duration_full'       => '4 semaines',
                'duration_alternance' => '2h / semaine',
                'image'    => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1600&q=80',
                'image_alt'=> 'Gestion santé',
                'badge'    => null,
                'popular'  => false,
                'price'    => 75000,
                'provider' => 'KMAA Santé',
                'start_date' => '1 Décembre 2026',
                'description' => 'Maîtrisez l\'administration des structures de santé.',
                'modules' => [
                    ['title' => 'Introduction à la gestion de santé', 'topics' => ['Rôles et responsabilités', 'Organisation hospitalière', 'Cadre juridique']],
                    ['title' => 'Gestion des ressources', 'topics' => ['Ressources humaines', 'Ressources matérielles', 'Ressources financières']],
                    ['title' => 'Qualité et sécurité', 'topics' => ['Normes de qualité', 'Sécurité des patients', 'Hygiène hospitalière']],
                    ['title' => 'Systèmes d\'information', 'topics' => ['Dossiers médicaux', 'Logiciels de gestion', 'Confidentialité']],
                ],
            ],
        ];
    }
}
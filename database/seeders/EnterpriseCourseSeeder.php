<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Module;
use App\Models\Sequence;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnterpriseCourseSeeder extends Seeder
{
    public function run(): void
    {
        $instructor = User::whereHas('roles', function ($q) {
                $q->where('name', 'formateur');
            })->first()
            ?? User::first();

        if (! $instructor) {
            $this->command->error('❌ Aucun utilisateur trouvé.');
            return;
        }

        $this->command->info("🏢 Formateur : {$instructor->name}");

        $coursesData = $this->getEnterpriseCoursesData();
        $stats = ['courses' => 0, 'modules' => 0, 'chapters' => 0, 'sequences' => 0];

        foreach ($coursesData as $data) {
            $this->command->info("→ [ENT] {$data['title']}");

            $course = Course::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'type'                   => 'enterprise',
                    'title'                  => $data['title'],
                    'description'            => $data['description'],
                    'long_description'       => $data['long_description'] ?? null,
                    'subject'                => $data['subject'],
                    'category'               => $data['category'],
                    'level'                  => $data['level'],
                    'diploma'                => 'attestation',
                    'duration_full'          => $data['duration_full'],
                    'duration_alternance'    => $data['duration_full'],
                    'image'                  => $data['image'],
                    'image_alt'              => $data['image_alt'],
                    'popular'                => $data['popular'] ?? false,
                    'badge'                  => $data['badge'] ?? null,
                    'is_free'                => false,
                    'has_certificate'        => true,
                    'instructor_id'          => $instructor->id,
                    'price'                  => $data['price'] ?? 0,
                    'enterprise_base_price'  => $data['price'] ?? 0,
                    'provider'               => 'KMAA Entreprises',
                    'language'               => 'Français',
                    'delivery_mode'          => $data['delivery_mode'] ?? 'mixte',
                    'hours_per_week'         => $data['hours_per_week'] ?? null,
                    'modules_count'          => count($data['modules'] ?? []),
                    'start_date'             => $data['start_date'] ?? null,
                ]
            );

            $stats['courses']++;

            // Modules + chapitres + séquences
            foreach ($data['modules'] ?? [] as $modIndex => $moduleData) {
                $module = Module::updateOrCreate(
                    ['course_id' => $course->id, 'position' => $modIndex],
                    ['title' => $moduleData['title']]
                );
                $stats['modules']++;

                $chapter = Chapter::updateOrCreate(
                    ['course_id' => $course->id, 'module_id' => $module->id, 'position' => 0],
                    ['title' => $moduleData['title'], 'week_number' => $modIndex + 1]
                );
                $stats['chapters']++;

                foreach (($moduleData['topics'] ?? []) as $i => $topic) {
                    Sequence::updateOrCreate(
                        ['chapter_id' => $chapter->id, 'position' => $i],
                        ['title' => $topic, 'type' => 'document', 'duration' => '15 min']
                    );
                    $stats['sequences']++;
                }
            }
        }

        $this->command->info('');
        $this->command->info('════════════════════════════════════════');
        $this->command->info("✅ {$stats['courses']} formations entreprises");
        $this->command->info("✅ {$stats['modules']} modules");
        $this->command->info("✅ {$stats['chapters']} chapitres");
        $this->command->info("✅ {$stats['sequences']} séquences");
        $this->command->info('════════════════════════════════════════');
    }

    private function getEnterpriseCoursesData(): array
    {
        return [
            [
                'slug' => 'commissariat-comptes-ohada-isa-entreprises',
                'title' => 'Commissariat aux comptes : Normes OHADA et ISA pour les entreprises',
                'subject' => 'Commissariat aux comptes',
                'category' => 'AUDIT',
                'level' => 'avance',
                'duration_full' => '12 semaines',
                'hours_per_week' => '6h / semaine',
                'delivery_mode' => 'mixte',
                'image' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=1600&q=80',
                'image_alt' => 'Commissariat aux comptes entreprise',
                'badge' => 'Certifiant',
                'popular' => true,
                'price' => 1500000,
                'start_date' => '15 Novembre 2026',
                'description' => 'Formez vos équipes aux missions de commissariat aux comptes conformément aux normes OHADA et ISA.',
                'long_description' => '<p>Cette formation intensive prépare vos collaborateurs à mener des missions de commissariat aux comptes en conformité avec les normes OHADA et les normes internationales d\'audit (ISA).</p>',
                'modules' => [
                    ['title' => 'Cadre juridique OHADA', 'topics' => ['Rôle du commissaire aux comptes', 'Nomination et révocation', 'Déontologie']],
                    ['title' => 'Normes ISA', 'topics' => ['ISA 200 à 299', 'ISA 300 à 499', 'ISA 500 à 599']],
                    ['title' => 'Évaluation des risques', 'topics' => ['Risque d\'audit', 'Seuil de signification', 'Approche par les risques']],
                    ['title' => 'Contrôle des comptes', 'topics' => ['Vérification des actifs', 'Vérification des passifs', 'Vérification des capitaux propres']],
                    ['title' => 'Rapport du commissaire', 'topics' => ['Types de rapports', 'Certification des comptes', 'Refus de certification']],
                    ['title' => 'Missions spéciales', 'topics' => ['Audit d\'acquisition', 'Audit de fusion', 'Audit de transformation']],
                    ['title' => 'Cas pratiques OHADA', 'topics' => ['Étude de cas réels', 'Simulation de mission', 'Rédaction du rapport']],
                    ['title' => 'Évaluation finale', 'topics' => ['Examen blanc', 'Correction collective', 'Certification']],
                ],
            ],

            [
                'slug' => 'actuariat-modelisation-risques-entreprises',
                'title' => 'Actuariat : Modélisation des risques et provisionnement',
                'subject' => 'Actuariat',
                'category' => 'FINANCE',
                'level' => 'avance',
                'duration_full' => '16 semaines',
                'hours_per_week' => '5h / semaine',
                'delivery_mode' => 'en_ligne',
                'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1600&q=80',
                'image_alt' => 'Actuariat',
                'badge' => 'Certifiant',
                'popular' => false,
                'price' => 2500000,
                'start_date' => '20 Novembre 2026',
                'description' => 'Maîtrisez les techniques actuarielles pour évaluer et provisionner les risques de votre entreprise.',
                'modules' => [
                    ['title' => 'Introduction à l\'actuariat', 'topics' => ['Définition et rôle', 'Domaines d\'application', 'Éthique']],
                    ['title' => 'Mathématiques financières', 'topics' => ['Valeur temps', 'Actualisation', 'Taux d\'intérêt']],
                    ['title' => 'Théorie du risque', 'topics' => ['Lois de probabilité', 'Espérance', 'Variance']],
                    ['title' => 'Assurance vie', 'topics' => ['Tables de mortalité', 'Primes', 'Réserves']],
                    ['title' => 'Assurance non-vie', 'topics' => ['Fréquence', 'Coût', 'Tarification']],
                    ['title' => 'Provisionnement', 'topics' => ['Provisions', 'Sinistres', 'Chain-Ladder']],
                    ['title' => 'Solvabilité II', 'topics' => ['Pilier 1', 'Pilier 2', 'Pilier 3']],
                    ['title' => 'Modélisation', 'topics' => ['Régression', 'GLM', 'Séries temporelles']],
                    ['title' => 'Applications', 'topics' => ['Portefeuille', 'Dérivés', 'Risque marché']],
                    ['title' => 'Cas pratiques', 'topics' => ['Études réelles', 'Travaux dirigés', 'Présentations']],
                    ['title' => 'Projet final', 'topics' => ['Sujet', 'Collecte', 'Modélisation']],
                    ['title' => 'Mémoire', 'topics' => ['Structure', 'Analyse', 'Recommandations']],
                    ['title' => 'Soutenance', 'topics' => ['Présentation', 'Questions', 'Feedback']],
                    ['title' => 'Évaluation finale', 'topics' => ['Examen', 'Certification', 'Remise diplômes']],
                ],
            ],

            [
                'slug' => 'audit-interne-avance-grands-groupes',
                'title' => 'Audit interne avancé pour les grands groupes',
                'subject' => 'Audit & Contrôle',
                'category' => 'AUDIT',
                'level' => 'avance',
                'duration_full' => '8 semaines',
                'hours_per_week' => '12h / semaine',
                'delivery_mode' => 'presentiel',
                'image' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1600&q=80',
                'image_alt' => 'Audit interne entreprise',
                'badge' => 'Best-seller',
                'popular' => true,
                'price' => 1800000,
                'start_date' => '5 Décembre 2026',
                'description' => 'Formation intensive en présentiel pour structurer votre fonction d\'audit interne.',
                'modules' => [
                    ['title' => 'Cadre de l\'audit interne', 'topics' => ['Normes IIA', 'Charte d\'audit', 'Positionnement']],
                    ['title' => 'Planification annuelle', 'topics' => ['Cartographie', 'Plan basé risques', 'Allocation']],
                    ['title' => 'Préparation mission', 'topics' => ['Connaissance', 'Analyse processus', 'Programme']],
                    ['title' => 'Techniques avancées', 'topics' => ['Échantillonnage', 'Tests conformité', 'Tests substantifs']],
                    ['title' => 'Audit SI', 'topics' => ['Contrôles IT', 'Applicatifs', 'Cybersécurité']],
                    ['title' => 'Rapport', 'topics' => ['Structure', 'Recommandations', 'Suivi']],
                    ['title' => 'Gouvernance', 'topics' => ['Conseil', 'Risques', 'Conformité']],
                    ['title' => 'Évaluation', 'topics' => ['Cas pratique', 'Examen', 'Certification']],
                ],
            ],

            [
                'slug' => 'expertise-comptable-consolidation-ifrs',
                'title' => 'Expertise comptable : Consolidation et normes IFRS',
                'subject' => 'Expertise comptable',
                'category' => 'COMPTABILITÉ',
                'level' => 'avance',
                'duration_full' => '14 semaines',
                'hours_per_week' => '7h / semaine',
                'delivery_mode' => 'mixte',
                'image' => 'https://images.unsplash.com/photo-1554224154-26032ffc0d07?auto=format&fit=crop&w=1600&q=80',
                'image_alt' => 'Expertise comptable',
                'badge' => 'Certifiant',
                'popular' => false,
                'price' => 2200000,
                'start_date' => '10 Décembre 2026',
                'description' => 'Perfectionnez vos équipes comptables sur la consolidation et les normes internationales.',
                'modules' => [
                    ['title' => 'Introduction consolidation', 'topics' => ['Objectifs', 'Périmètre', 'Méthodes']],
                    ['title' => 'Cadre OHADA', 'topics' => ['Acte uniforme', 'Obligations', 'Sanctions']],
                    ['title' => 'Normes IFRS', 'topics' => ['IFRS 1-9', 'IFRS 10-17', 'Application']],
                    ['title' => 'Retraitements', 'topics' => ['Homogénéisation', 'Devises', 'Éliminations']],
                    ['title' => 'Méthode globale', 'topics' => ['Intégration', 'Partage', 'Écarts']],
                    ['title' => 'Autres méthodes', 'topics' => ['Proportionnelle', 'Équivalence', 'Cas complexes']],
                    ['title' => 'Documents synthèse', 'topics' => ['Bilan', 'Résultat', 'Annexes']],
                    ['title' => 'Analyse financière', 'topics' => ['Ratios', 'Flux', 'Rentabilité']],
                    ['title' => 'Cas simples', 'topics' => ['Mono-activité', 'Familial']],
                    ['title' => 'Cas complexes', 'topics' => ['Multi-activités', 'Internationaux']],
                    ['title' => 'Outils', 'topics' => ['Logiciels', 'TCD', 'Automatisation']],
                    ['title' => 'Projet final', 'topics' => ['Cas complet', 'Analyse', 'Présentation']],
                    ['title' => 'Révisions', 'topics' => ['Rappels', 'Q&R', 'Exercices']],
                    ['title' => 'Évaluation', 'topics' => ['Examen', 'Certification', 'Feedback']],
                ],
            ],

            [
                'slug' => 'conseil-strategie-transformation-digitale',
                'title' => 'Conseil en stratégie et transformation digitale',
                'subject' => 'Conseil & Stratégie',
                'category' => 'STRATÉGIE',
                'level' => 'intermediaire',
                'duration_full' => '6 semaines',
                'hours_per_week' => '4h / semaine',
                'delivery_mode' => 'en_ligne',
                'image' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1600&q=80',
                'image_alt' => 'Conseil stratégie',
                'badge' => 'Nouveau',
                'popular' => false,
                'price' => 1200000,
                'start_date' => '1 Décembre 2026',
                'description' => 'Accompagnez la transformation digitale de vos clients avec une méthode éprouvée.',
                'modules' => [
                    ['title' => 'Diagnostic stratégique', 'topics' => ['SWOT', 'BCG', 'Chaîne valeur']],
                    ['title' => 'Transformation digitale', 'topics' => ['Maturité', 'Roadmap', 'Priorisation']],
                    ['title' => 'Modèles économiques', 'topics' => ['Canvas', 'Innovation', 'Plateformes']],
                    ['title' => 'Conduite changement', 'topics' => ['Kotter', 'Communication', 'Formation']],
                    ['title' => 'Outils digitaux', 'topics' => ['CRM', 'ERP', 'Analytics']],
                    ['title' => 'Cas pratiques', 'topics' => ['Études', 'Présentations', 'Certification']],
                ],
            ],

            [
                'slug' => 'finance-entreprise-analyse-evaluation',
                'title' => 'Finance d\'entreprise : Analyse financière et évaluation',
                'subject' => 'Finance d\'entreprise',
                'category' => 'FINANCE',
                'level' => 'avance',
                'duration_full' => '10 semaines',
                'hours_per_week' => '5h / semaine',
                'delivery_mode' => 'mixte',
                'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1600&q=80',
                'image_alt' => 'Finance entreprise',
                'badge' => 'Certifiant',
                'popular' => false,
                'price' => 1600000,
                'start_date' => '15 Décembre 2026',
                'description' => 'Maîtrisez l\'analyse financière et l\'évaluation des entreprises.',
                'modules' => [
                    ['title' => 'États financiers', 'topics' => ['Bilan', 'Résultat', 'Flux']],
                    ['title' => 'Ratios', 'topics' => ['Liquidité', 'Solvabilité', 'Rentabilité']],
                    ['title' => 'Trésorerie', 'topics' => ['Flux', 'BFR', 'Cash-flow']],
                    ['title' => 'Évaluation', 'topics' => ['DCF', 'Comparables', 'Patrimoniale']],
                    ['title' => 'Investissement', 'topics' => ['VAN', 'TRI', 'Récupération']],
                    ['title' => 'Financement', 'topics' => ['Coût capital', 'Structure', 'Levier']],
                    ['title' => 'Risques', 'topics' => ['Change', 'Taux', 'Couverture']],
                    ['title' => 'Planification', 'topics' => ['Prévisions', 'Budgets', 'Scénarios']],
                    ['title' => 'Cas pratiques', 'topics' => ['Études', 'Excel', 'Présentations']],
                    ['title' => 'Évaluation', 'topics' => ['Examen', 'Certification', 'Feedback']],
                ],
            ],

            [
                'slug' => 'fiscalite-internationale-prix-transfert',
                'title' => 'Fiscalité internationale et prix de transfert',
                'subject' => 'Fiscalité & Droit',
                'category' => 'FISCALITÉ',
                'level' => 'avance',
                'duration_full' => '5 semaines',
                'hours_per_week' => '10h / semaine',
                'delivery_mode' => 'presentiel',
                'image' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=1600&q=80',
                'image_alt' => 'Fiscalité internationale',
                'badge' => 'Certifiant',
                'popular' => false,
                'price' => 1900000,
                'start_date' => '8 Décembre 2026',
                'description' => 'Maîtrisez la fiscalité internationale et les prix de transfert.',
                'modules' => [
                    ['title' => 'Introduction fiscalité', 'topics' => ['Principes', 'Sources', 'Conventions']],
                    ['title' => 'Prix de transfert', 'topics' => ['OCDE', 'Documentation', 'Comparables']],
                    ['title' => 'BEPS', 'topics' => ['Actions', 'Impact', 'Conformité']],
                    ['title' => 'Optimisation', 'topics' => ['Structures', 'Régimes', 'Risques']],
                    ['title' => 'Contentieux', 'topics' => ['Contrôles', 'Procédures', 'Recours']],
                ],
            ],

            [
                'slug' => 'data-analytics-finance-audit',
                'title' => 'Data Analytics pour la finance et l\'audit',
                'subject' => 'Data & Tech',
                'category' => 'DATA',
                'level' => 'intermediaire',
                'duration_full' => '8 semaines',
                'hours_per_week' => '4h / semaine',
                'delivery_mode' => 'en_ligne',
                'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1600&q=80',
                'image_alt' => 'Data Analytics',
                'badge' => 'Nouveau',
                'popular' => false,
                'price' => 1400000,
                'start_date' => '25 Novembre 2026',
                'description' => 'Exploitez la data pour améliorer vos missions d\'audit et de conseil.',
                'modules' => [
                    ['title' => 'Introduction data', 'topics' => ['Types', 'Sources', 'Qualité']],
                    ['title' => 'Excel avancé', 'topics' => ['TCD', 'Power Query', 'Fonctions']],
                    ['title' => 'SQL', 'topics' => ['Requêtes', 'Jointures', 'Agrégations']],
                    ['title' => 'Power BI', 'topics' => ['Modélisation', 'Visualisation', 'DAX']],
                    ['title' => 'Python', 'topics' => ['Pandas', 'NumPy', 'Visualisation']],
                    ['title' => 'Automatisation', 'topics' => ['Scripts', 'Scheduling', 'Reporting']],
                    ['title' => 'Applications audit', 'topics' => ['Risques', 'Anomalies', 'Tests']],
                    ['title' => 'Projet final', 'topics' => ['Cas', 'Dashboard', 'Présentation']],
                ],
            ],

            [
                'slug' => 'management-leadership-cadres-dirigeants',
                'title' => 'Management et leadership pour cadres dirigeants',
                'subject' => 'Management & Leadership',
                'category' => 'MANAGEMENT',
                'level' => 'avance',
                'duration_full' => '4 semaines',
                'hours_per_week' => '15h / semaine',
                'delivery_mode' => 'presentiel',
                'image' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1600&q=80',
                'image_alt' => 'Management leadership',
                'badge' => 'Best-seller',
                'popular' => true,
                'price' => 1700000,
                'start_date' => '3 Décembre 2026',
                'description' => 'Développez les compétences de leadership de vos managers et dirigeants.',
                'modules' => [
                    ['title' => 'Fondamentaux leadership', 'topics' => ['Styles', 'Intelligence émotionnelle', 'Vision']],
                    ['title' => 'Communication', 'topics' => ['Écoute', 'Feedback', 'Prise de parole']],
                    ['title' => 'Motivation', 'topics' => ['Reconnaissance', 'Objectifs', 'Autonomie']],
                    ['title' => 'Pilotage', 'topics' => ['Évaluation', 'Coaching', 'Développement']],
                ],
            ],

            [
                'slug' => 'cybersecurite-entreprises-gouvernance',
                'title' => 'Cybersécurité pour les entreprises : Stratégie et gouvernance',
                'subject' => 'Cybersécurité',
                'category' => 'CYBERSÉCURITÉ',
                'level' => 'intermediaire',
                'duration_full' => '6 semaines',
                'hours_per_week' => '5h / semaine',
                'delivery_mode' => 'mixte',
                'image' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1600&q=80',
                'image_alt' => 'Cybersécurité entreprise',
                'badge' => 'Certifiant',
                'popular' => false,
                'price' => 1300000,
                'start_date' => '28 Novembre 2026',
                'description' => 'Protégez votre entreprise contre les cybermenaces.',
                'modules' => [
                    ['title' => 'Panorama menaces', 'topics' => ['Attaques', 'Acteurs', 'Tendances']],
                    ['title' => 'Gouvernance', 'topics' => ['Politiques', 'Rôles', 'Conformité']],
                    ['title' => 'Sécurité technique', 'topics' => ['Firewall', 'Antivirus', 'Chiffrement']],
                    ['title' => 'Sécurité humaine', 'topics' => ['Formation', 'Phishing', 'Bonnes pratiques']],
                    ['title' => 'Réponse incidents', 'topics' => ['Détection', 'Réaction', 'Communication']],
                    ['title' => 'Continuité', 'topics' => ['PRA', 'Sauvegardes', 'Tests']],
                ],
            ],

            [
                'slug' => 'audit-systemes-information-si',
                'title' => 'Audit des systèmes d\'information (SI)',
                'subject' => 'Audit & Contrôle',
                'category' => 'AUDIT',
                'level' => 'avance',
                'duration_full' => '7 semaines',
                'hours_per_week' => '4h / semaine',
                'delivery_mode' => 'en_ligne',
                'image' => 'https://images.unsplash.com/photo-1518186285589-2f7649de83e0?auto=format&fit=crop&w=1600&q=80',
                'image_alt' => 'Audit SI',
                'badge' => 'Certifiant',
                'popular' => false,
                'price' => 1350000,
                'start_date' => '12 Décembre 2026',
                'description' => 'Auditez la sécurité et la performance des SI.',
                'modules' => [
                    ['title' => 'Introduction audit SI', 'topics' => ['Objectifs', 'Normes', 'Référentiels']],
                    ['title' => 'Gouvernance IT', 'topics' => ['COBIT', 'ITIL', 'Alignement']],
                    ['title' => 'Contrôles généraux', 'topics' => ['Accès', 'Changements', 'Exploitation']],
                    ['title' => 'Contrôles applicatifs', 'topics' => ['Intégrité', 'Validation', 'Piste']],
                    ['title' => 'Cybersécurité', 'topics' => ['Vulnérabilités', 'Tests intrusion', 'SIEM']],
                    ['title' => 'Continuité', 'topics' => ['PRA', 'PCA', 'Tests']],
                    ['title' => 'Rapport', 'topics' => ['Structure', 'Recommandations', 'Suivi']],
                ],
            ],

            [
                'slug' => 'commissariat-apports-fusion-acquisition',
                'title' => 'Commissariat aux apports et fusion-acquisition',
                'subject' => 'Commissariat aux comptes',
                'category' => 'FINANCE',
                'level' => 'avance',
                'duration_full' => '6 semaines',
                'hours_per_week' => '8h / semaine',
                'delivery_mode' => 'presentiel',
                'image' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1600&q=80',
                'image_alt' => 'Fusion acquisition',
                'badge' => 'Best-seller',
                'popular' => false,
                'price' => 2100000,
                'start_date' => '18 Décembre 2026',
                'description' => 'Formation avancée sur le commissariat aux apports et M&A.',
                'modules' => [
                    ['title' => 'Cadre juridique', 'topics' => ['OHADA', 'Fusion', 'Scission']],
                    ['title' => 'Commissariat apports', 'topics' => ['Rôle', 'Méthodes', 'Rapport']],
                    ['title' => 'Évaluation', 'topics' => ['Méthodes', 'Choix', 'Application']],
                    ['title' => 'Due diligence', 'topics' => ['Financière', 'Juridique', 'Fiscale']],
                    ['title' => 'Négociation', 'topics' => ['Term sheet', 'Closing', 'Intégration']],
                    ['title' => 'Cas pratiques', 'topics' => ['Études', 'Simulation', 'Certification']],
                ],
            ],
        ];
    }
}
<?php

namespace Database\Seeders;

use App\Models\NewsArticle;
use Illuminate\Database\Seeder;

class NewsArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            ['slug' => 'formation-continue-carrieres', 'category' => 'formation', 'featured' => true,
             'title' => "Comment la formation continue transforme les carrières en Afrique de l'Ouest",
             'excerpt' => "Une étude de terrain menée auprès de nos anciens apprenants révèle l'impact concret des parcours certifiants sur l'évolution professionnelle.",
             'content' => "La formation continue n'est plus un luxe : c'est un levier de mobilité professionnelle en Afrique de l'Ouest.\n\nChez KMAA, les apprenants qui terminent un parcours structuré progressent plus vite en responsabilité et en confiance.\n\nTrois leviers reviennent souvent : la pratique sur cas réels, l'accompagnement pédagogique, et la reconnaissance auprès des employeurs.",
             'image' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=1200&q=80',
             'image_alt' => 'Jeune professionnelle travaillant sur un ordinateur portable',
             'author_name' => 'Aïcha Ouédraogo', 'author_role' => 'Responsable pédagogique KMAA',
             'author_avatar' => 'https://images.unsplash.com/photo-1531123897727-8f129e1688ce?w=200&q=80',
             'read_time' => 6, 'published_at' => '2026-08-28'],

            ['slug' => 'kmaa-entreprise-transformation-digitale', 'category' => 'entreprise', 'featured' => true,
             'title' => 'KMAA Entreprise : 12 PME accompagnées dans leur transformation digitale',
             'excerpt' => "Retour sur un semestre d'accompagnement auprès d'entreprises burkinabè engagées dans la modernisation de leurs outils de gestion.",
             'content' => "Douze PME ont suivi un parcours intensif sur la digitalisation des process administratifs et financiers.\n\nL'objectif : passer d'outils dispersés à des tableaux de bord simples et actionnables.\n\nLes gains les plus visibles portent sur la trésorerie, la facturation et le reporting mensuel.",
             'image' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?w=1200&q=80',
             'image_alt' => 'Équipe en réunion autour d\'un ordinateur',
             'author_name' => 'Boureima Sawadogo', 'author_role' => 'Consultant KMAA Entreprise',
             'author_avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&q=80',
             'read_time' => 5, 'published_at' => '2026-08-24'],

            ['slug' => 'journee-portes-ouvertes', 'category' => 'evenement', 'featured' => true,
             'title' => 'Journée portes ouvertes KMAA : le programme complet',
             'excerpt' => "Ateliers, témoignages d'anciens apprenants et rencontres avec nos formateurs — tout ce qu'il faut savoir avant de venir.",
             'content' => "La journée portes ouvertes permet de découvrir les parcours, rencontrer l'équipe et poser toutes vos questions.\n\nAu programme : ateliers découverte, témoignages et sessions d'orientation personnalisées.",
             'image' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1200&q=80',
             'image_alt' => 'Public assistant à une conférence',
             'author_name' => 'Équipe KMAA', 'author_role' => 'Communication',
             'author_avatar' => 'https://images.unsplash.com/photo-1607346256330-dee7af15f7c5?w=200&q=80',
             'read_time' => 3, 'published_at' => '2026-08-20'],

            ['slug' => 'valoriser-formation-cv', 'category' => 'carriere',
             'title' => '5 compétences à mettre en avant sur votre CV après une formation certifiante',
             'excerpt' => "Nos conseillers carrière partagent leurs meilleures pratiques pour valoriser un parcours KMAA auprès des recruteurs.",
             'content' => "Savoir présenter sa formation est aussi important que de l'avoir suivie.\n\nMettez en avant les cas pratiques, les outils maîtrisés et les résultats concrets obtenus pendant le parcours.",
             'image' => 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=1200&q=80',
             'image_alt' => 'Entretien de recrutement',
             'author_name' => 'Fatou Zongo', 'author_role' => 'Conseillère insertion professionnelle',
             'author_avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=200&q=80',
             'read_time' => 4, 'published_at' => '2026-08-17'],

            ['slug' => 'modele-tableau-bord-controle-gestion', 'category' => 'ressources',
             'title' => 'Modèle gratuit : votre premier tableau de bord de contrôle de gestion',
             'excerpt' => "Téléchargez notre modèle Excel pas à pas pour structurer le suivi budgétaire de votre organisation dès aujourd'hui.",
             'content' => "Un bon tableau de bord commence par des indicateurs simples et suivis chaque mois.\n\nCe modèle vous aide à structurer budget, réalisations et écarts sans complexité inutile.\n\nAdaptez les onglets à votre activité, puis partagez une synthèse claire avec votre équipe.",
             'image' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=1200&q=80',
             'image_alt' => 'Analyse de graphiques financiers',
             'author_name' => 'Moussa Kaboré', 'author_role' => 'Formateur contrôle de gestion',
             'author_avatar' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=200&q=80',
             'read_time' => 2, 'published_at' => '2026-08-14'],

            ['slug' => 'parcours-fatou', 'category' => 'vie-kmaa',
             'title' => 'Portrait : Fatou, de la reconversion à la certification',
             'excerpt' => "Après 8 mois d'accompagnement personnalisé, Fatou a rejoint une équipe de gestion administrative à Ouagadougou.",
             'content' => "La reconversion de Fatou a commencé par un bilan de compétences chez KMAA.\n\nHuit mois plus tard, elle intégre une équipe de gestion administrative à Ouagadougou.\n\nSon conseil : oser poser la première question, même quand le chemin paraît long.",
             'image' => 'https://images.unsplash.com/photo-1531123897727-8f129e1688ce?w=1200&q=80',
             'image_alt' => 'Portrait d\'une jeune femme professionnelle',
             'author_name' => 'Rédaction KMAA', 'author_role' => "Vie de l'école",
             'author_avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=200&q=80',
             'read_time' => 5, 'published_at' => '2026-08-10'],

            ['slug' => 'nouveau-parcours-data-analyst', 'category' => 'formation',
             'title' => 'Nouveau parcours : Data Analyst, la formation qui monte',
             'excerpt' => "Face à la demande croissante des entreprises locales, KMAA lance un nouveau parcours dédié à l'analyse de données.",
             'content' => "La demande en profils data progresse fortement chez les entreprises locales.\n\nCe parcours combine Excel avancé, visualisation et cas pratiques métier.",
             'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1200&q=80',
             'image_alt' => 'Tableaux de bord de données',
             'author_name' => 'Ibrahim Traoré', 'author_role' => 'Responsable pédagogique Tech & Data',
             'author_avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&q=80',
             'read_time' => 4, 'published_at' => '2026-08-06'],

            ['slug' => 'audit-interne-externalise-pme', 'category' => 'entreprise',
             'title' => "Pourquoi de plus en plus de PME choisissent l'audit interne externalisé",
             'excerpt' => "Décryptage d'une tendance de fond dans la gestion des risques des petites et moyennes entreprises burkinabè.",
             'content' => "L'audit externalisé permet d'accéder à une expertise sans recruter immédiatement une équipe dédiée.\n\nIl aide aussi à structurer les contrôles et à rassurer partenaires et financeurs.",
             'image' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1200&q=80',
             'image_alt' => 'Documents financiers et calculatrice',
             'author_name' => 'Boureima Sawadogo', 'author_role' => 'Consultant KMAA Entreprise',
             'author_avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&q=80',
             'read_time' => 6, 'published_at' => '2026-08-02'],

            ['slug' => 'reconversion-apres-35-ans', 'category' => 'carriere',
             'title' => 'Se reconvertir après 35 ans : ce que nos apprenants nous ont appris',
             'excerpt' => "La reconversion professionnelle n'a pas d'âge. Témoignages et conseils pratiques pour se lancer sereinement.",
             'content' => "La reconversion après 35 ans est fréquente et tout à fait réaliste avec un accompagnement adapté.\n\nLe plus important : un objectif clair, un rythme soutenable, et une mise en pratique régulière.",
             'image' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=1200&q=80',
             'image_alt' => 'Professionnel lors d\'un entretien',
             'author_name' => 'Fatou Zongo', 'author_role' => 'Conseillère insertion professionnelle',
             'author_avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=200&q=80',
             'read_time' => 5, 'published_at' => '2026-07-29'],

            ['slug' => 'forum-emploi-kmaa', 'category' => 'evenement',
             'title' => 'Retour sur le forum emploi KMAA x entreprises partenaires',
             'excerpt' => "Plus de 200 apprenants ont rencontré nos entreprises partenaires lors de cette édition placée sous le signe de l'insertion.",
             'content' => "Le forum emploi relie directement apprenants et recruteurs partenaires de KMAA.\n\nAteliers CV, speed meetings et offres à pourvoir étaient au programme.",
             'image' => 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=1200&q=80',
             'image_alt' => 'Forum emploi',
             'author_name' => 'Équipe KMAA', 'author_role' => 'Communication',
             'author_avatar' => 'https://images.unsplash.com/photo-1607346256330-dee7af15f7c5?w=200&q=80',
             'read_time' => 4, 'published_at' => '2026-07-25'],

            ['slug' => 'guide-reussir-entretien-certification', 'category' => 'ressources',
             'title' => 'Guide pratique : réussir son entretien de certification',
             'excerpt' => "Toutes les étapes, les erreurs à éviter et les conseils de nos formateurs pour aborder sereinement votre évaluation finale.",
             'content' => "Préparez des exemples concrets tirés de vos exercices et cas pratiques.\n\nStructurez vos réponses : contexte, action, résultat.",
             'image' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1200&q=80',
             'image_alt' => 'Session de préparation',
             'author_name' => 'Moussa Kaboré', 'author_role' => 'Formateur contrôle de gestion',
             'author_avatar' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=200&q=80',
             'read_time' => 7, 'published_at' => '2026-07-21'],

            ['slug' => 'ceremonie-diplomes-2026', 'category' => 'vie-kmaa',
             'title' => 'KMAA fête ses nouveaux diplômés 2026',
             'excerpt' => "Retour en images sur la cérémonie de remise de diplômes qui a réuni apprenants, formateurs et partenaires.",
             'content' => "La cérémonie a réuni apprenants, formateurs et partenaires pour célébrer les parcours aboutis.\n\nUn moment fort pour l'équipe et pour chaque diplômé.",
             'image' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=1200&q=80',
             'image_alt' => 'Cérémonie de remise de diplômes',
             'author_name' => 'Rédaction KMAA', 'author_role' => "Vie de l'école",
             'author_avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=200&q=80',
             'read_time' => 3, 'published_at' => '2026-07-15'],
        ];

        foreach ($articles as $article) {
            NewsArticle::updateOrCreate(
                ['slug' => $article['slug']],
                [...$article, 'published' => true]
            );
        }

        $this->command->info('✅ ' . count($articles) . ' actualités seedées.');
    }
}
<?php

namespace Database\Seeders;

use App\Models\Masterclass;
use Illuminate\Database\Seeder;

class MasterclassSeeder extends Seeder
{
    public function run(): void
    {
        Masterclass::updateOrCreate(
            ['id' => 1],
            [
                'category' => 'LEADERSHIP • INNOVATION',
                'title' => 'Construire une vision qui transforme',
                'subtitle' => 'Leadership, innovation et impact : les clés pour transformer une idée en véritable projet.',
                'description' => "Une expérience immersive pensée pour les entrepreneurs, étudiants, professionnels et porteurs de projets qui souhaitent développer leur leadership et passer à l'action.",
                'date_label' => 'Samedi 12 septembre 2026',
                'time_label' => '18:00 – 20:00',
                'timezone' => 'GMT',
                'participants' => 284,
                'capacity' => 500,
                'language' => 'Français',
                'status' => 'live',
                'youtube_id' => 'qp0HIF3SfI4',

                'speaker_name' => 'M. Martial YAOGO',
                'speaker_role' => 'Expert Comptable',
                'speaker_organization' => 'KMAA Academy',
                'speaker_bio' => 'Entrepreneur, formateur et passionné par la transformation des organisations et le développement des talents.',
                'speaker_image' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=600&q=85',
                'speaker_stats' => [
                    ['value' => '12+', 'label' => "années d'expérience"],
                    ['value' => '80+', 'label' => 'conférences'],
                    ['value' => '2.4K', 'label' => 'apprenants'],
                ],

                'sessions' => [
                    ['time' => '18:00', 'duration' => '10 min', 'title' => 'Ouverture & bienvenue', 'description' => "Introduction, présentation de l'expert et découverte des objectifs de la session.", 'status' => 'done'],
                    ['time' => '18:10', 'duration' => '25 min', 'title' => 'Pourquoi les grands projets échouent-ils ?', 'description' => 'Comprendre les erreurs classiques et les blocages qui empêchent une idée de devenir un projet.', 'status' => 'done'],
                    ['time' => '18:35', 'duration' => '30 min', 'title' => 'Construire une vision forte', 'description' => 'Méthodes concrètes pour définir une vision claire, mobilisatrice et mesurable.', 'status' => 'live'],
                    ['time' => '19:05', 'duration' => '25 min', 'title' => 'Leadership & passage à l’action', 'description' => 'Transformer sa vision en décisions, habitudes et résultats.', 'status' => 'next'],
                    ['time' => '19:30', 'duration' => '20 min', 'title' => 'Questions & réponses', 'description' => 'Échange direct avec l’expert autour des problématiques des participants.', 'status' => 'next'],
                    ['time' => '19:50', 'duration' => '10 min', 'title' => 'Conclusion & challenge', 'description' => 'Synthèse, challenge personnel et prochaines étapes.', 'status' => 'next'],
                ],

                'videos' => [
                    ['id' => 'qp0HIF3SfI4', 'title' => 'How Great Leaders Inspire Action', 'speaker' => 'Simon Sinek — TED', 'duration' => '18:04', 'category' => 'Leadership', 'description' => 'Une référence incontournable sur la vision, le leadership et la capacité à inspirer.'],
                    ['id' => 'eIho2S0ZahI', 'title' => 'How to Speak So That People Want to Listen', 'speaker' => 'Julian Treasure — TED', 'duration' => '9:58', 'category' => 'Communication', 'description' => 'Comprendre comment communiquer avec plus d’impact et capter véritablement l’attention.'],
                ],

                'resources' => [
                    ['icon' => '📘', 'title' => 'Guide — Construire sa vision', 'type' => 'PDF', 'size' => '2.4 MB'],
                    ['icon' => '📊', 'title' => 'Canvas Vision & Objectifs', 'type' => 'PDF', 'size' => '1.1 MB'],
                    ['icon' => '🧠', 'title' => 'Checklist Leadership', 'type' => 'PDF', 'size' => '840 KB'],
                    ['icon' => '📝', 'title' => 'Notes de la Masterclass', 'type' => 'PDF', 'size' => '1.7 MB'],
                ],

                'quiz' => [
                    ['question' => 'Quel est le premier élément permettant de mobiliser une équipe autour d’un projet ?', 'options' => ['Une vision claire', 'Un budget important', 'Une grande équipe', 'Une campagne publicitaire'], 'correct' => 0],
                    ['question' => 'Quel est le meilleur moyen de transformer une vision en action ?', 'options' => ['Attendre le moment parfait', 'Définir des objectifs concrets', 'Multiplier les réunions', 'Changer régulièrement de stratégie'], 'correct' => 1],
                    ['question' => 'Un bon leader doit principalement savoir :', 'options' => ['Tout contrôler', 'Travailler seul', 'Donner du sens et mobiliser', 'Éviter les erreurs'], 'correct' => 2],
                ],

                'published' => true,
            ]
        );

        $this->command->info('✅ Masterclass seedée.');
    }
}

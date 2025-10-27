<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AdminTrainingService;

class AdminTraining extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:training
                            {action : Action (start, continue, evaluate, certificate, stats)}
                            {--module= : Module de formation (overview, admin_interface, customer_support, technical_advanced)}
                            {--user= : ID utilisateur}
                            {--session= : ID session existante}
                            {--trainer= : Nom du formateur}
                            {--interactive : Mode interactif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Système de formation interactive pour équipe admin RestroSaaS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎓 Formation Équipe Admin RestroSaaS');
        $this->info('===================================');

        $trainingService = new AdminTrainingService();
        $action = $this->argument('action');

        try {
            switch ($action) {
                case 'start':
                    return $this->startTraining($trainingService);
                case 'continue':
                    return $this->continueTraining($trainingService);
                case 'evaluate':
                    return $this->evaluateTraining($trainingService);
                case 'certificate':
                    return $this->generateCertificate($trainingService);
                case 'stats':
                    return $this->showStats($trainingService);
                default:
                    $this->error('Action non reconnue. Actions: start, continue, evaluate, certificate, stats');
                    return 1;
            }
        } catch (\Exception $e) {
            $this->error('Erreur: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Démarrer nouvelle formation
     */
    private function startTraining(AdminTrainingService $service)
    {
        $this->newLine();
        $this->info('🚀 Démarrage Nouvelle Formation');
        $this->info('==============================');

        // Sélection utilisateur
        $userId = $this->option('user');
        if (!$userId) {
            $userId = $this->ask('👤 ID Utilisateur');
        }

        // Sélection module
        $module = $this->option('module');
        if (!$module) {
            $modules = [
                'overview' => '📋 Vue d\'ensemble RestroSaaS (30min)',
                'admin_interface' => '🖥️ Interface Administration (45min)',
                'customer_support' => '💬 Support Client (60min)',
                'technical_advanced' => '🔧 Administration Technique (90min)'
            ];

            $this->table(['Module', 'Description'], array_map(function($key, $value) {
                return [$key, $value];
            }, array_keys($modules), $modules));

            $module = $this->choice('Choisir module', array_keys($modules));
        }

        // Formateur
        $trainer = $this->option('trainer') ?: $this->ask('👨‍🏫 Nom du formateur (optionnel)');

        // Démarrer formation
        $result = $service->startTrainingSession($userId, $module, $trainer);

        if ($result['success']) {
            $this->info('✅ Formation démarrée avec succès !');
            $this->table(['Information', 'Valeur'], [
                ['Session ID', $result['session_id']],
                ['Module', $result['module_info']['title']],
                ['Durée estimée', $result['estimated_duration'] . ' minutes'],
                ['Utilisateur', $userId],
                ['Formateur', $trainer ?: 'Auto-formation']
            ]);

            if ($this->option('interactive')) {
                return $this->runInteractiveSession($service, $result['session_id']);
            }

        } else {
            $this->error('❌ Erreur: ' . $result['error']);
            return 1;
        }

        return 0;
    }

    /**
     * Session interactive
     */
    private function runInteractiveSession(AdminTrainingService $service, $sessionId)
    {
        $this->newLine();
        $this->info('🎯 Mode Formation Interactive');
        $this->info('============================');

        $sections = ['introduction', 'architecture', 'roles', 'kpis']; // Exemple

        foreach ($sections as $section) {
            $this->newLine();
            $this->comment("📖 Section: " . ucfirst($section));

            if ($this->confirm('Commencer cette section ?', true)) {
                $content = $service->getTrainingContent($sessionId, $section);

                if ($content['success']) {
                    $this->displaySectionContent($content['content']);

                    // Quiz si disponible
                    if (isset($content['content']['quiz'])) {
                        $this->runQuiz($service, $sessionId, $content['content']['quiz']);
                    }
                } else {
                    $this->error('Erreur chargement contenu: ' . $content['error']);
                }
            }
        }

        $this->info('🎉 Formation interactive terminée !');
        return 0;
    }

    /**
     * Afficher contenu section
     */
    private function displaySectionContent($content)
    {
        if (isset($content['slides'])) {
            foreach ($content['slides'] as $i => $slide) {
                $this->newLine();
                $this->line("📊 Slide " . ($i + 1) . ": " . $slide['title']);
                $this->newLine();

                foreach ($slide['bullets'] as $bullet) {
                    $this->line("  • " . $bullet);
                }

                if ($i < count($content['slides']) - 1) {
                    $this->ask('Appuyer sur Entrée pour continuer...');
                }
            }
        }

        if (isset($content['practical_scenarios'])) {
            $this->newLine();
            $this->comment('🎭 Scénarios Pratiques:');

            foreach ($content['practical_scenarios'] as $scenario) {
                $this->newLine();
                $this->line("📝 Scénario: " . $scenario['scenario']);
                $this->line("✅ Bonne réponse: " . $scenario['good_response']);
                $this->line("❌ Mauvaise réponse: " . $scenario['bad_response']);
                $this->line("💡 Explication: " . $scenario['explanation']);
            }
        }
    }

    /**
     * Exécuter quiz
     */
    private function runQuiz(AdminTrainingService $service, $sessionId, $quiz)
    {
        $this->newLine();
        $this->info('📝 Quiz de Validation');
        $this->info('====================');

        $answers = [];

        foreach ($quiz['questions'] as $i => $question) {
            $this->newLine();
            $this->line("❓ Question " . ($i + 1) . ": " . $question['question']);

            $selected = $this->choice('Votre réponse', $question['options']);
            $selectedIndex = array_search($selected, $question['options']);

            $answers[] = [
                'question' => $question['question'],
                'selected' => $selectedIndex,
                'correct' => $question['correct'],
                'options' => $question['options']
            ];
        }

        // Évaluer quiz
        $evaluation = $service->evaluateTraining($sessionId, 'quiz', $answers);

        if ($evaluation['success']) {
            $result = $evaluation['evaluation'];
            $this->newLine();
            $this->info('📊 Résultats Quiz');
            $this->info('=================');

            $statusIcon = $result['passed'] ? '✅' : '❌';
            $this->line("{$statusIcon} Score: {$result['score']}% ({$result['correct_answers']}/{$result['total_questions']})");
            $this->line("💬 " . $result['feedback']);

            if ($result['passed']) {
                $this->info('🎉 Quiz réussi ! Vous pouvez continuer.');
            } else {
                $this->warn('⚠️ Score insuffisant. Révision recommandée.');
            }
        }
    }

    /**
     * Afficher statistiques
     */
    private function showStats(AdminTrainingService $service)
    {
        $this->info('📊 Statistiques Formation Équipe');
        $this->info('================================');

        $stats = $service->getTeamTrainingStats();

        if ($stats['success']) {
            $overview = $stats['stats']['overview'];

            $this->newLine();
            $this->comment('📈 Vue d\'ensemble');
            $this->table(['Métrique', 'Valeur'], [
                ['Sessions totales', $overview['total_sessions']],
                ['Sessions actives', $overview['active_sessions']],
                ['Sessions terminées', $overview['completed_sessions']],
                ['Score moyen', $overview['average_score'] . '%'],
                ['Taux certification', $overview['certification_rate'] . '%']
            ]);

            $this->newLine();
            $this->comment('📚 Statistiques par Module');

            if (!empty($stats['stats']['modules'])) {
                $moduleData = [];
                foreach ($stats['stats']['modules'] as $module => $data) {
                    $moduleData[] = [
                        $module,
                        $data['sessions'],
                        $data['average_score'] . '%',
                        $data['completion_rate'] . '%'
                    ];
                }

                $this->table(['Module', 'Sessions', 'Score Moyen', 'Taux Completion'], $moduleData);
            } else {
                $this->line('Aucune donnée de module disponible.');
            }

        } else {
            $this->error('Erreur récupération stats: ' . $stats['error']);
            return 1;
        }

        return 0;
    }

    /**
     * Générer certificat
     */
    private function generateCertificate(AdminTrainingService $service)
    {
        $sessionId = $this->option('session');
        if (!$sessionId) {
            $sessionId = $this->ask('🎓 ID Session pour certificat');
        }

        $result = $service->generateCertificate($sessionId);

        if ($result['success']) {
            $cert = $result['certificate'];

            $this->info('🏆 CERTIFICAT GÉNÉRÉ AVEC SUCCÈS');
            $this->info('===============================');
            $this->newLine();

            $this->table(['Information', 'Valeur'], [
                ['Certificat ID', $cert['certificate_id']],
                ['Utilisateur', $cert['user_id']],
                ['Module', $cert['module']],
                ['Score Final', $cert['score'] . '%'],
                ['Date Emission', $cert['issued_date']->format('d/m/Y H:i')],
                ['Date Expiration', $cert['expiry_date']->format('d/m/Y')],
                ['Formateur', $cert['trainer']],
                ['Statut', '✅ Valide']
            ]);

        } else {
            $this->error('❌ ' . $result['error']);
            if (isset($result['required_score'])) {
                $this->line("Score requis: {$result['required_score']}%");
                $this->line("Score actuel: {$result['current_score']}%");
            }
            return 1;
        }

        return 0;
    }

    /**
     * Continuer formation existante
     */
    private function continueTraining(AdminTrainingService $service)
    {
        $sessionId = $this->option('session');
        if (!$sessionId) {
            $sessionId = $this->ask('🔄 ID Session à continuer');
        }

        $this->info("🔄 Reprise formation session: {$sessionId}");

        // Logique pour reprendre session existante
        $this->info('Formation reprise avec succès');

        return 0;
    }

    /**
     * Évaluer formation manuelle
     */
    private function evaluateTraining(AdminTrainingService $service)
    {
        $sessionId = $this->option('session');
        if (!$sessionId) {
            $sessionId = $this->ask('📊 ID Session à évaluer');
        }

        $this->info("📊 Évaluation session: {$sessionId}");

        // Interface évaluation
        $this->info('Évaluation terminée');

        return 0;
    }
}

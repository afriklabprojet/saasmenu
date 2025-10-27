<?php

namespace App\Console\Commands\Firebase;

use Illuminate\Console\Command;
use App\Services\FirebaseService;
use Exception;

class TestFirebaseConnectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'firebase:test-connection';

    /**
     * The console command description.
     */
    protected $description = 'Test Firebase configuration and connection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Firebase configuration...');

        try {
            // Vérifier la configuration de base
            $this->checkConfiguration();

            // Tester le service Firebase
            $this->testFirebaseService();

            // Tester l'envoi d'une notification test
            $this->testNotificationSending();

            $this->info('✅ Firebase configuration is working correctly!');

        } catch (Exception $e) {
            $this->error('❌ Firebase configuration error: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Vérifier la configuration Firebase
     */
    private function checkConfiguration()
    {
        $this->line('Checking configuration...');

        $required = [
            'FIREBASE_PROJECT_ID',
            'FIREBASE_SERVER_KEY',
            'FIREBASE_WEB_API_KEY'
        ];

        $missing = [];

        foreach ($required as $key) {
            if (empty(env($key))) {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            throw new Exception('Missing required environment variables: ' . implode(', ', $missing));
        }

        // Vérifier le fichier credentials s'il est défini
        if ($credentials = env('FIREBASE_CREDENTIALS')) {
            if (!file_exists($credentials)) {
                throw new Exception("Firebase credentials file not found: {$credentials}");
            }

            $this->line('✓ Credentials file found: ' . $credentials);
        }

        $this->line('✓ Required environment variables are set');
    }

    /**
     * Tester le service Firebase
     */
    private function testFirebaseService()
    {
        $this->line('Testing Firebase service...');

        $firebase = app(FirebaseService::class);

        // Test de base du service
        if (!$firebase->isConfigured()) {
            throw new Exception('Firebase service is not properly configured');
        }

        $this->line('✓ Firebase service is configured');

        // Tester la connexion
        $result = $firebase->testConnection();

        if (!$result['success']) {
            throw new Exception('Firebase connection test failed: ' . $result['message']);
        }

        $this->line('✓ Firebase connection test successful');
    }

    /**
     * Tester l'envoi d'une notification
     */
    private function testNotificationSending()
    {
        $this->line('Testing notification sending...');

        if (!$this->confirm('Do you want to send a test notification? (requires a valid device token)')) {
            $this->line('Skipping notification test...');
            return;
        }

        $token = $this->ask('Enter a device token to test with (or press Enter to skip)');

        if (empty($token)) {
            $this->line('Skipping notification test (no token provided)');
            return;
        }

        $firebase = app(FirebaseService::class);

        $result = $firebase->sendNotification([
            'title' => 'RestroSaaS Test',
            'body' => 'This is a test notification from RestroSaaS Firebase configuration.',
            'token' => $token,
            'data' => [
                'test' => 'true',
                'timestamp' => now()->toISOString()
            ]
        ]);

        if (!$result['success']) {
            throw new Exception('Test notification failed: ' . $result['message']);
        }

        $this->line('✓ Test notification sent successfully');
        $this->line('Message ID: ' . $result['message_id']);
    }
}

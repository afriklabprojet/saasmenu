<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\TranslationHelper;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

/**
 * LocalizationController
 * Contrôleur pour l'addon multi_language
 * Gère le changement de langue dynamique FR/EN/AR
 */
class LocalizationController extends Controller
{
    /**
     * Affiche la page de test de localisation
     * Utilisé par l'addon multi_language
     */
    public function index()
    {
        $data = [
            'current_locale' => App::getLocale(),
            'session_locale' => Session::get('locale'),
            'available_locales' => ['fr', 'en', 'ar'], // multi_language support
            'test_data' => $this->getTestData(),
            'translations' => $this->getTranslationExamples()
        ];

        return view('admin.localization.test', $data);
    }

    /**
     * Change la langue de l'application
     * Endpoint principal pour l'addon multi_language
     */
    public function changeLocale(Request $request)
    {
        $locale = $request->input('locale', 'fr');

        // multi_language: Support pour FR/EN/AR
        if (in_array($locale, ['fr', 'en', 'ar'])) {
            App::setLocale($locale);
            Session::put('locale', $locale);

            if ($locale === 'fr') {
                TranslationHelper::setFrenchLocale();
            }

            return response()->json([
                'success' => true,
                'message' => $locale === 'fr' ? 'Langue changée vers le français' : 'Language changed to English',
                'locale' => $locale
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Langue non supportée'
        ], 400);
    }

    /**
     * Teste les fonctions de traduction
     */
    public function testTranslations()
    {
        return response()->json([
            'status_translations' => [
                'active' => TranslationHelper::translateStatus('active'),
                'pending' => TranslationHelper::translateStatus('pending'),
                'completed' => TranslationHelper::translateStatus('completed'),
                'cancelled' => TranslationHelper::translateStatus('cancelled')
            ],
            'user_type_translations' => [
                'admin' => TranslationHelper::translateUserType('admin'),
                'owner' => TranslationHelper::translateUserType('owner'),
                'customer' => TranslationHelper::translateUserType('customer'),
                'staff' => TranslationHelper::translateUserType('staff')
            ],
            'date_formatting' => [
                'current_date' => TranslationHelper::formatDate(now()),
                'relative_date' => TranslationHelper::formatDateRelative(now()->subDays(2)),
                'custom_format' => TranslationHelper::formatDate(now(), 'l d F Y à H:i')
            ],
            'number_formatting' => [
                'price' => TranslationHelper::formatPrice(1299.99),
                'number' => TranslationHelper::formatNumber(1234567.89),
                'integer' => TranslationHelper::formatNumber(1000, 0)
            ]
        ]);
    }

    /**
     * Génère des données de test
     */
    private function getTestData(): array
    {
        return [
            'orders' => [
                [
                    'id' => 1,
                    'status' => 'pending',
                    'amount' => 45.99,
                    'created_at' => now()->subHours(2),
                    'customer_type' => 'customer'
                ],
                [
                    'id' => 2,
                    'status' => 'completed',
                    'amount' => 78.50,
                    'created_at' => now()->subDays(1),
                    'customer_type' => 'staff'
                ],
                [
                    'id' => 3,
                    'status' => 'cancelled',
                    'amount' => 125.00,
                    'created_at' => now()->subDays(3),
                    'customer_type' => 'admin'
                ]
            ],
            'restaurants' => [
                [
                    'name' => 'Le Petit Bistrot',
                    'status' => 'active',
                    'owner_type' => 'owner',
                    'created_at' => now()->subDays(30)
                ],
                [
                    'name' => 'Pizza Express',
                    'status' => 'inactive',
                    'owner_type' => 'manager',
                    'created_at' => now()->subDays(15)
                ]
            ]
        ];
    }

    /**
     * Exemples de traductions
     */
    private function getTranslationExamples(): array
    {
        return [
            'admin_interface' => [
                'dashboard' => __('admin.dashboard'),
                'orders' => __('admin.orders'),
                'restaurants' => __('admin.restaurants'),
                'customers' => __('admin.customers'),
                'settings' => __('admin.settings')
            ],
            'common_actions' => [
                'add' => __('admin.actions.add'),
                'edit' => __('admin.actions.edit'),
                'delete' => __('admin.actions.delete'),
                'view' => __('admin.actions.view'),
                'save' => __('admin.actions.save')
            ],
            'status_labels' => [
                'active' => __('admin.status.active'),
                'inactive' => __('admin.status.inactive'),
                'pending' => __('admin.status.pending'),
                'completed' => __('admin.status.completed')
            ],
            'notifications' => [
                'system_alert' => __('notifications.types.system_alert'),
                'order_update' => __('notifications.types.order_update'),
                'payment_received' => __('notifications.types.payment_received'),
                'new_registration' => __('notifications.types.new_registration')
            ],
            'training_modules' => [
                'basic_operations' => __('training.modules.basic_operations'),
                'order_management' => __('training.modules.order_management'),
                'customer_service' => __('training.modules.customer_service'),
                'financial_reports' => __('training.modules.financial_reports')
            ]
        ];
    }

    /**
     * Affiche les statistiques de localisation
     */
    public function getStats()
    {
        $translationFiles = [
            'admin' => 'resources/lang/fr/admin.php',
            'notifications' => 'resources/lang/fr/notifications.php',
            'training' => 'resources/lang/fr/training.php',
            'commands' => 'resources/lang/fr/commands.php',
            'validation' => 'resources/lang/fr/validation.php'
        ];

        $stats = [];
        foreach ($translationFiles as $name => $file) {
            $fullPath = base_path($file);
            if (file_exists($fullPath)) {
                $content = file_get_contents($fullPath);
                $lines = substr_count($content, "'=>");
                $stats[$name] = [
                    'file' => $file,
                    'exists' => true,
                    'translations_count' => $lines,
                    'size' => filesize($fullPath)
                ];
            } else {
                $stats[$name] = [
                    'file' => $file,
                    'exists' => false,
                    'translations_count' => 0,
                    'size' => 0
                ];
            }
        }

        return response()->json([
            'current_locale' => App::getLocale(),
            'translation_files' => $stats,
            'total_translations' => array_sum(array_column($stats, 'translations_count')),
            'middleware_active' => class_exists('App\Http\Middleware\LocalizationMiddleware'),
            'helper_available' => class_exists('App\Helpers\TranslationHelper')
        ]);
    }
}

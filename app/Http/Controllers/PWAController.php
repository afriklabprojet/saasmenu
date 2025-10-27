<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\PushSubscription;

class PWAController extends Controller
{
    /**
     * Générer le manifest PWA
     */
    public function manifest()
    {
        $appData = $this->getAppData();

        $manifest = [
            'name' => $appData['name'],
            'short_name' => $appData['short_name'],
            'description' => $appData['description'],
            'start_url' => url('/'),
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => $appData['theme_color'],
            'orientation' => 'portrait-primary',
            'scope' => '/',
            'lang' => 'fr-FR',
            'dir' => 'ltr',
            'icons' => $this->generateIcons(),
            'categories' => ['food', 'restaurant', 'delivery', 'shopping'],
            'screenshots' => $this->generateScreenshots(),
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json');
    }

    /**
     * Service Worker
     */
    public function serviceWorker()
    {
        $version = config('app.version', '1.0.0');
        $cacheName = 'e-menu-v' . str_replace('.', '-', $version);

        $content = view('pwa.service-worker', compact('cacheName'))->render();

        return response($content)
            ->header('Content-Type', 'application/javascript')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    /**
     * Page d'installation PWA
     */
    public function install()
    {
        return view('pwa.install');
    }

    /**
     * Page hors ligne
     */
    public function offline()
    {
        return view('pwa.offline');
    }

    /**
     * Souscrire aux notifications push
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|url',
            'keys.auth' => 'required|string',
            'keys.p256dh' => 'required|string'
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non connecté'
            ], 401);
        }

        try {
            // Créer ou mettre à jour la souscription
            PushSubscription::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'endpoint' => $request->endpoint
                ],
                [
                    'auth_key' => $request->input('keys.auth'),
                    'p256dh_key' => $request->input('keys.p256dh'),
                    'is_active' => true,
                    'user_agent' => $request->userAgent(),
                    'last_used_at' => now()
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Notifications activées avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Se désabonner des notifications
     */
    public function unsubscribe(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non connecté'
            ], 401);
        }

        try {
            // Désactiver toutes les souscriptions de l'utilisateur
            PushSubscription::where('user_id', $user->id)
                ->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Notifications désactivées'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la désactivation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Statut de l'installation PWA
     */
    public function status()
    {
        return response()->json([
            'installable' => true,
            'name' => $this->getAppData()['name'],
            'version' => config('app.version', '1.0.0'),
            'offline_ready' => true,
            'push_notifications' => true
        ]);
    }

    /**
     * Obtenir les données de l'application
     */
    private function getAppData()
    {
        try {
            // Utilisation de la classe helper du projet
            $vdata = session('vendor_id', 1); // ID vendeur par défaut
            $appData = \App\Helpers\helper::appdata($vdata);

            if ($appData) {
                return [
                    'name' => $appData->website_title ?? 'E-menu',
                    'short_name' => 'E-menu',
                    'description' => 'Application de commande en ligne pour restaurants - Commandez facilement vos plats préférés',
                    'theme_color' => $appData->primary_color ?? '#007bff'
                ];
            }
        } catch (\Exception $e) {
            // Ignorer les erreurs et utiliser les valeurs par défaut
        }

        // Valeurs par défaut
        return [
            'name' => config('app.name', 'E-menu'),
            'short_name' => 'E-menu',
            'description' => 'Application de commande en ligne pour restaurants',
            'theme_color' => '#007bff'
        ];
    }

    /**
     * Générer les icônes pour le manifest
     */
    private function generateIcons()
    {
        $iconPath = '/images/logo.png'; // Chemin par défaut

        // Tentative d'obtenir l'icône depuis les paramètres
        try {
            $vdata = session('vendor_id', 1);
            $appData = \App\Helpers\helper::appdata($vdata);

            if ($appData && $appData->favicon) {
                $iconPath = \App\Helpers\helper::image_path($appData->favicon);
            }
        } catch (\Exception $e) {
            // Utiliser le chemin par défaut en cas d'erreur
        }

        return [
            [
                'src' => $iconPath,
                'sizes' => '72x72',
                'type' => 'image/png',
                'purpose' => 'any'
            ],
            [
                'src' => $iconPath,
                'sizes' => '96x96',
                'type' => 'image/png',
                'purpose' => 'any'
            ],
            [
                'src' => $iconPath,
                'sizes' => '128x128',
                'type' => 'image/png',
                'purpose' => 'any'
            ],
            [
                'src' => $iconPath,
                'sizes' => '144x144',
                'type' => 'image/png',
                'purpose' => 'any'
            ],
            [
                'src' => $iconPath,
                'sizes' => '152x152',
                'type' => 'image/png',
                'purpose' => 'any'
            ],
            [
                'src' => $iconPath,
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any maskable'
            ],
            [
                'src' => $iconPath,
                'sizes' => '384x384',
                'type' => 'image/png',
                'purpose' => 'any'
            ],
            [
                'src' => $iconPath,
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any maskable'
            ]
        ];
    }

    /**
     * Générer les captures d'écran pour le manifest
     */
    private function generateScreenshots()
    {
        return [
            [
                'src' => '/images/screenshot-mobile.png',
                'sizes' => '320x568',
                'type' => 'image/png',
                'form_factor' => 'narrow'
            ],
            [
                'src' => '/images/screenshot-desktop.png',
                'sizes' => '1280x720',
                'type' => 'image/png',
                'form_factor' => 'wide'
            ]
        ];
    }
}

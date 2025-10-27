<?php

namespace App\Http\Controllers;

use App\Addons\RestaurantQrMenu\Models\RestaurantQrMenu;
use App\Addons\RestaurantQrMenu\Models\QrMenuScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class QrMenuScanController extends Controller
{
    /**
     * Scanner un QR code et rediriger vers le menu
     */
    public function scan(Request $request, string $slug)
    {
        /** @var RestaurantQrMenu|null $qrMenu */
        $qrMenu = RestaurantQrMenu::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$qrMenu) {
            return view('qr-menu.error', [
                'message' => 'QR Code invalide ou expiré'
            ]);
        }

        // Enregistrer le scan pour analytics
        $this->recordScan($request, $qrMenu);

        // Incrémenter le compteur
        $qrMenu->incrementScanCount();

        // Détecter la langue préférée du client
        $this->setLocaleFromRequest($request);

        // Paramètres de redirection
        $settings = $qrMenu->settings ?? [];
        $autoRedirect = $settings['auto_redirect'] ?? true;
        $tableNumber = $request->get('table');

        if ($autoRedirect) {
            // Redirection directe vers le menu avec paramètres
            $menuUrl = $this->buildMenuUrl($qrMenu->menu_url, $tableNumber);
            return redirect($menuUrl);
        }

        // Afficher une page intermédiaire avec informations
        return view('qr-menu.scan', compact('qrMenu', 'tableNumber'));
    }

    /**
     * Page d'information du restaurant avant redirection
     */
    public function info(string $slug)
    {
        /** @var RestaurantQrMenu|null $qrMenu */
        $qrMenu = RestaurantQrMenu::where('slug', $slug)
            ->where('is_active', true)
            ->with('vendor')
            ->first();

        if (!$qrMenu) {
            return view('qr-menu.error', [
                'message' => 'QR Code invalide ou expiré'
            ]);
        }

        return view('qr-menu.info', compact('qrMenu'));
    }

    /**
     * Redirection vers le menu depuis la page d'info
     */
    public function redirect(Request $request, string $slug)
    {
        /** @var RestaurantQrMenu|null $qrMenu */
        $qrMenu = RestaurantQrMenu::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$qrMenu) {
            return redirect()->route('home');
        }

        $tableNumber = $request->get('table');
        $menuUrl = $this->buildMenuUrl($qrMenu->menu_url, $tableNumber);

        return redirect($menuUrl);
    }

    /**
     * Enregistrer le scan pour analytics
     */
    private function recordScan(Request $request, RestaurantQrMenu $qrMenu): void
    {
        $settings = $qrMenu->settings ?? [];

        if (!($settings['analytics_enabled'] ?? true)) {
            return;
        }

        QrMenuScan::create([
            'qr_menu_id' => $qrMenu->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $this->detectDeviceType($request->userAgent()),
            'table_number' => $request->get('table'),
            'location_data' => $this->getLocationData($request),
            'scanned_at' => now(),
        ]);
    }

    /**
     * Détecter le type d'appareil
     */
    private function detectDeviceType(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'unknown';
        }

        $userAgent = strtolower($userAgent);

        if (preg_match('/(mobile|android|iphone)/', $userAgent)) {
            return 'mobile';
        }

        if (preg_match('/(tablet|ipad)/', $userAgent)) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Obtenir les données de localisation si disponibles
     */
    private function getLocationData(Request $request): ?array
    {
        $lat = $request->get('lat');
        $lng = $request->get('lng');

        if ($lat && $lng) {
            return [
                'latitude' => $lat,
                'longitude' => $lng,
                'accuracy' => $request->get('accuracy'),
                'timestamp' => now()->timestamp,
            ];
        }

        return null;
    }

    /**
     * Construire l'URL du menu avec paramètres
     */
    private function buildMenuUrl(string $baseUrl, ?string $tableNumber = null): string
    {
        $params = [];

        if ($tableNumber) {
            $params['table'] = $tableNumber;
        }

        // Ajouter la langue actuelle
        $params['lang'] = App::getLocale();

        if (empty($params)) {
            return $baseUrl;
        }

        $separator = parse_url($baseUrl, PHP_URL_QUERY) ? '&' : '?';
        return $baseUrl . $separator . http_build_query($params);
    }

    /**
     * Définir la locale à partir de la requête
     */
    private function setLocaleFromRequest(Request $request): void
    {
        // Langue demandée en paramètre
        $requestedLang = $request->get('lang');
        if ($requestedLang && in_array($requestedLang, ['fr', 'en', 'ar'])) {
            App::setLocale($requestedLang);
            return;
        }

        // Détecter depuis Accept-Language
        $acceptLanguage = $request->header('Accept-Language');
        if ($acceptLanguage) {
            $preferredLang = substr($acceptLanguage, 0, 2);
            if (in_array($preferredLang, ['fr', 'en', 'ar'])) {
                App::setLocale($preferredLang);
                return;
            }
        }

        // Fallback sur la langue par défaut
        App::setLocale(config('app.locale', 'fr'));
    }
}

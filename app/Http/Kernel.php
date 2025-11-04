<?php



namespace App\Http;



use Illuminate\Foundation\Http\Kernel as HttpKernel;



class Kernel extends HttpKernel

{

    /**

     * The application's global HTTP middleware stack.

     *

     * These middleware are run during every request to your application.

     *

     * @var array<int, class-string|string>

     */

    protected $middleware = [

        // \App\Http\Middleware\TrustHosts::class,

        \App\Http\Middleware\TrustProxies::class,

        \Illuminate\Http\Middleware\HandleCors::class,

        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,

        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,

        \App\Http\Middleware\TrimStrings::class,

        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,

        // Middleware de sécurité pour production - ACTIVÉ pour corriger les vulnérabilités
        \App\Http\Middleware\SecurityHeaders::class,

        // Middleware de notifications temps réel - TEMPORAIREMENT DÉSACTIVÉ (boucle infinie)
        // \App\Http\Middleware\NotificationMiddleware::class,

        // Middleware de monitoring performance - TEMPORAIREMENT DÉSACTIVÉ
        // \App\Http\Middleware\PerformanceMonitoring::class,

        // Middleware de localisation multi-langue - ACTIVÉ pour supporter FR/EN
        \App\Http\Middleware\LocalizationMiddleware::class,

    ];



    /**

     * The application's route middleware groups.

     *

     * @var array<string, array<int, class-string|string>>

     */

    protected $middlewareGroups = [

        'web' => [

            \App\Http\Middleware\EncryptCookies::class,

            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,

            \Illuminate\Session\Middleware\StartSession::class,

            // \Illuminate\Session\Middleware\AuthenticateSession::class,

            \Illuminate\View\Middleware\ShareErrorsFromSession::class,

            \App\Http\Middleware\VerifyCsrfToken::class,

            \Illuminate\Routing\Middleware\SubstituteBindings::class,

        ],



        'api' => [

            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,

            'throttle:api',

            \Illuminate\Routing\Middleware\SubstituteBindings::class,

        ],

    ];



    /**

     * The application's route middleware.

     *

     * These middleware may be assigned to groups or used individually.

     *

     * @var array<string, class-string|string>

     */

    protected $routeMiddleware = [

        'auth' => \App\Http\Middleware\Authenticate::class,

        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,

        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,

        'can' => \Illuminate\Auth\Middleware\Authorize::class,

        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,

        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,

        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,

        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,

        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        'adminmiddleware'=>  \App\Http\Middleware\adminmiddleware::class,

        'VendorMiddleware'=>  \App\Http\Middleware\VendorMiddleware::class,

        'subscription.limit'=>  \App\Http\Middleware\SubscriptionLimit::class,

        'AuthMiddleware'=>  \App\Http\Middleware\AuthMiddleware::class,

        'MaintenanceMiddleware'=> \App\Http\Middleware\MaintenanceMiddleware::class,

        'FrontMiddleware'=> \App\Http\Middleware\FrontMiddleware::class,

        'landingMiddleware'=> \App\Http\Middleware\landingMiddleware::class,


        // Middlewares pour les addons
        'addon.permission' => \App\Http\Middleware\ValidateAddonPermission::class,
        'addon.ratelimit' => \App\Http\Middleware\AddonRateLimit::class,
        'api.key' => \App\Http\Middleware\ValidateApiKey::class,

        // Middleware de vérification des limites d'abonnement
        'subscription.limit' => \App\Http\Middleware\SubscriptionLimitMiddleware::class,

    ];

}


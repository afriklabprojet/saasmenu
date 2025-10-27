<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\SocialAccount;
use App\Services\GoogleAuthService;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class GoogleLoginController extends Controller
{
    protected $googleService;

    public function __construct(GoogleAuthService $googleService)
    {
        $this->googleService = $googleService;
    }

    /**
     * Redirection vers Google OAuth
     */
    public function redirect()
    {
        try {
            return Socialite::driver('google')
                ->scopes(['openid', 'profile', 'email'])
                ->redirect();

        } catch (Exception $e) {
            Log::error('Google Redirect Error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Erreur lors de la redirection vers Google');
        }
    }

    /**
     * Callback de retour Google
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Chercher un compte existant avec cet email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Utilisateur existant - créer/mettre à jour le lien social
                $this->createOrUpdateSocialAccount($user, $googleUser);
                Auth::login($user);

                return redirect()->intended(route('dashboard'))
                    ->with('success', 'Connexion Google réussie');
            } else {
                // Nouvel utilisateur - créer le compte
                $user = $this->createUserFromGoogle($googleUser);
                Auth::login($user);

                return redirect()->route('dashboard')
                    ->with('success', 'Compte créé avec succès via Google');
            }

        } catch (Exception $e) {
            Log::error('Google Callback Error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Erreur lors de la connexion Google');
        }
    }

    /**
     * Créer un utilisateur à partir des données Google
     */
    private function createUserFromGoogle($googleUser)
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'email_verified_at' => now(),
                'password' => Hash::make(uniqid()), // Mot de passe temporaire
                'avatar' => $googleUser->getAvatar(),
                'provider' => 'google',
                'provider_id' => $googleUser->getId(),
            ]);

            // Créer le lien social
            $this->createOrUpdateSocialAccount($user, $googleUser);

            DB::commit();
            return $user;

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Créer ou mettre à jour le compte social
     */
    private function createOrUpdateSocialAccount($user, $googleUser)
    {
        SocialAccount::updateOrCreate([
            'user_id' => $user->id,
            'provider' => 'google',
        ], [
            'provider_id' => $googleUser->getId(),
            'provider_token' => $googleUser->token,
            'provider_refresh_token' => $googleUser->refreshToken,
            'provider_expires_at' => $googleUser->expiresIn ? now()->addSeconds($googleUser->expiresIn) : null,
            'avatar' => $googleUser->getAvatar(),
            'profile_data' => [
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'avatar' => $googleUser->getAvatar(),
                'locale' => $googleUser->user['locale'] ?? null,
            ],
            'last_login_at' => now(),
        ]);
    }

    /**
     * Authentification avec token Google (API)
     */
    public function authenticateWithToken(Request $request)
    {
        try {
            $validated = $request->validate([
                'access_token' => 'required|string',
            ]);

            $userProfile = $this->googleService->getUserProfile($validated['access_token']);

            if (!$userProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token Google invalide'
                ], 401);
            }

            $user = User::where('email', $userProfile['email'])->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé',
                    'requires_registration' => true,
                    'profile' => $userProfile
                ], 404);
            }

            // Mettre à jour les informations sociales
            $this->updateSocialAccountFromToken($user, $userProfile, $validated['access_token']);

            // Créer un token API
            $token = $user->createToken('google-auth')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Connexion Google réussie',
                'user' => $user,
                'token' => $token
            ]);

        } catch (Exception $e) {
            Log::error('Google Token Auth Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'authentification'
            ], 500);
        }
    }

    /**
     * Inscription avec token Google (API)
     */
    public function registerWithToken(Request $request)
    {
        try {
            $validated = $request->validate([
                'access_token' => 'required|string',
                'additional_data' => 'nullable|array',
            ]);

            $userProfile = $this->googleService->getUserProfile($validated['access_token']);

            if (!$userProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token Google invalide'
                ], 401);
            }

            // Vérifier si l'utilisateur existe déjà
            $existingUser = User::where('email', $userProfile['email'])->first();
            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Un compte existe déjà avec cet email'
                ], 409);
            }

            // Créer le nouvel utilisateur
            $userData = array_merge([
                'name' => $userProfile['name'],
                'email' => $userProfile['email'],
                'email_verified_at' => now(),
                'password' => Hash::make(uniqid()),
                'avatar' => $userProfile['picture'] ?? null,
                'provider' => 'google',
                'provider_id' => $userProfile['id'],
            ], $validated['additional_data'] ?? []);

            $user = User::create($userData);

            // Créer le lien social
            $this->updateSocialAccountFromToken($user, $userProfile, $validated['access_token']);

            // Créer un token API
            $token = $user->createToken('google-register')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès via Google',
                'user' => $user,
                'token' => $token
            ], 201);

        } catch (Exception $e) {
            Log::error('Google Token Register Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'inscription'
            ], 500);
        }
    }

    /**
     * Lier un compte Google à un utilisateur connecté
     */
    public function linkAccount(Request $request)
    {
        try {
            $user = Auth::user();
            $googleUser = Socialite::driver('google')->user();

            // Vérifier si ce compte Google n'est pas déjà lié
            $existingLink = SocialAccount::where('provider', 'google')
                ->where('provider_id', $googleUser->getId())
                ->where('user_id', '!=', $user->id)
                ->first();

            if ($existingLink) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce compte Google est déjà lié à un autre utilisateur'
                ], 409);
            }

            $this->createOrUpdateSocialAccount($user, $googleUser);

            return response()->json([
                'success' => true,
                'message' => 'Compte Google lié avec succès'
            ]);

        } catch (Exception $e) {
            Log::error('Google Link Account Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la liaison du compte'
            ], 500);
        }
    }

    /**
     * Délier un compte Google
     */
    public function unlinkAccount(Request $request)
    {
        try {
            $user = Auth::user();

            $deleted = SocialAccount::where('user_id', $user->id)
                ->where('provider', 'google')
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Compte Google délié avec succès'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Aucun compte Google lié trouvé'
            ], 404);

        } catch (Exception $e) {
            Log::error('Google Unlink Account Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du déliage du compte'
            ], 500);
        }
    }

    /**
     * Mettre à jour le compte social depuis un token
     */
    private function updateSocialAccountFromToken($user, $profile, $token)
    {
        SocialAccount::updateOrCreate([
            'user_id' => $user->id,
            'provider' => 'google',
        ], [
            'provider_id' => $profile['id'],
            'provider_token' => $token,
            'avatar' => $profile['picture'] ?? null,
            'profile_data' => $profile,
            'last_login_at' => now(),
        ]);
    }

    /**
     * Obtenir les paramètres Google
     */
    public function getSettings()
    {
        try {
            $settings = [
                'client_id' => config('services.google.client_id'),
                'enabled' => config('services.google.enabled', false),
                'scopes' => config('services.google.scopes', ['openid', 'profile', 'email']),
            ];

            return response()->json([
                'success' => true,
                'settings' => $settings
            ]);

        } catch (Exception $e) {
            Log::error('Google Settings Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des paramètres'
            ], 500);
        }
    }

    /**
     * Mettre à jour les paramètres Google
     */
    public function updateSettings(Request $request)
    {
        try {
            $validated = $request->validate([
                'client_id' => 'required|string',
                'client_secret' => 'required|string',
                'enabled' => 'boolean',
                'scopes' => 'array',
            ]);

            // Mettre à jour la configuration
            config(['services.google.client_id' => $validated['client_id']]);
            config(['services.google.client_secret' => $validated['client_secret']]);
            config(['services.google.enabled' => $validated['enabled'] ?? false]);
            config(['services.google.scopes' => $validated['scopes'] ?? ['openid', 'profile', 'email']]);

            return response()->json([
                'success' => true,
                'message' => 'Paramètres Google mis à jour avec succès'
            ]);

        } catch (Exception $e) {
            Log::error('Google Settings Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des paramètres'
            ], 500);
        }
    }

    /**
     * Méthodes placeholder pour les autres fonctionnalités
     */
    public function logout() { return response()->json(['success' => true]); }
    public function linkAccountWithToken() { return response()->json(['success' => true]); }
    public function getLinkedAccounts() { return response()->json(['success' => true, 'accounts' => []]); }
    public function unlinkSocialAccount($provider) { return response()->json(['success' => true]); }
    public function syncGoogleProfile() { return response()->json(['success' => true]); }
    public function importGoogleContacts() { return response()->json(['success' => true]); }
    public function shareToGoogle() { return response()->json(['success' => true]); }
    public function testConnection() { return response()->json(['success' => true]); }
    public function getLoginStats() { return response()->json(['success' => true, 'stats' => []]); }
    public function getSocialAnalytics() { return response()->json(['success' => true, 'analytics' => []]); }
    public function getSocialUsers() { return response()->json(['success' => true, 'users' => []]); }
    public function bulkSocialActions() { return response()->json(['success' => true]); }
    public function handleGoogleWebhook() { return response()->json(['success' => true]); }
    public function sendGoogleInvite() { return response()->json(['success' => true]); }
    public function getSocialRecommendations() { return response()->json(['success' => true, 'recommendations' => []]); }
    public function shareReviewToGoogle() { return response()->json(['success' => true]); }
    public function validateGoogleToken() { return response()->json(['success' => true]); }
    public function getAvailableProviders() { return response()->json(['success' => true, 'providers' => ['google', 'facebook']]); }
    public function getProviderInfo($provider) { return response()->json(['success' => true, 'info' => []]); }
    public function getSocialLoginStatus() { return response()->json(['success' => true, 'status' => []]); }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\SocialAccount;
use App\Services\FacebookAuthService;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class FacebookLoginController extends Controller
{
    protected $facebookService;

    public function __construct(FacebookAuthService $facebookService)
    {
        $this->facebookService = $facebookService;
    }

    /**
     * Redirection vers Facebook OAuth
     */
    public function redirect()
    {
        try {
            return Socialite::driver('facebook')
                ->scopes(['email', 'public_profile'])
                ->redirect();

        } catch (Exception $e) {
            Log::error('Facebook Redirect Error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Erreur lors de la redirection vers Facebook');
        }
    }

    /**
     * Callback de retour Facebook
     */
    public function callback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();

            // Chercher un compte existant avec cet email
            $user = User::where('email', $facebookUser->getEmail())->first();

            if ($user) {
                // Utilisateur existant - créer/mettre à jour le lien social
                $this->createOrUpdateSocialAccount($user, $facebookUser);
                Auth::login($user);

                return redirect()->intended(route('dashboard'))
                    ->with('success', 'Connexion Facebook réussie');
            } else {
                // Nouvel utilisateur - créer le compte
                $user = $this->createUserFromFacebook($facebookUser);
                Auth::login($user);

                return redirect()->route('dashboard')
                    ->with('success', 'Compte créé avec succès via Facebook');
            }

        } catch (Exception $e) {
            Log::error('Facebook Callback Error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Erreur lors de la connexion Facebook');
        }
    }

    /**
     * Créer un utilisateur à partir des données Facebook
     */
    private function createUserFromFacebook($facebookUser)
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $facebookUser->getName(),
                'email' => $facebookUser->getEmail(),
                'email_verified_at' => now(),
                'password' => Hash::make(uniqid()), // Mot de passe temporaire
                'avatar' => $facebookUser->getAvatar(),
                'provider' => 'facebook',
                'provider_id' => $facebookUser->getId(),
            ]);

            // Créer le lien social
            $this->createOrUpdateSocialAccount($user, $facebookUser);

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
    private function createOrUpdateSocialAccount($user, $facebookUser)
    {
        SocialAccount::updateOrCreate([
            'user_id' => $user->id,
            'provider' => 'facebook',
        ], [
            'provider_id' => $facebookUser->getId(),
            'provider_token' => $facebookUser->token,
            'avatar' => $facebookUser->getAvatar(),
            'profile_data' => [
                'name' => $facebookUser->getName(),
                'email' => $facebookUser->getEmail(),
                'avatar' => $facebookUser->getAvatar(),
                'facebook_id' => $facebookUser->getId(),
            ],
            'last_login_at' => now(),
        ]);
    }

    /**
     * Authentification avec token Facebook (API)
     */
    public function authenticateWithToken(Request $request)
    {
        try {
            $validated = $request->validate([
                'access_token' => 'required|string',
            ]);

            $userProfile = $this->facebookService->getUserProfile($validated['access_token']);

            if (!$userProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token Facebook invalide'
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
            $token = $user->createToken('facebook-auth')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Connexion Facebook réussie',
                'user' => $user,
                'token' => $token
            ]);

        } catch (Exception $e) {
            Log::error('Facebook Token Auth Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'authentification'
            ], 500);
        }
    }

    /**
     * Inscription avec token Facebook (API)
     */
    public function registerWithToken(Request $request)
    {
        try {
            $validated = $request->validate([
                'access_token' => 'required|string',
                'additional_data' => 'nullable|array',
            ]);

            $userProfile = $this->facebookService->getUserProfile($validated['access_token']);

            if (!$userProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token Facebook invalide'
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
                'avatar' => $userProfile['picture']['data']['url'] ?? null,
                'provider' => 'facebook',
                'provider_id' => $userProfile['id'],
            ], $validated['additional_data'] ?? []);

            $user = User::create($userData);

            // Créer le lien social
            $this->updateSocialAccountFromToken($user, $userProfile, $validated['access_token']);

            // Créer un token API
            $token = $user->createToken('facebook-register')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès via Facebook',
                'user' => $user,
                'token' => $token
            ], 201);

        } catch (Exception $e) {
            Log::error('Facebook Token Register Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'inscription'
            ], 500);
        }
    }

    /**
     * Lier un compte Facebook à un utilisateur connecté
     */
    public function linkAccount(Request $request)
    {
        try {
            $user = Auth::user();
            $facebookUser = Socialite::driver('facebook')->user();

            // Vérifier si ce compte Facebook n'est pas déjà lié
            $existingLink = SocialAccount::where('provider', 'facebook')
                ->where('provider_id', $facebookUser->getId())
                ->where('user_id', '!=', $user->id)
                ->first();

            if ($existingLink) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce compte Facebook est déjà lié à un autre utilisateur'
                ], 409);
            }

            $this->createOrUpdateSocialAccount($user, $facebookUser);

            return response()->json([
                'success' => true,
                'message' => 'Compte Facebook lié avec succès'
            ]);

        } catch (Exception $e) {
            Log::error('Facebook Link Account Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la liaison du compte'
            ], 500);
        }
    }

    /**
     * Délier un compte Facebook
     */
    public function unlinkAccount(Request $request)
    {
        try {
            $user = Auth::user();

            $deleted = SocialAccount::where('user_id', $user->id)
                ->where('provider', 'facebook')
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Compte Facebook délié avec succès'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Aucun compte Facebook lié trouvé'
            ], 404);

        } catch (Exception $e) {
            Log::error('Facebook Unlink Account Error: ' . $e->getMessage());
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
            'provider' => 'facebook',
        ], [
            'provider_id' => $profile['id'],
            'provider_token' => $token,
            'avatar' => $profile['picture']['data']['url'] ?? null,
            'profile_data' => $profile,
            'last_login_at' => now(),
        ]);
    }

    /**
     * Obtenir les paramètres Facebook
     */
    public function getSettings()
    {
        try {
            $settings = [
                'app_id' => config('services.facebook.client_id'),
                'enabled' => config('services.facebook.enabled', false),
                'scopes' => config('services.facebook.scopes', ['email', 'public_profile']),
            ];

            return response()->json([
                'success' => true,
                'settings' => $settings
            ]);

        } catch (Exception $e) {
            Log::error('Facebook Settings Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des paramètres'
            ], 500);
        }
    }

    /**
     * Mettre à jour les paramètres Facebook
     */
    public function updateSettings(Request $request)
    {
        try {
            $validated = $request->validate([
                'app_id' => 'required|string',
                'app_secret' => 'required|string',
                'enabled' => 'boolean',
                'scopes' => 'array',
            ]);

            // Mettre à jour la configuration
            config(['services.facebook.client_id' => $validated['app_id']]);
            config(['services.facebook.client_secret' => $validated['app_secret']]);
            config(['services.facebook.enabled' => $validated['enabled'] ?? false]);
            config(['services.facebook.scopes' => $validated['scopes'] ?? ['email', 'public_profile']]);

            return response()->json([
                'success' => true,
                'message' => 'Paramètres Facebook mis à jour avec succès'
            ]);

        } catch (Exception $e) {
            Log::error('Facebook Settings Update Error: ' . $e->getMessage());
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
    public function syncFacebookProfile() { return response()->json(['success' => true]); }
    public function importFacebookFriends() { return response()->json(['success' => true]); }
    public function shareToFacebook() { return response()->json(['success' => true]); }
    public function testConnection() { return response()->json(['success' => true]); }
    public function getLoginStats() { return response()->json(['success' => true, 'stats' => []]); }
    public function handleFacebookWebhook() { return response()->json(['success' => true]); }
    public function sendFacebookInvite() { return response()->json(['success' => true]); }
    public function shareReviewToFacebook() { return response()->json(['success' => true]); }
    public function validateFacebookToken() { return response()->json(['success' => true]); }
}

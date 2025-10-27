<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialLoginController extends Controller
{
    /**
     * Rediriger vers le provider
     */
    public function redirectToProvider($provider)
    {
        // Vérifier que le provider est supporté
        if (!in_array($provider, ['google', 'facebook', 'apple'])) {
            return redirect()->route('login')->withErrors(['error' => 'Provider non supporté']);
        }

        try {
            return Socialite::driver($provider)->redirect();
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors(['error' => 'Erreur de connexion avec ' . $provider]);
        }
    }

    /**
     * Gérer le callback du provider
     */
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors(['error' => 'Impossible de récupérer les informations']);
        }

        // Chercher l'utilisateur par ID social
        $user = $this->findOrCreateUser($socialUser, $provider);

        // Connecter l'utilisateur
        Auth::login($user, true);

        // Rediriger selon le rôle
        if ($user->type == 1) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->type == 2) {
            return redirect()->route('admin.dashboard'); // Vendor dashboard
        } else {
            return redirect('/'); // Customer
        }
    }

    /**
     * Trouver ou créer l'utilisateur
     */
    protected function findOrCreateUser($socialUser, $provider)
    {
        // Chercher par ID social
        $columnName = $provider . '_id';
        /** @var \App\Models\User|null $user */
        $user = User::where($columnName, $socialUser->getId())->first();

        if ($user) {
            // Mettre à jour les infos si nécessaire
            $user->update([
                'name' => $socialUser->getName() ?? $user->name,
                'image' => $socialUser->getAvatar() ?? $user->image,
            ]);
            return $user;
        }

        // Chercher par email
        /** @var \App\Models\User|null $user */
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Lier le compte social existant
            $user->update([
                $columnName => $socialUser->getId(),
                'login_type' => $provider,
            ]);
            return $user;
        }

        // Créer un nouvel utilisateur
        return User::create([
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'password' => Hash::make(Str::random(24)), // Mot de passe aléatoire
            $columnName => $socialUser->getId(),
            'login_type' => $provider,
            'image' => $socialUser->getAvatar(),
            'type' => 3, // Client par défaut
            'is_verified' => 1, // Email déjà vérifié par le provider
        ]);
    }

    /**
     * Délier un compte social
     */
    public function unlinkSocialAccount($provider)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!in_array($provider, ['google', 'facebook', 'apple'])) {
            return back()->withErrors(['error' => 'Provider invalide']);
        }

        // Vérifier que l'utilisateur a un mot de passe (pour ne pas se bloquer)
        if (!$user->password || $user->login_type == $provider) {
            return back()->withErrors([
                'error' => 'Vous devez définir un mot de passe avant de délier ce compte'
            ]);
        }

        $columnName = $provider . '_id';
        $user->update([
            $columnName => null,
        ]);

        return back()->with('success', 'Compte ' . ucfirst($provider) . ' délié avec succès');
    }
}

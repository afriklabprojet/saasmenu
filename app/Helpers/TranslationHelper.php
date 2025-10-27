<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;

class TranslationHelper
{
    /**
     * Traductions pour les status communs
     */
    private static $statusTranslations = [
        'active' => 'Actif',
        'inactive' => 'Inactif',
        'pending' => 'En attente',
        'approved' => 'Approuvé',
        'rejected' => 'Rejeté',
        'completed' => 'Terminé',
        'processing' => 'En cours',
        'cancelled' => 'Annulé',
        'delivered' => 'Livré',
        'confirmed' => 'Confirmé',
        'draft' => 'Brouillon',
        'published' => 'Publié'
    ];

    /**
     * Traductions pour les types d'utilisateurs
     */
    private static $userTypeTranslations = [
        'admin' => 'Administrateur',
        'owner' => 'Propriétaire',
        'manager' => 'Gestionnaire',
        'staff' => 'Personnel',
        'customer' => 'Client',
        'vendor' => 'Vendeur',
        'delivery' => 'Livreur',
        'driver' => 'Chauffeur'
    ];

    /**
     * Traductions pour les jours de la semaine
     */
    private static $dayTranslations = [
        'monday' => 'Lundi',
        'tuesday' => 'Mardi',
        'wednesday' => 'Mercredi',
        'thursday' => 'Jeudi',
        'friday' => 'Vendredi',
        'saturday' => 'Samedi',
        'sunday' => 'Dimanche'
    ];

    /**
     * Traductions pour les mois
     */
    private static $monthTranslations = [
        'january' => 'Janvier',
        'february' => 'Février',
        'march' => 'Mars',
        'april' => 'Avril',
        'may' => 'Mai',
        'june' => 'Juin',
        'july' => 'Juillet',
        'august' => 'Août',
        'september' => 'Septembre',
        'october' => 'Octobre',
        'november' => 'Novembre',
        'december' => 'Décembre'
    ];

    /**
     * Traduit un status
     */
    public static function translateStatus(string $status): string
    {
        return self::$statusTranslations[strtolower($status)] ?? ucfirst($status);
    }

    /**
     * Traduit un type d'utilisateur
     */
    public static function translateUserType(string $type): string
    {
        return self::$userTypeTranslations[strtolower($type)] ?? ucfirst($type);
    }

    /**
     * Traduit un jour de la semaine
     */
    public static function translateDay(string $day): string
    {
        return self::$dayTranslations[strtolower($day)] ?? ucfirst($day);
    }

    /**
     * Traduit un mois
     */
    public static function translateMonth(string $month): string
    {
        return self::$monthTranslations[strtolower($month)] ?? ucfirst($month);
    }

    /**
     * Formate une date en français
     */
    public static function formatDate($date, string $format = 'd/m/Y à H:i'): string
    {
        if (!$date) {
            return '-';
        }

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        // Définir la locale Carbon en français
        $date->locale('fr');

        return $date->format($format);
    }

    /**
     * Formate une date relative en français
     */
    public static function formatDateRelative($date): string
    {
        if (!$date) {
            return '-';
        }

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        $date->locale('fr');

        return $date->diffForHumans();
    }

    /**
     * Traduit les messages de validation Laravel
     */
    public static function getValidationMessages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'email' => 'Le champ :attribute doit être une adresse e-mail valide.',
            'unique' => 'Le champ :attribute a déjà été pris.',
            'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
            'min' => [
                'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
                'numeric' => 'Le champ :attribute doit être au moins :min.',
                'file' => 'Le champ :attribute doit faire au moins :min kilo-octets.',
            ],
            'max' => [
                'string' => 'Le champ :attribute ne peut contenir plus de :max caractères.',
                'numeric' => 'Le champ :attribute ne peut être supérieur à :max.',
                'file' => 'Le champ :attribute ne peut faire plus de :max kilo-octets.',
            ],
            'numeric' => 'Le champ :attribute doit être un nombre.',
            'string' => 'Le champ :attribute doit être une chaîne de caractères.',
            'array' => 'Le champ :attribute doit être un tableau.',
            'boolean' => 'Le champ :attribute doit être vrai ou faux.',
            'date' => 'Le champ :attribute n\'est pas une date valide.',
            'image' => 'Le champ :attribute doit être une image.',
            'mimes' => 'Le champ :attribute doit être un fichier de type : :values.',
            'exists' => 'Le champ :attribute sélectionné est invalide.',
        ];
    }

    /**
     * Traduit les attributs des champs de formulaire
     */
    public static function getAttributeNames(): array
    {
        return [
            'name' => 'nom',
            'email' => 'e-mail',
            'password' => 'mot de passe',
            'password_confirmation' => 'confirmation du mot de passe',
            'first_name' => 'prénom',
            'last_name' => 'nom de famille',
            'phone' => 'téléphone',
            'address' => 'adresse',
            'city' => 'ville',
            'country' => 'pays',
            'state' => 'état/région',
            'zip_code' => 'code postal',
            'description' => 'description',
            'price' => 'prix',
            'quantity' => 'quantité',
            'image' => 'image',
            'category' => 'catégorie',
            'title' => 'titre',
            'content' => 'contenu',
            'status' => 'statut',
            'type' => 'type',
            'date' => 'date',
            'time' => 'heure'
        ];
    }

    /**
     * Formatage des nombres avec séparateurs français
     */
    public static function formatNumber($number, int $decimals = 2): string
    {
        if (!is_numeric($number)) {
            return '-';
        }

        return number_format($number, $decimals, ',', ' ');
    }

    /**
     * Formatage des prix en euros
     */
    public static function formatPrice($amount, string $currency = '€'): string
    {
        if (!is_numeric($amount)) {
            return '-';
        }

        return self::formatNumber($amount, 2) . ' ' . $currency;
    }

    /**
     * Traduit les messages d'erreur courants
     */
    public static function getCommonErrorMessages(): array
    {
        return [
            'not_found' => 'Élément non trouvé.',
            'access_denied' => 'Accès refusé.',
            'unauthorized' => 'Non autorisé.',
            'validation_failed' => 'La validation a échoué.',
            'server_error' => 'Erreur serveur.',
            'network_error' => 'Erreur réseau.',
            'timeout' => 'Délai d\'attente dépassé.',
            'invalid_request' => 'Requête invalide.',
            'insufficient_permissions' => 'Permissions insuffisantes.',
            'resource_not_available' => 'Ressource non disponible.'
        ];
    }

    /**
     * Traduit les titres de page
     */
    public static function translatePageTitle(string $page): string
    {
        $titles = __('ui.page_titles');
        return $titles[$page] ?? ucfirst($page);
    }

    /**
     * Traduit les boutons d'action
     */
    public static function translateButton(string $button): string
    {
        $buttons = __('ui.buttons');
        return $buttons[$button] ?? ucfirst($button);
    }

    /**
     * Traduit les messages système
     */
    public static function translateMessage(string $message): string
    {
        $messages = __('ui.messages');
        return $messages[$message] ?? ucfirst($message);
    }

    /**
     * Traduit les libellés de formulaire
     */
    public static function translateLabel(string $label): string
    {
        $labels = __('ui.form_labels');
        return $labels[$label] ?? ucfirst($label);
    }

    /**
     * Traduit les éléments de navigation
     */
    public static function translateNavigation(string $nav): string
    {
        $navigation = __('ui.navigation');
        return $navigation[$nav] ?? ucfirst($nav);
    }

    /**
     * Active la locale française pour l'application
     */
    public static function setFrenchLocale(): void
    {
        App::setLocale('fr');
        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fr');
        \Carbon\Carbon::setLocale('fr');
    }
}

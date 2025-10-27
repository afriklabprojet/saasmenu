<?php

namespace Addons\MultiLanguage;

/**
 * Multi-Language Addon
 * Gestion multilingue pour l'application (FR/EN/AR)
 */
class MultiLanguageAddon
{
    public const VERSION = '1.0.0';
    public const NAME = 'Multi-Language Support';
    public const DESCRIPTION = 'Support multilingue avec changement de langue dynamique';

    /**
     * Langues supportées
     */
    public const SUPPORTED_LANGUAGES = [
        'fr' => [
            'name' => 'Français',
            'flag' => '🇫🇷',
            'rtl' => false
        ],
        'en' => [
            'name' => 'English',
            'flag' => '🇺🇸',
            'rtl' => false
        ],
        'ar' => [
            'name' => 'العربية',
            'flag' => '🇸🇦',
            'rtl' => true
        ]
    ];

    /**
     * Initialiser l'addon
     */
    public static function init()
    {
        // Addon activé via middleware LocalizationMiddleware dans Kernel.php
        return true;
    }

    /**
     * Obtenir la langue par défaut
     */
    public static function getDefaultLanguage()
    {
        return config('app.locale', 'fr');
    }

    /**
     * Obtenir toutes les langues supportées
     */
    public static function getSupportedLanguages()
    {
        return self::SUPPORTED_LANGUAGES;
    }

    /**
     * Vérifier si une langue est supportée
     */
    public static function isLanguageSupported($locale)
    {
        return array_key_exists($locale, self::SUPPORTED_LANGUAGES);
    }
}

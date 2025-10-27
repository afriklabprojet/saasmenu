# Multi-Language Addon

Ce addon fournit un support multilingue complet pour RestroSaaS.

## Fonctionnalités

- Support pour Français, Anglais et Arabe
- Changement de langue dynamique
- Support RTL pour l'arabe
- Middleware de localisation automatique
- Interface utilisateur pour le changement de langue

## Installation

L'addon est automatiquement chargé et configuré via:

1. **Middleware**: `LocalizationMiddleware` enregistré dans `app/Http/Kernel.php`
2. **Routes**: Routes de changement de langue disponibles sous `/lang/`
3. **Composant UI**: Sélecteur de langue disponible via `@include('components.language-switcher')`

## Utilisation

### Changement de langue via API
```bash
POST /lang/change
{
    "locale": "fr|en|ar"
}
```

### Intégration dans les vues
```blade
{{-- Inclure le sélecteur de langue --}}
@include('components.language-switcher')
```

### Langues supportées

| Code | Langue | Direction | Flag |
|------|--------|-----------|------|
| fr   | Français | LTR | 🇫🇷 |
| en   | English  | LTR | 🇺🇸 |
| ar   | العربية   | RTL | 🇸🇦 |

## Configuration

La langue est stockée en session et appliquée automatiquement via le middleware `LocalizationMiddleware`.

## Fichiers

- `MultiLanguageAddon.php` - Classe principale de l'addon
- `addon.php` - Configuration de l'addon
- `routes/web.php` - Routes de l'addon
- `Controllers/LanguageController.php` - Contrôleur API
- `Models/Language.php` - Modèle de données
- `views/language-switcher.blade.php` - Composant UI

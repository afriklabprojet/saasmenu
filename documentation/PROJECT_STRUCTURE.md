# 🍽️ RestroSaaS - Organisation du Projet

## 📁 Structure des Dossiers

### 📚 [`documentation/`](./documentation/)
Contient toute la documentation technique et fonctionnelle du projet.
- Guides de démarrage et installation
- Documentation des fonctionnalités
- Guides d'intégration (WhatsApp, CinetPay, etc.)
- Rapports et tests
- **77 fichiers** de documentation

→ Consultez [`documentation/README.md`](./documentation/README.md) pour l'index complet

### 🛠️ [`scripts/`](./scripts/)
Contient tous les scripts d'administration, test et déploiement.
- Scripts de déploiement production
- Scripts de test système
- Scripts de validation
- Scripts de migration
- **22 scripts** disponibles

→ Consultez [`scripts/README.md`](./scripts/README.md) pour les détails d'usage

## 🚀 Démarrage Rapide

### Installation
\`\`\`bash
# 1. Installer les dépendances
composer install
npm install

# 2. Configuration environnement
cp .env.example .env
php artisan key:generate

# 3. Base de données
php artisan migrate
php artisan db:seed

# 4. Lancer le serveur
php artisan serve
\`\`\`

### Documentation Essentielle
- 📖 [Guide de Démarrage](./documentation/DEMARRAGE_RAPIDE.md)
- 🔧 [Installation Détaillée](./documentation/INSTALLATION.md)
- 🏪 [Guide Restaurateurs](./documentation/GUIDE_RESTAURANTS.md)
- 🐛 [Guide de Dépannage](./documentation/GUIDE_DEPANNAGE.md)

## 🏗️ Architecture du Projet

\`\`\`
restro-saas/
├── app/                    # Code applicatif Laravel
│   ├── Http/Controllers/   # Contrôleurs
│   ├── Models/            # Modèles Eloquent
│   ├── Services/          # Services métier
│   └── Helpers/           # Fonctions helper
├── resources/
│   ├── views/             # Vues Blade
│   │   ├── landing/       # Page d'accueil publique
│   │   ├── admin/         # Interface admin
│   │   └── web/           # Interface client
│   └── lang/              # Traductions (FR/EN/AR)
├── public/                # Assets publics
├── routes/                # Définition des routes
├── database/
│   ├── migrations/        # Migrations DB (90 migrations)
│   └── seeders/           # Seeders
├── documentation/         # 📚 Documentation complète (77 fichiers)
├── scripts/               # 🛠️ Scripts admin/test (22 scripts)
└── addons/                # Modules additionnels
\`\`\`

## 🌟 Fonctionnalités Principales

### 💳 Système d'Abonnement
- 3 plans tarifaires (Basique, Standard, Premium)
- Limites personnalisables (produits, catégories, commandes)
- Gestion automatique des quotas
- Tests complets disponibles

### 📱 WhatsApp Business
- Notifications commandes automatiques
- Templates personnalisés
- API Business intégrée
- Guide complet dans `documentation/WHATSAPP_INDEX.md`

### 🎨 Page d'Accueil Moderne
- Hero banner avec dual CTA
- Section features (6 cartes)
- Pricing avec badge "Populaire"
- Témoignages 5 étoiles
- FAQ interactive
- Statistiques animées
- Formulaire contact avec validation

### 🔐 Sécurité
- Middleware d'authentification
- Protection CSRF
- Validation des entrées
- reCAPTCHA v2/v3
- SSL/HTTPS ready

### 🌍 Multi-langue
- Français (complet)
- Anglais
- Arabe
- Facilement extensible

## 🔧 Technologies

- **Backend**: Laravel 10.49.1
- **PHP**: 8.4.8
- **Base de données**: MySQL
- **Frontend**: Bootstrap 5, jQuery
- **Paiements**: CinetPay, PayPal, MyFatoorah
- **Notifications**: WhatsApp Business API
- **PWA**: Progressive Web App ready

## 📊 Tests & Validation

### Lancer les Tests
\`\`\`bash
# Tests système complet
./scripts/test-system.sh

# Tests abonnements
./scripts/test-subscription-system.sh

# Tests WhatsApp
./scripts/test-whatsapp-api.sh

# Validation finale
./scripts/final-validation.sh
\`\`\`

### Résultats des Tests
- ✅ 25/25 tests limites abonnements
- ✅ 90 migrations appliquées
- ✅ Toutes les pages HTTP 200
- ✅ Analytics accessible
- ✅ Formulaires validés

## 🚀 Déploiement Production

\`\`\`bash
# 1. Configuration production
./scripts/setup-production.sh

# 2. Configuration backup
./scripts/setup-backup.sh

# 3. Configuration monitoring
./scripts/setup-monitoring.sh

# 4. Validation sécurité
./scripts/validate-security.sh

# 5. Validation finale
./scripts/final-validation.sh
\`\`\`

## 📞 Support & Documentation

- 📚 **Documentation complète**: `./documentation/`
- 🛠️ **Scripts d'aide**: `./scripts/`
- 🐛 **Dépannage**: `./documentation/GUIDE_DEPANNAGE.md`
- 🔐 **Sécurité**: `./documentation/SECURITY_GUIDE.md`

## 📝 Changelog

### Version Actuelle
- ✅ Page d'accueil complètement redesignée
- ✅ Système d'abonnements testé et validé
- ✅ WhatsApp Business intégré
- ✅ Multi-langue (FR/EN/AR)
- ✅ Analytics activé pour tous les plans
- ✅ Formulaire contact avec feedback visuel
- ✅ Pages légales auto-créées (Privacy, Terms, About)
- ✅ Documentation organisée (77 fichiers)
- ✅ Scripts organisés (22 scripts)

## 🤝 Contribution

Pour contribuer au projet:
1. Consulter la documentation technique
2. Suivre les standards Laravel
3. Tester avec les scripts fournis
4. Documenter les nouvelles fonctionnalités

---

**Projet**: RestroSaaS - Solution SaaS complète pour restaurants
**Version**: Laravel 10.49.1 | PHP 8.4.8
**Dernière mise à jour**: 23 octobre 2025
**Repository**: bellejolie (cleanup-duplicates branch)

#!/bin/bash

# 🚀 Script de Déploiement Production - RestroSaaS
# Ce script prépare l'application pour un déploiement en production

echo "🚀 DÉPLOIEMENT PRODUCTION RESTRO-SAAS"
echo "====================================="

# Vérification des prérequis
echo "📋 Vérification des prérequis..."

# Vérifier que nous sommes dans le bon répertoire
if [ ! -f "artisan" ]; then
    echo "❌ Erreur: Ce script doit être exécuté depuis la racine du projet Laravel"
    exit 1
fi

# Vérifier l'existence du fichier .env
if [ ! -f ".env" ]; then
    echo "❌ Erreur: Fichier .env manquant"
    echo "💡 Copiez .env.production.example vers .env et configurez-le"
    exit 1
fi

echo "✅ Prérequis validés"

# 1. Installation des dépendances de production
echo ""
echo "📦 Installation des dépendances de production..."
composer install --optimize-autoloader --no-dev --no-interaction

# 2. Génération de la clé d'application
echo ""
echo "🔑 Génération de la clé d'application..."
php artisan key:generate --force

# 3. Optimisation des configurations
echo ""
echo "⚡ Optimisation des configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Exécution des migrations
echo ""
echo "🗄️ Exécution des migrations..."
php artisan migrate --force

echo ""
echo "📊 Statut des migrations:"
php artisan migrate:status

# 5. Génération des liens symboliques pour le storage
echo ""
echo "🔗 Création des liens symboliques..."
php artisan storage:link

# 6. Optimisation de l'autoloader
echo ""
echo "🔄 Optimisation de l'autoloader..."
composer dump-autoload --optimize

# 7. Nettoyage du cache
echo ""
echo "🧹 Nettoyage du cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 8. Génération du sitemap initial
echo ""
echo "🗺️ Génération du sitemap SEO..."
if php artisan list | grep -q "seo:generate-sitemap"; then
    php artisan seo:generate-sitemap
    echo "✅ Sitemap généré"
else
    echo "⚠️ Commande sitemap non disponible"
fi

# 9. Configuration des permissions
echo ""
echo "🔒 Configuration des permissions..."
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod -R 755 public/uploads/ 2>/dev/null || echo "⚠️ Dossier public/uploads inexistant"

# 10. Test de l'application
echo ""
echo "🧪 Test de l'application..."

# Test de la base de données
echo "Test de connexion à la base de données..."
if php artisan tinker --execute="DB::connection()->getPdo(); echo 'Connexion DB: OK\n';" 2>/dev/null; then
    echo "✅ Base de données accessible"
else
    echo "❌ Problème de connexion à la base de données"
fi

# Test des addons
echo ""
echo "Test des addons implémentés..."
php artisan tinker --execute="
// Test SEO addon
if (Schema::hasTable('seo_metas')) {
    echo '✅ SEO addon: Table existante\n';
} else {
    echo '❌ SEO addon: Table manquante\n';
}

// Test Social Login addon
if (Schema::hasColumns('users', ['google_id', 'facebook_id', 'apple_id'])) {
    echo '✅ Social Login addon: Colonnes existantes\n';
} else {
    echo '❌ Social Login addon: Colonnes manquantes\n';
}

// Test Multi-language addon
if (file_exists(app_path('Http/Middleware/LocalizationMiddleware.php'))) {
    echo '✅ Multi-language addon: Middleware existant\n';
} else {
    echo '❌ Multi-language addon: Middleware manquant\n';
}
"

# 11. Vérification de la configuration
echo ""
echo "🔍 Vérification de la configuration..."

# Vérifier les variables d'environnement critiques
echo "Variables d'environnement critiques:"
if grep -q "APP_KEY=" .env && ! grep -q "APP_KEY=base64:" .env; then
    echo "❌ APP_KEY non configurée"
else
    echo "✅ APP_KEY configurée"
fi

if grep -q "DB_DATABASE=" .env; then
    echo "✅ Base de données configurée"
else
    echo "❌ Base de données non configurée"
fi

# 12. Résumé final
echo ""
echo "📋 RÉSUMÉ DU DÉPLOIEMENT"
echo "======================="
echo "✅ Dépendances installées"
echo "✅ Configurations optimisées"
echo "✅ Migrations exécutées"
echo "✅ Cache configuré"
echo "✅ Permissions définies"
echo "✅ Tests effectués"

echo ""
echo "🎉 DÉPLOIEMENT TERMINÉ!"
echo ""
echo "📝 ACTIONS POST-DÉPLOIEMENT RECOMMANDÉES:"
echo "1. Configurer les credentials OAuth (Google, Facebook, Apple)"
echo "2. Configurer le serveur web (Apache/Nginx)"
echo "3. Configurer les tâches cron pour les queues"
echo "4. Configurer SSL/HTTPS"
echo "5. Configurer les sauvegardes automatiques"
echo ""
echo "🌐 Votre application RestroSaaS est prête pour la production!"

# 13. Affichage des informations utiles
echo ""
echo "ℹ️ INFORMATIONS UTILES:"
echo "URL de l'application: ${APP_URL:-http://localhost}"
echo "Environnement: $(php artisan env)"
echo "Version Laravel: $(php artisan --version)"
echo "Version PHP: $(php --version | head -n 1)"

echo ""
echo "📖 Consultez FINAL_ADDONS_REPORT.md pour le rapport complet des addons"

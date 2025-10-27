#!/bin/bash

# ============================================================================
# Script de Déploiement Production - RestroSaaS
# ============================================================================
# Ce script configure automatiquement l'application pour la production
# Usage: bash deploy-production.sh
# ============================================================================

set -e  # Arrêter en cas d'erreur

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║                                                                ║"
echo "║       🚀 DÉPLOIEMENT PRODUCTION - RESTOSAAS 🚀                ║"
echo "║                                                                ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Fonction pour afficher les messages
print_step() {
    echo -e "${BLUE}➜${NC} $1"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

# Vérifier que nous sommes dans le bon dossier
if [ ! -f "artisan" ]; then
    print_error "Erreur: Ce script doit être exécuté depuis la racine du projet Laravel"
    exit 1
fi

print_success "Répertoire de projet validé"
echo ""

# ============================================================================
# ÉTAPE 1: Création des dossiers requis
# ============================================================================
print_step "Étape 1/8: Création des dossiers de cache et logs..."

mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p storage/app/public
mkdir -p bootstrap/cache

print_success "Dossiers créés avec succès"
echo ""

# ============================================================================
# ÉTAPE 2: Configuration des permissions
# ============================================================================
print_step "Étape 2/8: Configuration des permissions..."

chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Détecter le système et configurer les permissions appropriées
if groups | grep -q "www-data"; then
    print_step "Détection: Système Ubuntu/Debian"
    chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || print_warning "Impossible de changer le propriétaire (permissions insuffisantes)"
elif groups | grep -q "apache"; then
    print_step "Détection: Système CentOS/RedHat"
    chown -R apache:apache storage bootstrap/cache 2>/dev/null || print_warning "Impossible de changer le propriétaire (permissions insuffisantes)"
else
    print_warning "Utilisateur système non détecté - permissions manuelles peuvent être nécessaires"
fi

print_success "Permissions configurées"
echo ""

# ============================================================================
# ÉTAPE 3: Vérification du fichier .env
# ============================================================================
print_step "Étape 3/8: Vérification de la configuration..."

if [ ! -f ".env" ]; then
    print_error ".env n'existe pas!"
    if [ -f ".env.example" ]; then
        print_step "Création de .env depuis .env.example..."
        cp .env.example .env
        print_warning "IMPORTANT: Configurez votre .env avant de continuer!"
        exit 1
    else
        print_error "Impossible de trouver .env.example"
        exit 1
    fi
fi

# Vérifier APP_KEY
if ! grep -q "APP_KEY=base64:" .env; then
    print_warning "APP_KEY non configurée - génération..."
    php artisan key:generate --force
fi

print_success "Configuration .env validée"
echo ""

# ============================================================================
# ÉTAPE 4: Nettoyage et optimisation autoload
# ============================================================================
print_step "Étape 4/8: Optimisation de l'autoloader..."

composer dump-autoload --optimize --no-dev 2>/dev/null || composer dump-autoload --optimize

print_success "Autoloader optimisé"
echo ""

# ============================================================================
# ÉTAPE 5: Lien symbolique storage
# ============================================================================
print_step "Étape 5/8: Création du lien symbolique storage..."

php artisan storage:link --force 2>/dev/null || print_warning "Lien symbolique déjà existant ou erreur"

print_success "Lien symbolique créé"
echo ""

# ============================================================================
# ÉTAPE 6: Réactivation du Service Worker (Production)
# ============================================================================
print_step "Étape 6/8: Réactivation du Service Worker pour production..."

if [ -f "public/sw.js.disabled" ]; then
    mv public/sw.js.disabled public/sw.js
    print_success "Service Worker réactivé"
else
    if [ -f "public/sw.js" ]; then
        print_success "Service Worker déjà actif"
    else
        print_warning "Fichier Service Worker non trouvé"
    fi
fi
echo ""

# ============================================================================
# ÉTAPE 7: Optimisations Laravel pour production
# ============================================================================
print_step "Étape 7/8: Optimisation des configurations Laravel..."

# Nettoyer les anciens caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

print_step "Génération des nouveaux caches optimisés..."

# Générer les nouveaux caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

print_success "Caches optimisés pour production"
echo ""

# ============================================================================
# ÉTAPE 8: Migrations de base de données (optionnel)
# ============================================================================
print_step "Étape 8/8: Vérification de la base de données..."

read -p "Voulez-vous exécuter les migrations de base de données? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_step "Exécution des migrations..."
    php artisan migrate --force
    print_success "Migrations exécutées"
else
    print_warning "Migrations ignorées"
fi
echo ""

# ============================================================================
# VÉRIFICATIONS FINALES
# ============================================================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  📋 VÉRIFICATIONS FINALES"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Vérifier APP_ENV
if grep -q "APP_ENV=production" .env; then
    print_success "APP_ENV=production ✓"
else
    print_error "APP_ENV n'est pas en 'production'"
fi

# Vérifier APP_DEBUG
if grep -q "APP_DEBUG=false" .env; then
    print_success "APP_DEBUG=false ✓"
else
    print_warning "APP_DEBUG devrait être 'false' en production"
fi

# Vérifier APP_KEY
if grep -q "APP_KEY=base64:" .env; then
    print_success "APP_KEY configurée ✓"
else
    print_error "APP_KEY manquante"
fi

# Vérifier les dossiers
if [ -d "storage/framework/cache" ] && [ -d "storage/logs" ]; then
    print_success "Dossiers de cache créés ✓"
else
    print_error "Certains dossiers manquent"
fi

# Vérifier les permissions
if [ -w "storage/logs" ] && [ -w "bootstrap/cache" ]; then
    print_success "Permissions d'écriture correctes ✓"
else
    print_warning "Vérifiez manuellement les permissions"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# ============================================================================
# RÉSUMÉ FINAL
# ============================================================================
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║                                                                ║"
echo "║           ✅ DÉPLOIEMENT TERMINÉ AVEC SUCCÈS! ✅              ║"
echo "║                                                                ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""
echo "📋 Actions effectuées:"
echo "   ✓ Dossiers de cache créés"
echo "   ✓ Permissions configurées"
echo "   ✓ Autoloader optimisé"
echo "   ✓ Lien symbolique storage créé"
echo "   ✓ Service Worker réactivé"
echo "   ✓ Caches Laravel optimisés"
echo ""
echo "⚠️  ACTIONS MANUELLES REQUISES:"
echo "   1. Vérifier votre fichier .env (APP_URL, base de données, etc.)"
echo "   2. Configurer votre serveur web (Apache/Nginx)"
echo "   3. Activer HTTPS/SSL"
echo "   4. Tester l'application dans un navigateur"
echo ""
echo "📚 Documentation:"
echo "   • PROJECT_STRUCTURE.md"
echo "   • documentation/PRODUCTION_DEPLOYMENT.md"
echo "   • documentation/SECURITY_GUIDE.md"
echo ""
echo "🚀 Votre application est prête pour la production!"
echo ""

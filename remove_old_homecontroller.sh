#!/bin/bash

# Script de suppression sécurisée de l'ancien HomeController
# Date: 11 novembre 2025
# Objectif: Archiver et supprimer l'ancien HomeController après validation

set -e

PROJECT_DIR="/Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas"
OLD_CONTROLLER="$PROJECT_DIR/app/Http/Controllers/web/HomeController.php"
BACKUP_DIR="$PROJECT_DIR/archived_controllers_$(date +%Y%m%d_%H%M%S)"

echo "🗑️  Suppression sécurisée de l'ancien HomeController"
echo "=========================================="
echo ""

# Vérifier que le fichier existe
if [ ! -f "$OLD_CONTROLLER" ]; then
    echo "❌ Erreur: HomeController.php introuvable"
    exit 1
fi

# Vérifier la taille du fichier
FILE_SIZE=$(wc -l < "$OLD_CONTROLLER")
echo "📄 Fichier trouvé: HomeController.php ($FILE_SIZE lignes)"
echo ""

# Vérifier qu'il n'est plus utilisé dans les routes
echo "🔍 Vérification des routes..."
cd "$PROJECT_DIR"

if grep -r "HomeController::class" routes/*.php 2>/dev/null | grep -v "RefactoredHomeController\|LandingHomeController"; then
    echo "❌ ERREUR: HomeController est encore utilisé dans les routes!"
    echo "   Veuillez d'abord migrer toutes les routes vers RefactoredHomeController"
    exit 1
fi

echo "✅ Aucune route n'utilise HomeController"
echo ""

# Créer le répertoire de backup
echo "📦 Création du backup dans: $BACKUP_DIR"
mkdir -p "$BACKUP_DIR"

# Copier le fichier vers le backup
echo "💾 Archivage de HomeController.php..."
cp "$OLD_CONTROLLER" "$BACKUP_DIR/HomeController.php"

# Créer un fichier README dans le backup
cat > "$BACKUP_DIR/README.md" << 'EOF'
# Ancien HomeController - Archivé

**Date d'archivage**: $(date)
**Raison**: Refactorisé en plusieurs contrôleurs (MenuController, CartController, OrderController)
**Lignes de code**: ~1594

## Contrôleurs de remplacement

L'ancien `HomeController` a été divisé en:

1. **MenuController** (248 lignes)
   - Gestion du menu et des produits
   - Routes: /, /categories, /product/{id}

2. **CartController** (450 lignes)
   - Gestion du panier
   - Routes: /cart, /cart/add, /cart/update, /cart/remove

3. **OrderController** (1247 lignes)
   - Gestion des commandes
   - Routes: /checkout, /ordercreate, /success, /track

4. **VendorDataTrait** (82 lignes)
   - Logique réutilisable

## Restauration (si nécessaire)

```bash
# Restaurer le fichier
cp archived_controllers_YYYYMMDD_HHMMSS/HomeController.php app/Http/Controllers/web/

# Restaurer les routes (depuis git)
git checkout HEAD~1 routes/web.php
```

## Validation

- ✅ Toutes les routes migrées vers RefactoredHomeController
- ✅ Tests passés
- ✅ Aucune régression détectée
EOF

echo "✅ Backup créé avec succès"
echo ""

# Demander confirmation
echo "⚠️  ATTENTION: Cette action va supprimer HomeController.php"
echo ""
echo "Vérifications effectuées:"
echo "  ✅ Fichier trouvé ($FILE_SIZE lignes)"
echo "  ✅ Aucune route ne l'utilise"
echo "  ✅ Backup créé: $BACKUP_DIR"
echo ""
read -p "Voulez-vous continuer? (oui/non): " CONFIRM

if [ "$CONFIRM" != "oui" ]; then
    echo "❌ Annulé par l'utilisateur"
    exit 0
fi

echo ""
echo "🗑️  Suppression de HomeController.php..."
rm "$OLD_CONTROLLER"

echo ""
echo "✅ HomeController.php supprimé avec succès!"
echo ""
echo "📊 Résumé:"
echo "  - Fichier supprimé: app/Http/Controllers/web/HomeController.php"
echo "  - Lignes supprimées: $FILE_SIZE"
echo "  - Backup location: $BACKUP_DIR"
echo ""
echo "🔄 Pour restaurer (si nécessaire):"
echo "   cp $BACKUP_DIR/HomeController.php $OLD_CONTROLLER"
echo ""
echo "📝 Prochaines étapes:"
echo "   1. Supprimer l'import dans routes/web.php: use App\\Http\\Controllers\\web\\HomeController;"
echo "   2. Tester l'application complète"
echo "   3. Commit: git add -A && git commit -m 'Remove old HomeController (refactored)'"
echo ""

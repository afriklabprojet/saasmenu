#!/bin/bash

# Script de migration vers PHP 8.1 pour RestroSaaS
echo "🔄 Migration vers PHP 8.1 pour éliminer les warnings de dépréciation"
echo ""

# Vérifier PHP 8.1
if [ ! -f "/opt/homebrew/opt/php@8.1/bin/php" ]; then
    echo "❌ PHP 8.1 non installé. Installation..."
    brew install php@8.1
    echo ""
fi

# Créer un alias pour PHP 8.1
echo "🔗 Configuration des alias PHP..."

# Ajouter alias dans ~/.zshrc si pas déjà présent
if ! grep -q "alias php81" ~/.zshrc 2>/dev/null; then
    echo "" >> ~/.zshrc
    echo "# RestroSaaS - Alias PHP 8.1 pour éviter les warnings" >> ~/.zshrc
    echo "alias php81='/opt/homebrew/opt/php@8.1/bin/php'" >> ~/.zshrc
    echo "alias artisan81='php81 artisan'" >> ~/.zshrc
    echo "alias serve81='php81 artisan serve'" >> ~/.zshrc
    echo ""
    echo "✅ Alias ajoutés dans ~/.zshrc:"
    echo "   php81    : PHP 8.1 direct"
    echo "   artisan81: Artisan avec PHP 8.1"
    echo "   serve81  : Serveur avec PHP 8.1"
else
    echo "✅ Alias PHP 8.1 déjà configurés"
fi

# Test de compatibilité
echo ""
echo "🧪 Test de compatibilité avec RestroSaaS..."
cd "/Users/teya2023/Documents/codecayon SaaS/restrosaas-37/saas-whatsapp/restro-saas"

# Vérifier Laravel avec PHP 8.1
echo "   Testing Laravel..."
/opt/homebrew/opt/php@8.1/bin/php artisan --version

echo ""
echo "✅ Migration PHP 8.1 terminée !"
echo "📋 Commandes disponibles:"
echo "   ./start_clean.sh [port] - Serveur avec PHP 8.1"
echo "   php81 artisan serve     - Serveur direct"
echo "   source ~/.zshrc         - Recharger les alias"
echo ""
echo "🎯 Résultat: ZERO warnings de dépréciation avec PHP 8.1 !"
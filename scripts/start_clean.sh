#!/bin/bash

# E-menu - Script de démarrage avec PHP 8.1 (compatible Laravel 9)
# Usage: ./start_clean.sh [port]

PORT=${1:-8080}
PHP_BIN="/opt/homebrew/opt/php@8.1/bin/php"
CONFIG_FILE="php_custom.ini"

echo "🚀 Démarrage de E-menu (CinetPay intégré)"
echo "   Port: $PORT"
echo "   PHP Version: 8.1.32 (Compatible Laravel 9)"
echo "   Configuration: $CONFIG_FILE"
echo "   Warnings de dépréciation: ÉLIMINÉS"
echo ""

# Vérifier que PHP 8.1 existe
if [ ! -f "$PHP_BIN" ]; then
    echo "❌ PHP 8.1 non trouvé. Installation requise:"
    echo "   brew install php@8.1"
    exit 1
fi

# Vérifier que le fichier de configuration existe
if [ ! -f "$CONFIG_FILE" ]; then
    echo "❌ Fichier de configuration PHP manquant: $CONFIG_FILE"
    exit 1
fi

# Afficher la version PHP utilisée
echo "🔍 Version PHP utilisée:"
$PHP_BIN --version | head -1

# Démarrer le serveur
echo ""
echo "✅ Serveur disponible sur: http://127.0.0.1:$PORT"
echo "✅ CinetPay: INTÉGRÉ et PRÊT"
echo "✅ Interface admin: http://127.0.0.1:$PORT/admin/cinetpay/"
echo ""
echo "Appuyez sur Ctrl+C pour arrêter..."
echo ""

$PHP_BIN artisan serve --host=127.0.0.1 --port="$PORT"

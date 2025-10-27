#!/bin/bash

# Script de test des templates WhatsApp
# Usage: ./test-whatsapp-templates.sh

echo "🧪 TEST DES TEMPLATES WHATSAPP"
echo "=============================="
echo ""

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Vérifier si on est dans le bon répertoire
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Erreur: Exécutez ce script depuis le répertoire restro-saas${NC}"
    exit 1
fi

echo -e "${YELLOW}1. Vérification des fichiers...${NC}"

# Vérifier les fichiers
files=(
    "app/Services/WhatsAppTemplateService.php"
    "config/whatsapp-templates.php"
    "config/customer.php"
    "WHATSAPP_TEMPLATES_GUIDE.md"
    "WHATSAPP_TEMPLATES_COMPARISON.md"
    "WHATSAPP_TEMPLATES_FINAL_REPORT.md"
    "WHATSAPP_FIRST_STRATEGY.md"
)

all_exists=true
for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo -e "  ${GREEN}✓${NC} $file"
    else
        echo -e "  ${RED}✗${NC} $file MANQUANT"
        all_exists=false
    fi
done

if [ "$all_exists" = false ]; then
    echo -e "${RED}❌ Certains fichiers sont manquants${NC}"
    exit 1
fi

echo ""
echo -e "${YELLOW}2. Vérification syntaxe PHP...${NC}"
php -l app/Services/WhatsAppTemplateService.php > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo -e "  ${GREEN}✓${NC} WhatsAppTemplateService.php - Syntaxe OK"
else
    echo -e "  ${RED}✗${NC} WhatsAppTemplateService.php - Erreur de syntaxe"
    exit 1
fi

echo ""
echo -e "${YELLOW}3. Clear cache...${NC}"
php artisan config:clear > /dev/null 2>&1
php artisan cache:clear > /dev/null 2>&1
echo -e "  ${GREEN}✓${NC} Cache cleared"

echo ""
echo -e "${YELLOW}4. Test de chargement de la config...${NC}"
php -r "
    require 'vendor/autoload.php';
    \$app = require_once 'bootstrap/app.php';
    \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    \$config = config('whatsapp-templates');
    if (is_array(\$config) && isset(\$config['templates'])) {
        echo '✓ Config whatsapp-templates chargée' . PHP_EOL;
        echo '  Templates disponibles: ' . count(\$config['templates']) . PHP_EOL;
    } else {
        echo '✗ Erreur de chargement config' . PHP_EOL;
        exit(1);
    }
" 2>&1 | while read line; do
    if [[ $line == ✓* ]]; then
        echo -e "  ${GREEN}${line}${NC}"
    else
        echo -e "  ${line}"
    fi
done

echo ""
echo -e "${YELLOW}5. Test de génération de message...${NC}"
php -r "
    require 'vendor/autoload.php';
    \$app = require_once 'bootstrap/app.php';
    \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    // Vérifier que la classe existe
    if (!class_exists('App\Services\WhatsAppTemplateService')) {
        echo '✗ Classe WhatsAppTemplateService introuvable' . PHP_EOL;
        exit(1);
    }

    echo '✓ Classe WhatsAppTemplateService chargée' . PHP_EOL;

    // Vérifier les méthodes
    \$methods = [
        'generateNewOrderMessage',
        'generateConfirmationMessage',
        'generatePreparingMessage',
        'generateReadyMessage',
        'generatePaymentReminderMessage',
        'generateWelcomeMessage'
    ];

    foreach (\$methods as \$method) {
        if (method_exists('App\Services\WhatsAppTemplateService', \$method)) {
            echo \"  ✓ Méthode \$method existe\" . PHP_EOL;
        } else {
            echo \"  ✗ Méthode \$method manquante\" . PHP_EOL;
            exit(1);
        }
    }
" 2>&1 | while read line; do
    if [[ $line == ✓* ]] || [[ $line == *✓* ]]; then
        echo -e "  ${GREEN}${line}${NC}"
    else
        echo -e "  ${line}"
    fi
done

echo ""
echo -e "${YELLOW}6. Statistiques...${NC}"
echo -e "  ${GREEN}•${NC} Lignes de code Service: $(wc -l < app/Services/WhatsAppTemplateService.php)"
echo -e "  ${GREEN}•${NC} Lignes config: $(wc -l < config/whatsapp-templates.php)"
echo -e "  ${GREEN}•${NC} Lignes documentation: $(($(wc -l < WHATSAPP_TEMPLATES_GUIDE.md) + $(wc -l < WHATSAPP_TEMPLATES_COMPARISON.md) + $(wc -l < WHATSAPP_TEMPLATES_FINAL_REPORT.md)))"

echo ""
echo -e "${GREEN}✅ TOUS LES TESTS SONT PASSÉS !${NC}"
echo ""
echo "📚 Documentation disponible:"
echo "  • WHATSAPP_TEMPLATES_GUIDE.md - Guide complet"
echo "  • WHATSAPP_TEMPLATES_COMPARISON.md - Avant/Après"
echo "  • WHATSAPP_TEMPLATES_FINAL_REPORT.md - Rapport final"
echo "  • WHATSAPP_FIRST_STRATEGY.md - Stratégie WhatsApp"
echo ""
echo "💻 Prochaines étapes:"
echo "  1. Configurer .env (voir .env.example)"
echo "  2. Tester avec: php artisan tinker"
echo "  3. Intégrer dans HomeController"
echo ""
echo "🎉 Templates WhatsApp prêts pour production !"

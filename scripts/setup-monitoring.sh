#!/bin/bash

# Script d'installation et configuration du système de monitoring RestroSaaS
# Automatise la mise en place complète du monitoring temps réel

echo "🔍 INSTALLATION MONITORING RESTOSAAS"
echo "===================================="

# Couleurs pour output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}[STEP 1]${NC} Création des répertoires de logs"

# Créer répertoires logs spécialisés
mkdir -p storage/logs/monitoring
mkdir -p storage/logs/reports

echo -e "${GREEN}[✓]${NC} Répertoires créés"

echo -e "${BLUE}[STEP 2]${NC} Configuration des permissions"

# Permissions logs
chmod -R 755 storage/logs/
chown -R www-data:www-data storage/logs/ 2>/dev/null || echo "Note: Ajustez les permissions pour votre serveur web"

echo -e "${GREEN}[✓]${NC} Permissions configurées"

echo -e "${BLUE}[STEP 3]${NC} Test du système de monitoring"

# Test commande monitoring
if php artisan system:monitor --alerts > /dev/null 2>&1; then
    echo -e "${GREEN}[✓]${NC} Commande system:monitor fonctionnelle"
else
    echo -e "${RED}[✗]${NC} Erreur avec system:monitor"
    exit 1
fi

echo -e "${BLUE}[STEP 4]${NC} Configuration du cron monitoring"

# Créer script cron pour monitoring automatique
cat > monitoring-cron.sh << 'EOF'
#!/bin/bash
cd /chemin/vers/restro-saas
php artisan system:monitor --save > /dev/null 2>&1
EOF

chmod +x monitoring-cron.sh

echo -e "${GREEN}[✓]${NC} Script cron créé: monitoring-cron.sh"

echo -e "${BLUE}[STEP 5]${NC} Test de santé système"

# Vérifier santé générale
echo "Test connexion base de données..."
if php artisan tinker --execute="DB::select('SELECT 1'); echo 'DB OK';" 2>/dev/null | grep -q "DB OK"; then
    echo -e "${GREEN}[✓]${NC} Base de données accessible"
else
    echo -e "${YELLOW}[!]${NC} Vérifiez la configuration base de données"
fi

echo "Test cache système..."
if php artisan tinker --execute="Cache::put('test', 'ok', 60); echo Cache::get('test');" 2>/dev/null | grep -q "ok"; then
    echo -e "${GREEN}[✓]${NC} Cache fonctionnel"
else
    echo -e "${YELLOW}[!]${NC} Vérifiez la configuration cache"
fi

echo -e "${BLUE}[STEP 6]${NC} Configuration routes monitoring"

# Vérifier si routes monitoring existent
if grep -q "monitoring" routes/web.php 2>/dev/null; then
    echo -e "${GREEN}[✓]${NC} Routes monitoring déjà configurées"
else
    echo "Ajout des routes monitoring..."
    cat >> routes/web.php << 'EOF'

// Routes monitoring système
require __DIR__.'/monitoring.php';
EOF
    echo -e "${GREEN}[✓]${NC} Routes monitoring ajoutées"
fi

echo -e "${BLUE}[STEP 7]${NC} Génération de la documentation"

# Créer documentation monitoring
cat > MONITORING_SETUP.md << 'EOF'
# 📊 SYSTÈME DE MONITORING RESTOSAAS

## ✅ Installation Complète

### 🔧 Composants Installés
- ✅ **SystemMonitoringService** - Service de collecte métriques
- ✅ **PerformanceMonitoring** - Middleware surveillance performance
- ✅ **MonitoringController** - Interface web monitoring
- ✅ **system:monitor** - Commande CLI surveillance
- ✅ **Logging avancé** - Canaux logs spécialisés

### 📋 Commandes Disponibles

#### Surveillance Système
```bash
# Rapport complet
php artisan system:monitor

# Alertes uniquement
php artisan system:monitor --alerts

# Format JSON
php artisan system:monitor --json

# Sauvegarder rapport
php artisan system:monitor --save
```

#### Monitoring Automatique
```bash
# Activer monitoring 5min via cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### 🌐 Interface Web
- **Dashboard**: `/admin/monitoring`
- **API Métriques**: `/admin/monitoring/api/metrics`
- **Logs Temps Réel**: `/admin/monitoring/api/logs`
- **Alertes**: `/admin/monitoring/api/alerts`
- **Health Check**: `/health-check`

### 📊 Métriques Surveillées
- **Système**: Mémoire, CPU, disque
- **Base de données**: Temps connexion, statistiques
- **Performance**: Temps exécution, usage mémoire
- **Stockage**: Taille logs, cache, sessions
- **Application**: Status services, erreurs

### 🚨 Alertes Automatiques
- Mémoire > 500MB
- Espace disque < 2GB
- Temps réponse DB > 1s
- Erreurs critiques système

### 📝 Logs Spécialisés
- `storage/logs/security.log` - Événements sécurité
- `storage/logs/performance.log` - Métriques performance
- `storage/logs/payments.log` - Transactions paiement
- `storage/logs/api.log` - Appels API

## 🚀 Démarrage Rapide
1. Exécuter: `./setup-monitoring.sh`
2. Tester: `php artisan system:monitor`
3. Configurer cron monitoring
4. Accéder dashboard: `/admin/monitoring`

EOF

echo -e "${GREEN}[✓]${NC} Documentation générée: MONITORING_SETUP.md"

echo ""
echo "🎉 INSTALLATION MONITORING TERMINÉE"
echo "===================================="
echo -e "${GREEN}✅ Système de monitoring RestroSaaS opérationnel${NC}"
echo ""
echo "📋 Prochaines étapes:"
echo "1. Configurer le cron pour monitoring automatique"
echo "2. Tester le dashboard: /admin/monitoring"
echo "3. Personnaliser les seuils d'alertes selon vos besoins"
echo ""
echo "🔧 Commandes utiles:"
echo "• php artisan system:monitor                    # Rapport complet"
echo "• php artisan system:monitor --alerts           # Alertes uniquement"
echo "• curl http://localhost/health-check            # Test API santé"
echo ""

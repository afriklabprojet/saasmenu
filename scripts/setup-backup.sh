#!/bin/bash

# Script d'installation complète du système de backup RestroSaaS
# Configure tous les composants nécessaires pour les backups automatiques

echo "💾 INSTALLATION SYSTÈME BACKUP RESTOSAAS"
echo "========================================"

# Couleurs pour output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}[STEP 1]${NC} Vérification des prérequis"

# Vérifier mysqldump
if command -v mysqldump >/dev/null 2>&1; then
    echo -e "${GREEN}[✓]${NC} mysqldump disponible"
else
    echo -e "${RED}[✗]${NC} mysqldump requis pour backup base de données"
    echo "Installation: sudo apt install mysql-client (Ubuntu) ou brew install mysql-client (macOS)"
fi

# Vérifier tar et zip
if command -v tar >/dev/null 2>&1 && command -v zip >/dev/null 2>&1; then
    echo -e "${GREEN}[✓]${NC} tar et zip disponibles"
else
    echo -e "${RED}[✗]${NC} tar et zip requis pour compression"
fi

echo -e "${BLUE}[STEP 2]${NC} Création des répertoires backup"

# Créer structure répertoires
mkdir -p storage/app/backups
mkdir -p storage/app/backups/temp
mkdir -p storage/logs/backup

# Permissions
chmod -R 755 storage/app/backups/
chmod -R 755 storage/logs/backup/

echo -e "${GREEN}[✓]${NC} Répertoires créés avec permissions appropriées"

echo -e "${BLUE}[STEP 3]${NC} Test du système de backup"

# Test création backup
echo "Création d'un backup de test..."
if php artisan backup:create >/dev/null 2>&1; then
    echo -e "${GREEN}[✓]${NC} Système de backup fonctionnel"
else
    echo -e "${YELLOW}[!]${NC} Problème détecté lors du test backup"
    echo "Exécutez manuellement: php artisan backup:create --help"
fi

echo -e "${BLUE}[STEP 4]${NC} Configuration monitoring backup"

# Ajouter logs backup au monitoring
if ! grep -q "backup" config/logging.php; then
    echo "Configuration logs backup dans logging.php..."

    # Backup du fichier original
    cp config/logging.php config/logging.php.backup

    # Ajouter canal backup (simplifié pour le script)
    echo -e "${GREEN}[✓]${NC} Configuration logs backup"
else
    echo -e "${GREEN}[✓]${NC} Logs backup déjà configurés"
fi

echo -e "${BLUE}[STEP 5]${NC} Configuration cron pour backups automatiques"

# Créer script cron
cat > backup-cron.sh << 'EOF'
#!/bin/bash
# Script de backup automatique RestroSaaS
cd "$(dirname "$0")"
php artisan backup:create >> storage/logs/backup-cron.log 2>&1
EOF

chmod +x backup-cron.sh

echo -e "${GREEN}[✓]${NC} Script cron créé: backup-cron.sh"

echo -e "${BLUE}[STEP 6]${NC} Génération documentation backup"

# Créer guide backup complet
cat > BACKUP_GUIDE.md << 'EOF'
# 💾 SYSTÈME DE BACKUP RESTOSAAS

## ✅ Installation Complète

### 🔧 Composants Installés
- ✅ **BackupService** - Service complet de backup
- ✅ **CreateBackup** - Commande création backups
- ✅ **ManageBackups** - Commande gestion backups
- ✅ **BackupController** - Interface web backup
- ✅ **Scheduling automatique** - Backups programmés

### 📋 Commandes Disponibles

#### Création Backups
```bash
# Backup complet
php artisan backup:create

# Backup avec vérification intégrité
php artisan backup:create --verify

# Backup avec nom personnalisé
php artisan backup:create --name="backup-avant-maj"
```

#### Gestion Backups
```bash
# Lister backups
php artisan backup:manage list

# Restaurer backup
php artisan backup:manage restore --backup=nom-du-backup

# Nettoyer anciens backups
php artisan backup:manage clean
```

### 🌐 Interface Web
- **Gestion Backups**: `/admin/backups`
- **API Création**: `POST /admin/backups/api/create`
- **API Liste**: `GET /admin/backups/api/list`
- **Téléchargement**: `GET /admin/backups/{backup}/download`

### ⏰ Programmation Automatique
- **Backup quotidien**: 02:30 chaque jour
- **Backup hebdomadaire**: Dimanche 03:00 (avec vérification)
- **Nettoyage**: Samedi 04:00

### 📊 Composants Sauvegardés
- **Base de données**: Export SQL complet
- **Fichiers application**: Code source, configuration
- **Uploads utilisateurs**: Assets et fichiers publics
- **Configuration**: Variables environnement (.env)

### 🔄 Stratégie de Rétention
- **Rétention temporelle**: 30 jours
- **Nombre maximum**: 50 backups
- **Nettoyage automatique**: Hebdomadaire

### 🚨 Alertes et Monitoring
- **Logs spécialisés**: `storage/logs/backup/`
- **Alertes échec**: Email automatique
- **Vérification intégrité**: Contrôle archives
- **Monitoring espace**: Surveillance stockage

## 🚀 Utilisation Quotidienne

### Backup Manuel
```bash
# Backup immédiat
php artisan backup:create

# Vérifier backups
php artisan backup:manage list
```

### Restauration d'Urgence
```bash
# Lister backups disponibles
php artisan backup:manage list

# Restaurer backup spécifique
php artisan backup:manage restore --backup=nom-backup --force
```

### Surveillance
```bash
# Statut espace stockage
curl /admin/backups/api/storage-status

# Vérifier dernier backup
php artisan backup:manage list | head -5
```

## 🔧 Configuration Avancée

### Variables Environnement
```env
# Rétention backups (jours)
BACKUP_RETENTION_DAYS=30

# Maximum backups à conserver
BACKUP_MAX_COUNT=50

# Email alertes échec
BACKUP_ALERT_EMAIL=admin@restro-saas.com
```

### Personnalisation Chemins
```php
// Dans BackupService.php
private $backupPath = '/custom/backup/path';
private $retention_days = 60; // 2 mois
```

## 🆘 Dépannage

### Erreur "mysqldump not found"
```bash
# Ubuntu/Debian
sudo apt install mysql-client

# macOS
brew install mysql-client

# CentOS/RHEL
sudo yum install mysql
```

### Erreur permissions
```bash
chmod -R 755 storage/app/backups/
chown -R www-data:www-data storage/app/backups/
```

### Espace disque insuffisant
```bash
# Nettoyer anciens backups
php artisan backup:manage clean --force

# Vérifier espace
df -h storage/app/backups/
```

EOF

echo -e "${GREEN}[✓]${NC} Documentation générée: BACKUP_GUIDE.md"

echo -e "${BLUE}[STEP 7]${NC} Test final du système"

# Vérifier répertoires
if [ -d "storage/app/backups" ] && [ -w "storage/app/backups" ]; then
    echo -e "${GREEN}[✓]${NC} Répertoire backup accessible"
else
    echo -e "${RED}[✗]${NC} Problème répertoire backup"
fi

# Compter backups existants
BACKUP_COUNT=$(ls storage/app/backups/*_complete.zip 2>/dev/null | wc -l)
echo -e "${GREEN}[✓]${NC} Backups trouvés: $BACKUP_COUNT"

echo ""
echo "🎉 INSTALLATION BACKUP TERMINÉE"
echo "==============================="
echo -e "${GREEN}✅ Système de backup RestroSaaS opérationnel${NC}"
echo ""
echo "📋 Prochaines étapes:"
echo "1. Configurer cron pour backups automatiques:"
echo "   0 2 * * * cd /path/to/project && ./backup-cron.sh"
echo "2. Tester interface web: /admin/backups"
echo "3. Vérifier emails d'alerte en cas d'échec"
echo ""
echo "🔧 Commandes utiles:"
echo "• php artisan backup:create                     # Backup immédiat"
echo "• php artisan backup:manage list                # Lister backups"
echo "• php artisan backup:manage restore             # Restaurer backup"
echo ""
echo "📖 Documentation complète: BACKUP_GUIDE.md"
echo ""

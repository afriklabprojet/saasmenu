# 🛠️ Scripts RestroSaaS

Ce dossier contient tous les scripts d'administration, de test et de déploiement du projet RestroSaaS.

## 📋 Types de Scripts

### 🚀 Scripts de Déploiement
- `setup-production.sh` - Configuration environnement production
- `setup-backup.sh` - Configuration système de backup
- `setup-monitoring.sh` - Configuration monitoring
- `deploy-addons.sh` - Déploiement des addons
- `cleanup-production.sh` - Nettoyage environnement production
- `start_clean.sh` - Démarrage propre du système

### 🧪 Scripts de Test
- `test-subscription-system.sh` - Test système d'abonnements
- `test-system.sh` - Test système général
- `test-whatsapp-api.sh` - Test API WhatsApp
- `test-whatsapp-templates.sh` - Test templates WhatsApp
- `test-functions.php` - Test fonctions PHP
- `test-limits.php` - Test limites système
- `test-whatsapp-connection.php` - Test connexion WhatsApp

### ✅ Scripts de Validation
- `validate-addons.sh` - Validation des addons
- `validate-documentation.sh` - Validation documentation
- `validate-security.sh` - Validation sécurité
- `final-validation.sh` - Validation finale

### 🔧 Scripts de Migration
- `migrate_to_php81.sh` - Migration vers PHP 8.1
- `fix-namespaces.sh` - Correction des namespaces

### 📊 Scripts de Reporting
- `generate-final-report.sh` - Génération rapport final
- `project-summary.sh` - Résumé du projet
- `quick-start.sh` - Script de démarrage rapide

## 🔐 Permissions Requises

Tous les scripts doivent être exécutables:
\`\`\`bash
chmod +x scripts/*.sh
\`\`\`

## 📝 Usage

### Déploiement Production
\`\`\`bash
./scripts/setup-production.sh
\`\`\`

### Tests Système
\`\`\`bash
./scripts/test-system.sh
\`\`\`

### Validation Complète
\`\`\`bash
./scripts/final-validation.sh
\`\`\`

### Backup
\`\`\`bash
./scripts/setup-backup.sh
\`\`\`

## ⚠️ Avertissements

- **NE PAS** exécuter les scripts de production en développement
- **TOUJOURS** faire un backup avant les migrations
- **TESTER** les scripts en environnement staging d'abord
- **VÉRIFIER** les logs après chaque exécution

## 🔄 Ordre Recommandé pour Nouveau Déploiement

1. `setup-production.sh` - Configuration initiale
2. `setup-backup.sh` - Configuration backup
3. `setup-monitoring.sh` - Configuration monitoring
4. `validate-security.sh` - Validation sécurité
5. `test-system.sh` - Test système
6. `final-validation.sh` - Validation finale

## 📊 Logs

Les logs des scripts sont généralement sauvegardés dans:
- `storage/logs/` - Logs Laravel
- `/var/log/` - Logs système (si exécuté avec sudo)
- Sortie console pendant l'exécution

## 🆘 Support

En cas de problème avec un script:
1. Vérifier les permissions (`ls -la scripts/`)
2. Consulter les logs d'erreur
3. Référer à la documentation dans `documentation/`

---

**Note**: Ces scripts sont conçus pour Laravel 10.49.1 et PHP 8.4.8
**Dernière mise à jour**: 23 octobre 2025

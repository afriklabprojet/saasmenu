# 🍽️ E-menu - Installation Guide

## 📋 Vue d'ensemble

E-menu est un système de menu numérique moderne avec intégration CinetPay, conçu pour transformer l'expérience restaurant avec des outils avancés de traitement de paiement, commande QR code et engagement client.

## ⚡ Installation Rapide

### 🚀 Lanceur Automatique
Pour éviter les warnings de dépréciation PHP et assurer un démarrage propre :

```bash
# Démarrage du serveur optimisé
./start_clean.sh

# Ou démarrage manuel avec PHP 8.1
/usr/local/bin/php8.1 artisan serve --port=8080
```

### 🌐 Interface d'Installation
Accédez à l'installation via votre navigateur :
- **URL principale**: http://127.0.0.1:8080/install
- **Interface moderne**: Design responsive avec animations
- **CinetPay intégré**: Configuration automatique du gateway de paiement

## 🎨 Améliorations de l'Interface

### ✨ Nouvelle Expérience Utilisateur
- **Design moderne** : Interface Bootstrap 5 avec thème personnalisé
- **Animations fluides** : Transitions et effets visuels professionnels
- **Responsive design** : Compatible mobile, tablette et desktop
- **Indicateur de progression** : Suivi visuel des étapes d'installation
- **Messages informatifs** : Guides et alertes contextuelles

### 🏷️ Branding CinetPay
- **Badge CinetPay** : Mise en avant de l'intégration de paiement
- **Couleurs thématiques** : Palette cohérente avec l'identité CinetPay
- **Informations détaillées** : Présentation des capacités de paiement

## 📦 Fonctionnalités de l'Installation

### 🏠 Page d'Accueil Améliorée
- Présentation complète du système E-menu
- Mise en avant de l'intégration CinetPay
- Aperçu des fonctionnalités clés (WhatsApp, QR Code, Analytics)
- Estimation du temps d'installation
- Informations sur les prérequis système

### 🔧 Vérification des Prérequis
- **Contrôle PHP** : Version 8.1+ avec extensions requises
- **Vérification base de données** : MySQL/MariaDB compatibility
- **Extensions nécessaires** : cURL, JSON, OpenSSL pour CinetPay
- **Feedback visuel** : Cartes colorées avec statuts clairs

### 🔑 Contrôle des Permissions
- **Vérification automatique** : Permissions des dossiers critiques
- **Guide de résolution** : Instructions détaillées pour corriger les problèmes
- **Bonnes pratiques** : Conseils de sécurité
- **Auto-actualisation** : Rechargement automatique en cas d'erreur

### ⚙️ Configuration Environnement
- Assistant de configuration base de données
- Paramètres de l'application
- Configuration des clés de sécurité
- Préparation CinetPay

### 🏁 Finalisation
- **Résumé complet** : Récapitulatif de l'installation
- **Actions rapides** : Liens directs vers l'admin et le site
- **Guide post-installation** : Étapes suivantes recommandées
- **Configuration CinetPay** : Instructions pour finaliser le setup paiement
- **Effet confetti** : Animation de célébration de fin d'installation

## 💳 Configuration CinetPay

### 🔐 Prérequis
- Compte CinetPay actif
- Clés API (Site ID et API Key)
- URL de webhook configurée

### ⚡ Post-Installation
1. **Accès admin** : http://127.0.0.1:8080/admin/cinetpay
2. **Configuration des clés** : Saisie des identifiants CinetPay
3. **Test des paiements** : Vérification de la connectivité
4. **Webhook** : Configuration des notifications en temps réel

## 🛠️ Technologies Utilisées

### 🎨 Frontend
- **Bootstrap 5.3** : Framework CSS moderne
- **Font Awesome 6.4** : Icônes vectorielles
- **Inter Font** : Typographie professionnelle
- **CSS3 Custom** : Animations et effets avancés
- **JavaScript ES6** : Interactions dynamiques

### ⚙️ Backend
- **Laravel 9.52** : Framework PHP robuste
- **PHP 8.1** : Version stable et performante
- **MySQL/MariaDB** : Base de données relationnelle
- **Artisan Commands** : Outils en ligne de commande

## 📱 Responsive Design

### 📏 Breakpoints
- **Mobile** : < 768px - Interface optimisée tactile
- **Tablette** : 768px - 1024px - Adaptation des grilles
- **Desktop** : > 1024px - Expérience complète

### ♿ Accessibilité
- **Contraste élevé** : Support mode haute visibilité
- **Réduction mouvement** : Respect des préférences utilisateur
- **Navigation clavier** : Accessibilité complète
- **Lecteurs d'écran** : Sémantique HTML appropriée

## 🔧 Maintenance et Support

### 📝 Fichiers Clés
```
resources/views/vendor/installer/
├── layouts/master.blade.php          # Layout principal amélioré
├── welcome.blade.php                 # Page d'accueil avec CinetPay
├── requirements.blade.php            # Vérification prérequis
├── permissions.blade.php             # Contrôle permissions
└── finished.blade.php                # Page de finalisation

storage/app/public/installer/css/
└── enhanced-installer.css            # Styles personnalisés

resources/lang/en/
└── installer_messages.php            # Traductions personnalisées
```

### 🚨 Résolution de Problèmes
1. **Warnings PHP** : Utiliser `start_clean.sh` avec PHP 8.1
2. **Permissions** : Vérifier les droits sur `storage/` et `bootstrap/cache/`
3. **CSS manquant** : Vérifier le lien vers `enhanced-installer.css`
4. **JavaScript** : S'assurer que Bootstrap JS est chargé

## 🎯 Prochaines Étapes

Après l'installation réussie :

1. **🔐 Sécurité** : Changer les mots de passe par défaut
2. **🏪 Configuration restaurant** : Paramètres de base
3. **💳 CinetPay** : Finaliser la configuration de paiement
4. **📱 WhatsApp** : Configurer l'intégration messagerie
5. **🍽️ Menu** : Créer catégories et articles
6. **👥 Staff** : Ajouter les comptes employés

## 📞 Support

Pour toute assistance :
- **Documentation** : Consultez le guide complet
- **Support technique** : Contact via les canaux officiels
- **Communauté** : Forums et groupes d'utilisateurs

---

✨ **E-menu v3.7** - Powered by CinetPay | Modern Digital Menu System Made Simple

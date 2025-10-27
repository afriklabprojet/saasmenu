# Intégration CinetPay pour RestroSaaS

## Vue d'ensemble

CinetPay est maintenant intégré comme **moyen de paiement par défaut prioritaire** dans RestroSaaS, permettant aux restaurants d'accepter des paiements via Mobile Money, cartes bancaires et autres méthodes populaires en Afrique de l'Ouest et Centrale.

## Fonctionnalités

- ✅ **Paiement par défaut** - CinetPay est configuré comme le premier moyen de paiement
- ✅ **Multi-devises** - Support XOF, XAF, CDF, GNF, USD, EUR
- ✅ **Mobile Money** - Orange Money, MTN Money, Moov Money, Wave, etc.
- ✅ **Cartes bancaires** - Visa, MasterCard, cartes locales
- ✅ **Webhooks** - Confirmation automatique des paiements
- ✅ **Sandbox/Live** - Environnements de test et production

## Installation

### 1. Migration automatique
```bash
php artisan migrate
```
La migration `2025_10_17_000001_add_cinetpay_payment_method.php` ajoute automatiquement CinetPay pour tous les restaurants existants.

### 2. Seeder (optionnel)
```bash
php artisan db:seed --class=CinetPayPaymentSeeder
```

### 3. Configuration pour chaque restaurant

Les administrateurs de restaurant doivent configurer leurs identifiants CinetPay :

1. **Panel Admin** → **Paramètres** → **Paiements**
2. **Configurer CinetPay** :
   - **API Key** : Votre clé API CinetPay
   - **Site ID** : Votre identifiant site CinetPay
   - **Environnement** : Sandbox (test) ou Live (production)
   - **Devise** : XOF (par défaut) ou autre devise supportée

## Obtenir les identifiants CinetPay

### 1. Inscription
- Allez sur [https://cinetpay.com](https://cinetpay.com)
- Créez un compte marchand
- Complétez la vérification KYC

### 2. Récupération des clés
- **Tableau de bord** → **Développeurs** → **API**
- **API Key** : Clé d'authentification
- **Site ID** : Identifiant unique de votre site

### 3. Configuration Webhook
- **URL de notification** : `https://votre-domaine.com/cinetpay/notify`
- **URL de retour** : `https://votre-domaine.com/{slug}/cinetpay/return`

## Processus de paiement

### 1. Côté client
1. Client sélectionne CinetPay au checkout
2. Redirection vers la page de paiement CinetPay
3. Choix du moyen de paiement (Mobile Money, carte, etc.)
4. Saisie des informations de paiement
5. Confirmation du paiement

### 2. Côté serveur
1. Création de la commande avec statut "En attente"
2. Initialisation du paiement CinetPay
3. Réception du webhook de confirmation
4. Mise à jour du statut de paiement
5. Envoi de la confirmation par email/WhatsApp

## Moyens de paiement supportés

### Mobile Money
- 🇸🇳 Orange Money (Sénégal)
- 🇨🇮 MTN Money (Côte d'Ivoire) 
- 🇧🇫 Moov Money (Burkina Faso)
- 🇹🇬 T-Money (Togo)
- 🇧🇯 Flooz (Bénin)
- 🇲🇱 Wave (Mali)

### Cartes bancaires
- Visa
- MasterCard
- Cartes locales (UBA, Ecobank, etc.)

### Autres
- Virements bancaires
- Paiements en espèces (agents)

## Devises supportées

| Code | Devise | Pays |
|------|--------|------|
| XOF | Franc CFA Ouest | Sénégal, Côte d'Ivoire, Mali, etc. |
| XAF | Franc CFA Central | Cameroun, Gabon, etc. |
| CDF | Franc Congolais | RD Congo |
| GNF | Franc Guinéen | Guinée |
| USD | Dollar US | International |
| EUR | Euro | International |

## Sécurité

- ✅ Chiffrement SSL/TLS
- ✅ Validation des webhooks
- ✅ Vérification des IP sources
- ✅ Tokens de sécurité uniques
- ✅ Conformité PCI DSS

## Frais CinetPay

Les frais varient selon le pays et le moyen de paiement :

- **Mobile Money** : 1-3% + frais fixes
- **Cartes bancaires** : 2-4% + frais fixes  
- **Virements** : Frais fixes selon le montant

Consultez [la grille tarifaire CinetPay](https://cinetpay.com/tarifs) pour les détails.

## Support et contact

### CinetPay
- **Support** : support@cinetpay.com
- **Téléphone** : +225 07 59 42 42 42
- **Documentation** : [https://docs.cinetpay.com](https://docs.cinetpay.com)

### RestroSaaS
- Pour les questions d'intégration technique
- Consultez la documentation complète du système

## Résolution de problèmes

### Paiement échoué
1. Vérifiez les identifiants API
2. Contrôlez la configuration des webhooks
3. Examinez les logs dans `storage/logs/`

### Webhook non reçu
1. Vérifiez l'URL de notification dans CinetPay
2. Testez la connectivité réseau
3. Vérifiez les paramètres du pare-feu

### Environnement Sandbox
- Utilisez les identifiants de test CinetPay
- Les paiements ne sont pas réels
- Parfait pour les tests d'intégration

---

**CinetPay** est maintenant le moyen de paiement par défaut prioritaire de RestroSaaS, offrant une expérience de paiement optimale pour l'Afrique francophone ! 🚀
# 💳 Guide Configuration CinetPay - E-menu

## 🎯 Objectif
Configurez CinetPay pour accepter les paiements mobiles africains (Orange Money, MTN Money, Moov Money) dans votre restaurant E-menu.

---

## 📋 Prérequis

### 🔑 **Compte CinetPay Requis**
1. **Créer un compte** sur [cinetpay.com](https://cinetpay.com)
2. **Vérifier votre identité** (pièce d'identité + justificatifs)
3. **Obtenir vos identifiants** :
   - Site ID (exemple: 123456)
   - API Key (exemple: 12345678901234567890123456789012)

### 💰 **Frais CinetPay**
```
Orange Money : 1,5% + 0 FCFA
MTN Money   : 1,5% + 0 FCFA  
Moov Money  : 1,5% + 0 FCFA
Visa/Master : 3,5% + 0 FCFA
```

---

## ⚙️ Configuration dans E-menu

### 1️⃣ **Accéder à la Configuration**
```
1. Connectez-vous à l'admin : http://votre-site.com/admin
2. Menu → Paramètres → Méthodes de Paiement
3. Cliquez sur "CinetPay Configuration"
```

### 2️⃣ **Saisir vos Identifiants**
```
Site ID     : [Votre Site ID CinetPay]
API Key     : [Votre API Key CinetPay]
Secret Key  : [Votre Secret Key CinetPay]
Mode        : Live (pour production)
Statut      : Actif ✅
Position    : 1 (méthode prioritaire)
```

### 3️⃣ **Configuration des URLs**
```
URL de Retour    : Automatique (géré par E-menu)
URL de Notification : Automatique (géré par E-menu)
URL d'Annulation   : Automatique (géré par E-menu)
```

---

## 🧪 Test de Configuration

### 🔍 **Mode Sandbox (Test)**
Pour tester avant production :
```
Mode : Sandbox
Site ID Test : 5865 (fourni par CinetPay)
API Key Test : 12912847092917927423 (fourni par CinetPay)
```

### ✅ **Test Complet**
1. **Créer une commande test** de 100 FCFA
2. **Sélectionner CinetPay** comme moyen de paiement
3. **Simuler paiement** Orange Money avec :
   - Numéro : +225 0789999999
   - Code : 0000
4. **Vérifier** que la commande est confirmée

---

## 📱 Moyens de Paiement Disponibles

### 🟠 **Orange Money**
```
Pays supportés : 
• Côte d'Ivoire (+225)
• Mali (+223)
• Burkina Faso (+226)
• Niger (+227)
• Sénégal (+221)
```

### 🔵 **MTN Money**
```
Pays supportés :
• Côte d'Ivoire (+225) 
• Cameroun (+237)
• Ghana (+233)
• Ouganda (+256)
```

### 🟡 **Moov Money**
```
Pays supportés :
• Côte d'Ivoire (+225)
• Togo (+228)
• Bénin (+229)
```

### 💳 **Cartes Bancaires**
```
Types acceptés :
• Visa
• Mastercard
• Cartes locales (selon pays)
```

---

## 🔄 Processus de Paiement

### 👤 **Côté Client**
```
1. Client scanne QR code → Menu s'affiche
2. Sélectionne plats → Ajoute au panier
3. Valide commande → Choisit "Payer par Mobile Money"
4. Sélectionne CinetPay → Choisit Orange/MTN/Moov
5. Saisit numéro → Confirme sur son téléphone
6. Paiement validé → Reçoit confirmation SMS
```

### 🏪 **Côté Restaurant**
```
1. Notification WhatsApp instantanée
2. Commande apparaît dans admin
3. Statut "Payé" automatiquement mis à jour
4. Préparation peut commencer
5. Argent transféré sur compte CinetPay
```

---

## 💸 Gestion des Encaissements

### 📊 **Suivi des Paiements**
Dans votre admin E-menu :
```
Tableau de Bord → Transactions
• Voir tous les paiements CinetPay
• Statut en temps réel
• Montants et commissions
• Rapports quotidiens/mensuels
```

### 🏦 **Retrait des Fonds**
```
1. Connectez-vous sur cinetpay.com
2. Onglet "Retraits"
3. Demandez virement bancaire
4. Fonds transférés sous 24-48h
```

### 📈 **Frais de Transaction**
```
Exemple sur vente de 10 000 FCFA :
• Commission CinetPay : 150 FCFA (1,5%)
• Vous recevez : 9 850 FCFA
```

---

## ⚠️ Résolution de Problèmes

### 🔴 **Paiement Échoue**
```
Causes possibles :
• Solde insuffisant client
• Numéro de téléphone incorrect
• Réseau opérateur défaillant
• Configuration CinetPay incorrecte

Solutions :
• Demander au client de vérifier son solde
• Vérifier la saisie du numéro
• Réessayer dans quelques minutes
• Contacter support CinetPay
```

### 🟡 **Paiement en Attente**
```
Statut "Pending" :
• Normal pour paiements mobile money
• Attendre confirmation opérateur (1-3 min)
• Si dépasse 10 min → Contacter CinetPay
```

### 🔴 **Configuration Incorrecte**
```
Erreur "Site ID invalide" :
• Vérifier Site ID CinetPay
• Vérifier API Key
• Confirmer mode Live/Sandbox
• Tester avec identifiants sandbox
```

---

## 📞 Support CinetPay

### 🆘 **Contact Direct**
```
Email : support@cinetpay.com
Téléphone : +225 27 XX XX XX XX
WhatsApp : +225 05 XX XX XX XX
Chat : cinetpay.com (bouton chat)
```

### 📚 **Documentation**
```
API CinetPay : docs.cinetpay.com
FAQ : cinetpay.com/faq
Statut service : status.cinetpay.com
```

---

## ✅ Checklist Finale

```
□ Compte CinetPay créé et vérifié
□ Site ID et API Key obtenus
□ Configuration testée en mode Sandbox
□ Configuration activée en mode Live
□ Test de paiement réel effectué (petit montant)
□ Notification WhatsApp fonctionnelle
□ Staff formé sur le processus
□ Procédure de résolution de problèmes comprise
```

---

## 🎉 Félicitations !

Votre restaurant E-menu accepte maintenant les paiements mobiles !

**Avantages immédiats :**
- ✅ Plus besoin de monnaie
- ✅ Paiements instantanés et sécurisés
- ✅ Traçabilité complète des transactions
- ✅ Augmentation du panier moyen
- ✅ Satisfaction client améliorée

**🚀 Votre restaurant est maintenant à la pointe de la technologie de paiement africaine !**

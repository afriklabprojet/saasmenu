# 🛠️ Guide de Dépannage E-menu - Restaurants

## ⚡ Problèmes Urgents et Solutions

### 🔴 **URGENCE : Restaurant ne fonctionne plus**

#### 🌐 **Site Web Inaccessible**
```
Vérifications immédiates :
1. Internet fonctionne ? → Tester google.com
2. Serveur allumé ? → Vérifier électricité
3. Domaine expiré ? → Contacter hébergeur

Solution rapide :
• Redémarrer la box internet
• Attendre 5 minutes
• Tester à nouveau
```

#### 📱 **QR Codes ne fonctionnent plus**
```
Test rapide :
1. Scanner QR code avec votre téléphone
2. Menu s'affiche ? OUI → Problème résolu
3. Menu ne s'affiche pas ? → Voir solutions ci-dessous

Solutions :
• Nettoyer le QR code (traces, taches)
• Vérifier l'éclairage de la table
• Tester avec un autre téléphone
• Réimprimer le QR code si nécessaire
```

---

## 💳 Problèmes de Paiement

### 🟠 **Orange Money ne fonctionne pas**
```
Symptômes :
• Client saisit numéro → Erreur "Transaction échouée"
• Message "Opérateur non disponible"

Solutions :
1. Vérifier solde du client (minimum 100 FCFA)
2. Confirmer numéro saisi (format +225XXXXXXXX)
3. Demander au client de réessayer
4. Proposer un autre opérateur (MTN, Moov)
5. Si problème persiste → Paiement cash temporaire
```

### 🔵 **MTN Money problème**
```
Erreurs courantes :
• "PIN incorrect" → Client doit vérifier son code
• "Service temporairement indisponible" → Réessayer
• "Limite journalière atteinte" → Utiliser autre méthode

Solution rapide :
• Attendre 2-3 minutes
• Réessayer la transaction
• Tester avec Orange Money
```

### 💳 **Carte Bancaire refusée**
```
Causes fréquentes :
• Carte expirée
• Plafond dépassé
• Code incorrect (3 tentatives)
• Banque bloque la transaction

Solution :
• Demander autre carte
• Proposer paiement mobile money
• Accepter cash si nécessaire
```

---

## 📱 Problèmes Client Mobile

### 🔍 **Menu ne s'affiche pas**
```
Diagnostic :
1. Le QR code fonctionne-t-il ?
2. Le client a-t-il internet ?
3. La page charge-t-elle lentement ?

Solutions :
• Demander au client de se connecter au WiFi restaurant
• Proposer d'utiliser un autre téléphone
• Montrer le menu physique en attendant
• Prendre commande manuellement
```

### 🐌 **Menu très lent à charger**
```
Causes :
• Connexion internet lente
• Photos trop lourdes sur le site
• Serveur surchargé

Solutions immédiates :
• Optimiser images dans l'admin (réduire taille)
• Upgrader forfait internet
• Contacter support technique
```

### 🔄 **Commande ne se valide pas**
```
Problème : Client clique "Valider" → Rien ne se passe

Vérifications :
1. Panier contient au moins 1 article ?
2. Informations client remplies ?
3. Méthode de paiement sélectionnée ?

Solution temporaire :
• Prendre commande par téléphone
• Saisir manuellement dans l'admin
• Régler le problème technique plus tard
```

---

## 🏪 Problèmes Administration

### 🔐 **Impossible de se connecter à l'admin**
```
Erreur "Mot de passe incorrect" :
1. Vérifier clavier (majuscules/minuscules)
2. Essayer navigateur différent
3. Vider cache navigateur
4. Utiliser "Mot de passe oublié"

Si problème persiste :
• Contacter support technique
• Accès temporaire peut être créé
```

### 📸 **Impossible d'uploader photos**
```
Erreurs courantes :
• "Fichier trop volumineux" → Réduire taille (max 2MB)
• "Format non supporté" → Utiliser JPG ou PNG
• "Erreur upload" → Vérifier connexion internet

Solution :
1. Redimensionner image (max 800x600 pixels)
2. Compresser avec outil en ligne
3. Tenter upload à nouveau
```

### 🍽️ **Plat n'apparaît pas sur le menu**
```
Vérifications :
□ Plat marqué "Disponible" ?
□ Catégorie activée ?
□ Photo uploadée ?
□ Prix renseigné ?

Actions :
1. Aller dans Admin → Produits
2. Éditer le plat concerné
3. Vérifier tous les champs
4. Sauvegarder
5. Tester avec QR code
```

---

## 🔔 Problèmes Notifications

### 📱 **Pas de notification WhatsApp**
```
Diagnostic :
1. Numéro WhatsApp configuré dans admin ?
2. WhatsApp Business installé ?
3. Connexion internet stable ?

Configuration :
• Admin → Paramètres → Notifications
• Saisir numéro WhatsApp (+225XXXXXXXX)
• Tester avec commande factice
```

### 📧 **Emails non reçus**
```
Problèmes courants :
• Emails dans spam/courrier indésirable
• Adresse email incorrecte
• Serveur email mal configuré

Solutions :
1. Vérifier dossier spam
2. Ajouter noreply@votre-site.com aux contacts
3. Utiliser WhatsApp plutôt qu'email
```

---

## 🕒 Problèmes Horaires

### ⏰ **Restaurant fermé mais commandes arrivent**
```
Cause : Horaires mal configurés

Solution :
1. Admin → Paramètres → Horaires
2. Vérifier heures ouverture/fermeture
3. Configurer jours de fermeture
4. Activer "Fermeture temporaire" si besoin
```

### 📅 **Mauvais jour affiché**
```
Problème fuseau horaire :
• Vérifier paramètres serveur
• Contacter support technique
• Ajuster dans Admin → Paramètres → Général
```

---

## 📊 Problèmes de Stock

### ❌ **Plat affiché "Non disponible"**
```
Vérifications :
1. Stock activé pour ce plat ?
2. Quantité > 0 ?
3. Rupture de stock activée ?

Actions rapides :
• Admin → Produits → Modifier stock
• Désactiver gestion stock si pas utilisée
• Marquer "Toujours disponible"
```

### 🔄 **Stock ne se met pas à jour**
```
Problème synchronisation :
• Actualiser page admin (F5)
• Vérifier dernière commande
• Réinitialiser stock si nécessaire
```

---

## 🆘 Support d'Urgence

### 📞 **Contacts Immédiats**
```
🔴 URGENCE 24/7 :
WhatsApp : +225 07 XX XX XX XX
Téléphone : +225 27 XX XX XX XX

🟡 Support Normal :
Email : support@e-menu.ci
Chat : Sur votre admin (bouton aide)

🟢 Communauté :
Groupe WhatsApp restaurateurs
Forum : forum.e-menu.ci
```

### 📋 **Informations à Préparer**
Avant de contacter le support :
```
• URL de votre restaurant
• Description précise du problème
• Heure d'apparition du problème
• Étapes pour reproduire l'erreur
• Captures d'écran si possible
```

---

## 💡 Conseils Préventifs

### 🔒 **Sauvegardes Régulières**
```
Hebdomadaire :
□ Sauvegarder menu (Export Excel)
□ Sauvegarder photos importantes
□ Noter paramètres CinetPay
□ Sauvegarder contacts clients
```

### 🔄 **Maintenance Préventive**
```
Quotidienne :
• Tester 1 QR code au hasard
• Vérifier connexion internet
• Nettoyer cache navigateur

Hebdomadaire :
• Vérifier toutes les photos
• Tester processus de commande complet
• Nettoyer QR codes des tables
• Mettre à jour prix si nécessaire
```

### 📱 **Plan de Secours**
```
En cas de panne totale :
1. Menu physique de secours prêt
2. Carnet pour noter commandes manuellement
3. Calculator pour calculer totaux
4. Numéros support toujours accessibles
5. Mode paiement cash temporaire
```

---

## ✅ Checklist de Résolution

Avant de paniquer, vérifiez toujours :
```
□ Internet fonctionne ?
□ Électricité stable ?
□ Téléphone du client fonctionne ?
□ Problème sur 1 seule table ou toutes ?
□ Problème depuis quand ?
□ Changement récent effectué ?
□ Autres restaurants E-menu touchés ?
□ Support déjà contacté ?
```

---

## 🎯 Résolution par Priorité

### 🔴 **CRITIQUE** (Résoudre en 5 min)
- Restaurant inaccessible
- Aucun paiement ne fonctionne
- Toutes les commandes échouent

### 🟡 **IMPORTANT** (Résoudre en 30 min)
- Un QR code ne fonctionne pas
- Une méthode de paiement défaillante
- Notifications non reçues

### 🟢 **NORMAL** (Résoudre dans la journée)
- Photo ne s'affiche pas
- Menu lent à charger
- Email dans spam

---

**🛠️ La plupart des problèmes ont des solutions simples !**

*En cas de doute, contactez le support - nous sommes là pour vous aider !*

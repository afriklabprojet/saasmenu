# 🔧 MODE DÉVELOPPEMENT LOCAL - E-MENU WHATSAPP SAAS

## ✅ Configuration Locale Activée

### Modifications Appliquées

```env
APP_ENV=local                    (était: production)
APP_DEBUG=true                   (était: false)
APP_URL=http://localhost:8000    (était: https://...)

FORCE_HTTPS=false                (était: true)
SESSION_SECURE_COOKIE=false      (était: true)
SANCTUM_STATEFUL_DOMAINS=localhost
```

### État du Serveur

```
✅ SSL/HTTPS désactivé
✅ Mode debug activé
✅ Environnement: local
✅ Serveur: http://127.0.0.1:8000
```

## 🌐 Accès à l'Application

### URLs Locales

```
Application principale:
→ http://localhost:8000

Administration:
→ http://localhost:8000/admin

Login Admin:
→ http://localhost:8000/login
   Email: admin@emenu.com
   Pass: admin123

API:
→ http://localhost:8000/api
```

## 🔄 Commandes Utiles

### Démarrer le Serveur
```bash
cd restro-saas
php artisan serve
# Serveur: http://127.0.0.1:8000
```

### Arrêter le Serveur
```
Ctrl + C dans le terminal
```

### Reconfigurer
```bash
# Si vous modifiez le .env
php artisan config:clear
php artisan config:cache

# Redémarrer le serveur
php artisan serve
```

### Logs en Temps Réel
```bash
tail -f storage/logs/laravel.log
```

## 🔒 Retour en Mode Production

Quand vous déployez sur le serveur de production, **réactivez la sécurité** :

```bash
# Modifier .env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com
FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true

# Appliquer
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## ⚠️ IMPORTANT

```
❌ NE JAMAIS déployer en production avec:
   - APP_ENV=local
   - APP_DEBUG=true
   - FORCE_HTTPS=false

✅ En production, toujours utiliser:
   - APP_ENV=production
   - APP_DEBUG=false
   - FORCE_HTTPS=true
   - Certificat SSL valide
```

## 🧪 Tests en Local

### Tester l'Administration
```bash
# Ouvrir dans le navigateur
http://localhost:8000/admin

# Identifiants
Email: admin@emenu.com
Password: admin123
```

### Tester l'API
```bash
# Test simple
curl http://localhost:8000/api/health

# Test avec authentification
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@emenu.com","password":"admin123"}'
```

### Tester les Paiements (Sandbox)
```bash
# Mode sandbox CinetPay activé en local
# Utilisez les numéros de test dans CINETPAY_CONFIGURATION.md
```

## 🐛 Résolution de Problèmes

### Erreur "Unsupported SSL request"
```
✅ RÉSOLU: SSL désactivé, utilisez http:// (pas https://)
```

### Erreur 500
```bash
# Vérifier les logs
tail -f storage/logs/laravel.log

# Clear tous les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Base de données non accessible
```bash
# Vérifier la connexion
php artisan db:show

# Relancer les migrations si besoin
php artisan migrate:fresh --seed
```

### Permissions fichiers
```bash
# Donner les permissions
chmod -R 775 storage bootstrap/cache
```

## 📊 Mode Debug

En mode local, vous avez accès à :

```
✅ Messages d'erreur détaillés
✅ Stack traces complètes
✅ Debugbar Laravel (si installé)
✅ Logs temps réel
✅ Query logs MySQL
```

## 🔐 Sécurité Locale

Même en local, suivez les bonnes pratiques :

```
✅ .env dans .gitignore (ne jamais commit)
✅ Mots de passe forts même en local
✅ Pas de vraies données client en local
✅ Utiliser sandbox pour paiements
```

---

## 📞 Support

Si vous rencontrez des problèmes :

1. Vérifier les logs: `storage/logs/laravel.log`
2. Tester: `php artisan about`
3. Clear cache: `php artisan config:clear`
4. Redémarrer serveur: `Ctrl+C` puis `php artisan serve`

---

**🚀 Serveur Local: http://localhost:8000**

*Mode: Développement Local*  
*Date: 23 octobre 2025*  
*E-menu WhatsApp SaaS*

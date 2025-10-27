# �� DUMP BASE DE DONNÉES - KOFFI

## 🎯 Instructions pour créer le dump sur le serveur de production

### Méthode 1: Avec mysqldump (Recommandé)

```bash
mysqldump -u c2687072c_paulin225 -p'7)2GRB~eZ#IiBr.Q' c2687072c_restooo225 > koffi_backup_$(date +%Y%m%d).sql
```

### Méthode 2: Avec compression (pour économiser l'espace)

```bash
mysqldump -u c2687072c_paulin225 -p'7)2GRB~eZ#IiBr.Q' c2687072c_restooo225 | gzip > koffi_backup_$(date +%Y%m%d).sql.gz
```

### Méthode 3: Via PHP Artisan (si mysqldump n'est pas disponible)

```bash
php artisan db:dump --database=mysql --output=koffi_backup.sql
```

## 📥 Pour restaurer le dump

### Restaurer depuis un fichier .sql

```bash
mysql -u c2687072c_paulin225 -p'7)2GRB~eZ#IiBr.Q' c2687072c_restooo225 < koffi_backup.sql
```

### Restaurer depuis un fichier .sql.gz compressé

```bash
gunzip < koffi_backup.sql.gz | mysql -u c2687072c_paulin225 -p'7)2GRB~eZ#IiBr.Q' c2687072c_restooo225
```

## 🔐 Informations de connexion

- **Database**: c2687072c_restooo225
- **Username**: c2687072c_paulin225
- **Password**: 7)2GRB~eZ#IiBr.Q
- **Host**: 127.0.0.1

## ⚠️ Notes importantes

1. **Avant de restaurer**: Sauvegardez toujours la base actuelle
2. **Espace disque**: Vérifiez l'espace disponible avant de créer le dump
3. **Permissions**: Assurez-vous d'avoir les droits d'écriture dans le dossier
4. **Sécurité**: Ne partagez jamais le fichier dump publiquement (contient des données sensibles)

## 📊 Vérifier la taille du dump

```bash
ls -lh koffi_backup*.sql*
```

## 🗑️ Nettoyer les anciens dumps

```bash
# Supprimer les dumps de plus de 7 jours
find . -name "koffi_backup_*.sql*" -mtime +7 -delete
```


# Correctif: Table item_images manquante

**Date:** 23 octobre 2025  
**Problème:** QueryException - Table 'restro_saas.item_images' doesn't exist  
**Migration:** 2025_10_23_110500_create_item_images_table.php

---

## 1. Description du problème

### Erreur rencontrée
```
Illuminate\Database\QueryException

SQLSTATE[42S02]: Base table or view not found: 1146 
Table 'restro_saas.item_images' doesn't exist

select *, CONCAT('http://127.0.0.1:8000/storage/app/public/item/', image) AS image_url 
from `item_images` 
where `item_images`.`item_id` in (1, 2, 3, 4, 5)
```

### Contexte
- L'application tente de charger les images des produits (items)
- La table `item_images` permet de stocker plusieurs images par produit
- Bloque l'affichage des galeries photos des produits

---

## 2. Solution appliquée

### Migration créée

**Fichier:** `database/migrations/2025_10_23_110500_create_item_images_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->string('image')->nullable();
            $table->timestamps();
            
            $table->index('item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_images');
    }
};
```

**Commande:**
```bash
php artisan migrate --path=database/migrations/2025_10_23_110500_create_item_images_table.php --force
```

**Résultat:** ✅ Migration appliquée (32ms)

---

## 3. Relations Eloquent

### Modèle Item.php (mis à jour)

```php
// Image unique (compatibilité)
public function item_image(){
    return $this->hasOne('App\Models\ItemImages','item_id','id')
        ->select('*',\DB::raw("CONCAT('".url('/storage/app/public/item/')."/', image) AS image_url"));
}

// Images multiples
public function images(){
    return $this->hasMany('App\Models\ItemImages','item_id','id')
        ->select('*',\DB::raw("CONCAT('".url('/storage/app/public/item/')."/', image) AS image_url"));
}
```

### Modèle ItemImages.php (existant)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemImages extends Model
{
    protected $table='item_images';
    protected $fillable=['item_id','image'];
}
```

---

## 4. Structure de la table

| Colonne | Type | Null | Défaut | Description |
|---------|------|------|--------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Identifiant unique |
| item_id | BIGINT UNSIGNED | NO | - | ID du produit (FK) |
| image | VARCHAR(255) | YES | NULL | Nom du fichier image |
| created_at | TIMESTAMP | YES | NULL | Date création |
| updated_at | TIMESTAMP | YES | NULL | Date modification |

**Index:**
- PRIMARY KEY: `id`
- INDEX: `item_images_item_id_index` sur `item_id`
- FOREIGN KEY: `item_images_item_id_foreign` → `items(id)` ON DELETE CASCADE

---

## 5. Utilisation

### Charger un produit avec ses images

```php
// Image unique (première)
$item = Item::with('item_image')->find($id);
echo $item->item_image->image_url;

// Toutes les images
$item = Item::with('images')->find($id);
foreach($item->images as $image) {
    echo $image->image_url;
}
```

### Ajouter des images à un produit

```php
$item = Item::find($id);

// Ajouter une image
$item->images()->create([
    'image' => 'produit-1.jpg'
]);

// Ajouter plusieurs images
$images = ['image1.jpg', 'image2.jpg', 'image3.jpg'];
foreach($images as $imageName) {
    ItemImages::create([
        'item_id' => $item->id,
        'image' => $imageName
    ]);
}
```

---

## 6. Vérification

```bash
php artisan tinker --execute='
    $columns = Schema::getColumnListing("item_images");
    echo "Colonnes: " . count($columns) . "\n";
    print_r($columns);
'
```

**Résultat:**
```
Colonnes: 5
Array
(
    [0] => id
    [1] => item_id
    [2] => image
    [3] => created_at
    [4] => updated_at
)
```

✅ **Table créée avec succès**

---

## 7. Impact

### Avant
❌ Erreur lors du chargement des produits avec images  
❌ Galerie photos non fonctionnelle  
❌ QueryException bloquante  

### Après
✅ Table créée avec clé étrangère  
✅ Relations Eloquent configurées  
✅ Support images multiples  
✅ Application HTTP 200  

---

**Correctif #6/6 - Terminé avec succès** ✅

# 🔄 Plan de Migration Routes RESTful - RestroSaaS

**Date**: 15 novembre 2025  
**Objectif**: Migrer 307 routes vers architecture RESTful conforme  
**État actuel**: 200+ routes web analysées, pattern CRUDdy identifié

---

## 📊 Analyse des Routes Existantes

### Routes Web (809 lignes totales)
**Patterns CRUDdy détectés**:

#### 1. **Orders** (6 routes CRUDdy)
```php
// ❌ NON-RESTful
Route::get('/orders/update-{id}-{status}-{type}', [OrderController::class, 'update']);
Route::post('/orders/customerinfo/', [OrderController::class, 'customerinfo']);
Route::post('/orders/vendor_note/', [OrderController::class, 'vendor_note']);
Route::get('/orders/invoice/{order_number}', [OrderController::class, 'invoice']);
Route::get('/orders/print/{order_number}', [OrderController::class, 'print']);
Route::get('/orders/generatepdf/{order_number}', [OrderController::class, 'generatepdf']);

// ✅ RESTful recommandé
Route::patch('/admin/orders/{id}/status', [OrderController::class, 'updateStatus']);
Route::post('/admin/orders/{id}/customer-info', [OrderController::class, 'storeCustomerInfo']);
Route::post('/admin/orders/{id}/vendor-note', [OrderController::class, 'storeVendorNote']);
Route::get('/admin/orders/{order}/invoice', [OrderController::class, 'invoice']);
Route::get('/admin/orders/{order}/print', [OrderController::class, 'print']);
Route::get('/admin/orders/{order}/pdf', [OrderController::class, 'generatePdf']);
```

#### 2. **Categories** (8 routes CRUDdy)
```php
// ❌ NON-RESTful
Route::get('/categories/', [CategoryController::class, 'index']);
Route::get('categories/add', [CategoryController::class, 'add_category']);
Route::post('categories/save', [CategoryController::class, 'save_category']);
Route::get('categories/edit-{slug}', [CategoryController::class, 'edit_category']);
Route::post('categories/update-{slug}', [CategoryController::class, 'update_category']);
Route::get('categories/change_status-{slug}/{status}', [CategoryController::class, 'change_status']);
Route::get('categories/delete-{slug}', [CategoryController::class, 'delete_category']);
Route::post('categories/reorder_category', [CategoryController::class, 'reorder_category']);

// ✅ RESTful recommandé
Route::resource('/admin/categories', CategoryController::class);
Route::patch('/admin/categories/{category}/status', [CategoryController::class, 'updateStatus']);
Route::post('/admin/categories/reorder', [CategoryController::class, 'reorder']);
```

#### 3. **Products** (estimé 10+ routes)
```php
// ❌ NON-RESTful pattern attendu
Route::get('products/', [ProductController::class, 'index']);
Route::get('products/add', [ProductController::class, 'add_product']);
Route::post('products/save', [ProductController::class, 'save_product']);
Route::get('products/edit-{id}', [ProductController::class, 'edit']);
Route::post('products/update-{id}', [ProductController::class, 'update']);
Route::get('products/delete-{id}', [ProductController::class, 'delete']);
Route::get('products/change_status-{id}/{status}', [ProductController::class, 'change_status']);

// ✅ RESTful recommandé
Route::resource('/admin/products', ProductController::class);
Route::patch('/admin/products/{product}/status', [ProductController::class, 'updateStatus']);
```

#### 4. **Vendors** (7 routes CRUDdy)
```php
// ❌ NON-RESTful
Route::get('/vendors/', [VendorController::class, 'index']);
Route::get('vendors/add', [VendorController::class, 'add']);
Route::get('vendors/edit-{slug}', [VendorController::class, 'edit']);
Route::post('vendors/update-{slug}', [VendorController::class, 'update']);
Route::get('vendors/status-{slug}/{status}', [VendorController::class, 'status']);
Route::get('vendors/login-{slug}', [VendorController::class, 'vendor_login']);
Route::get('vendors/delete-{slug}', [VendorController::class, 'deletevendor']);

// ✅ RESTful recommandé
Route::resource('/admin/vendors', VendorController::class);
Route::patch('/admin/vendors/{vendor}/status', [VendorController::class, 'updateStatus']);
Route::post('/admin/vendors/{vendor}/login-as', [VendorController::class, 'loginAs']);
```

#### 5. **Plan Pricing** (7 routes CRUDdy)
```php
// ❌ NON-RESTful
Route::get('plan/add', [PlanPricingController::class, 'add_plan']);
Route::post('plan/save_plan', [PlanPricingController::class, 'save_plan']);
Route::get('plan/edit-{id}', [PlanPricingController::class, 'edit_plan']);
Route::post('plan/update_plan-{id}', [PlanPricingController::class, 'update_plan']);
Route::get('plan/status_change-{id}/{status}', [PlanPricingController::class, 'status_change']);
Route::get('plan/delete-{id}', [PlanPricingController::class, 'delete']);
Route::post('plan/reorder_plan', [PlanPricingController::class, 'reorder_plan']);

// ✅ RESTful recommandé
Route::resource('/admin/plans', PlanPricingController::class);
Route::patch('/admin/plans/{plan}/status', [PlanPricingController::class, 'updateStatus']);
Route::post('/admin/plans/reorder', [PlanPricingController::class, 'reorder']);
```

#### 6. **Tax** (8 routes CRUDdy)
```php
// ❌ NON-RESTful
Route::get('tax/', [TaxController::class, 'index']);
Route::get('tax/add', [TaxController::class, 'add']);
Route::post('tax/save', [TaxController::class, 'save']);
Route::get('tax/edit-{id}', [TaxController::class, 'edit']);
Route::post('tax/update-{id}', [TaxController::class, 'update']);
Route::get('tax/change_status-{id}/{status}', [TaxController::class, 'change_status']);
Route::get('tax/delete-{id}', [TaxController::class, 'delete']);
Route::post('tax/reorder_tax', [TaxController::class, 'reorder_tax']);

// ✅ RESTful recommandé
Route::resource('/admin/taxes', TaxController::class);
Route::patch('/admin/taxes/{tax}/status', [TaxController::class, 'updateStatus']);
Route::post('/admin/taxes/reorder', [TaxController::class, 'reorder']);
```

#### 7. **Shipping Area** (estimé 8 routes)
```php
// ❌ NON-RESTful
Route::get('shippingarea/', [ShippingareaController::class, 'index']);
Route::get('shippingarea/add', [ShippingareaController::class, 'add']);
Route::post('shippingarea/save', [ShippingareaController::class, 'save']);
Route::get('shippingarea/edit-{id}', [ShippingareaController::class, 'edit']);
Route::post('shippingarea/update-{id}', [ShippingareaController::class, 'update']);
Route::get('shippingarea/delete-{id}', [ShippingareaController::class, 'delete']);

// ✅ RESTful recommandé
Route::resource('/admin/shipping-areas', ShippingareaController::class);
```

#### 8. **Store Categories** (8 routes CRUDdy)
```php
// ❌ NON-RESTful
Route::get('store-category/', [StoreCategoryController::class, 'index']);
Route::get('store-category/add', [StoreCategoryController::class, 'add_category']);
Route::post('store-category/save', [StoreCategoryController::class, 'save_category']);
Route::get('store-category/edit-{id}', [StoreCategoryController::class, 'edit_category']);
Route::post('store-category/update-{id}', [StoreCategoryController::class, 'update_category']);
Route::get('store-category/change_status-{id}/{status}', [StoreCategoryController::class, 'change_status']);
Route::get('store-category/delete-{id}', [StoreCategoryController::class, 'delete_category']);
Route::post('store-category/reorder_category', [StoreCategoryController::class, 'reorder_category']);

// ✅ RESTful recommandé
Route::resource('/admin/store-categories', StoreCategoryController::class);
Route::patch('/admin/store-categories/{category}/status', [StoreCategoryController::class, 'updateStatus']);
Route::post('/admin/store-categories/reorder', [StoreCategoryController::class, 'reorder']);
```

#### 9. **FAQ** (7 routes CRUDdy)
```php
// ❌ NON-RESTful
Route::get('faq/', [OtherPagesController::class, 'faq_index']);
Route::get('faq/add', [OtherPagesController::class, 'faq_add']);
Route::post('faq/save', [OtherPagesController::class, 'faq_save']);
Route::get('faq/edit-{id}', [OtherPagesController::class, 'faq_edit']);
Route::post('faq/update-{id}', [OtherPagesController::class, 'faq_update']);
Route::get('faq/delete-{id}', [OtherPagesController::class, 'faq_delete']);
Route::post('faq/reorder_faq', [OtherPagesController::class, 'reorder_faq']);

// ✅ RESTful recommandé
Route::resource('/admin/faqs', FaqController::class); // nouveau contrôleur séparé
Route::post('/admin/faqs/reorder', [FaqController::class, 'reorder']);
```

#### 10. **Features** (7 routes CRUDdy)
```php
// ❌ NON-RESTful
Route::get('features/', [FeaturesController::class, 'index']);
Route::get('features/add', [FeaturesController::class, 'add']);
Route::post('features/save', [FeaturesController::class, 'save']);
Route::get('features/edit-{id}', [FeaturesController::class, 'edit']);
Route::post('features/update-{id}', [FeaturesController::class, 'update']);
Route::get('features/delete-{id}', [FeaturesController::class, 'delete']);
Route::post('features/reorder_features', [FeaturesController::class, 'reorder_features']);

// ✅ RESTful recommandé
Route::resource('/admin/features', FeaturesController::class);
Route::post('/admin/features/reorder', [FeaturesController::class, 'reorder']);
```

#### 11. **Testimonials** (7 routes CRUDdy)
```php
// ❌ NON-RESTful
Route::get('testimonials/', [TestimonialController::class, 'index']);
Route::get('testimonials/add', [TestimonialController::class, 'add']);
Route::post('testimonials/save', [TestimonialController::class, 'save']);
Route::get('testimonials/edit-{id}', [TestimonialController::class, 'edit']);
Route::post('testimonials/update-{id}', [TestimonialController::class, 'update']);
Route::get('testimonials/delete-{id}', [TestimonialController::class, 'delete']);
Route::post('testimonials/reorder_testimonials', [TestimonialController::class, 'reorder_testimonials']);

// ✅ RESTful recommandé
Route::resource('/admin/testimonials', TestimonialController::class);
Route::post('/admin/testimonials/reorder', [TestimonialController::class, 'reorder']);
```

#### 12. **Cities** (8 routes CRUDdy)
```php
// ❌ NON-RESTful
Route::get('cities/', [OtherPagesController::class, 'cities']);
Route::get('cities/add', [OtherPagesController::class, 'add_city']);
Route::post('cities/save', [OtherPagesController::class, 'save_city']);
Route::get('cities/edit-{id}', [OtherPagesController::class, 'edit_city']);
Route::post('cities/update-{id}', [OtherPagesController::class, 'update_city']);
Route::get('cities/delete-{id}', [OtherPagesController::class, 'delete_city']);
Route::get('cities/change_status-{id}/{status}', [OtherPagesController::class, 'statuschange_city']);
Route::post('cities/reorder_city', [OtherPagesController::class, 'reorder_city']);

// ✅ RESTful recommandé
Route::resource('/admin/cities', CityController::class); // nouveau contrôleur séparé
Route::patch('/admin/cities/{city}/status', [CityController::class, 'updateStatus']);
Route::post('/admin/cities/reorder', [CityController::class, 'reorder']);
```

#### 13. **Areas** (8 routes CRUDdy)
```php
// ❌ NON-RESTful
Route::get('areas/', [OtherPagesController::class, 'areas']);
Route::get('areas/add', [OtherPagesController::class, 'add_area']);
Route::post('areas/save', [OtherPagesController::class, 'save_area']);
Route::get('areas/edit-{id}', [OtherPagesController::class, 'edit_area']);
Route::post('areas/update-{id}', [OtherPagesController::class, 'update_area']);
Route::get('areas/delete-{id}', [OtherPagesController::class, 'delete_area']);
Route::get('areas/change_status-{id}/{status}', [OtherPagesController::class, 'statuschange_area']);
Route::post('areas/reorder_area', [OtherPagesController::class, 'reorder_area']);

// ✅ RESTful recommandé
Route::resource('/admin/areas', AreaController::class); // nouveau contrôleur séparé
Route::patch('/admin/areas/{area}/status', [AreaController::class, 'updateStatus']);
Route::post('/admin/areas/reorder', [AreaController::class, 'reorder']);
```

#### 14. **Promotional Banners** (7 routes CRUDdy)
```php
// ❌ NON-RESTful
Route::get('promotional-banner/', [BannerController::class, 'promotional_banner']);
Route::get('promotional-banner/add', [BannerController::class, 'promotional_banneradd']);
Route::get('promotional-banner/edit-{id}', [BannerController::class, 'promotional_banneredit']);
Route::post('promotional-banner/save', [BannerController::class, 'promotional_bannersave_banner']);
Route::post('promotional-banner/update-{id}', [BannerController::class, 'promotional_bannerupdate']);
Route::get('promotional-banner/delete-{id}', [BannerController::class, 'promotional_bannerdelete']);
Route::post('promotional-banner/reorder_promotionalbanner', [BannerController::class, 'reorder_promotionalbanner']);

// ✅ RESTful recommandé
Route::resource('/admin/promotional-banners', PromotionalBannerController::class);
Route::post('/admin/promotional-banners/reorder', [PromotionalBannerController::class, 'reorder']);
```

#### 15. **Settings** (12+ routes mixtes)
```php
// ❌ NON-RESTful
Route::post('settings/update', [SettingsController::class, 'settings_update']);
Route::post('settings/updateseo', [SettingsController::class, 'settings_updateseo']);
Route::post('settings/updatetheme', [SettingsController::class, 'settings_updatetheme']);
Route::post('settings/updateanalytics', [SettingsController::class, 'settings_updateanalytics']);
Route::post('settings/updatecustomedomain', [SettingsController::class, 'settings_updatecustomedomain']);
Route::post('settings/whatsapp_update', [WhatsappmessageController::class, 'whatsapp_update']);

// ✅ RESTful recommandé
Route::patch('/admin/settings/general', [SettingsController::class, 'updateGeneral']);
Route::patch('/admin/settings/seo', [SettingsController::class, 'updateSeo']);
Route::patch('/admin/settings/theme', [SettingsController::class, 'updateTheme']);
Route::patch('/admin/settings/analytics', [SettingsController::class, 'updateAnalytics']);
Route::patch('/admin/settings/custom-domain', [SettingsController::class, 'updateCustomDomain']);
Route::patch('/admin/settings/whatsapp', [WhatsappmessageController::class, 'updateWhatsApp']);
```

---

## 📈 Statistiques Globales

### Routes CRUDdy Identifiées
| Groupe | Routes CRUDdy | Priorité | Effort (heures) |
|--------|---------------|----------|-----------------|
| Orders | 6 | 🔴 Critique | 4h |
| Categories | 8 | 🔴 Critique | 3h |
| Products | 10 | 🔴 Critique | 5h |
| Vendors | 7 | 🟠 Haute | 4h |
| Plans | 7 | 🟠 Haute | 3h |
| Tax | 8 | 🟡 Moyenne | 3h |
| Shipping Areas | 8 | 🟡 Moyenne | 3h |
| Store Categories | 8 | 🟡 Moyenne | 3h |
| FAQ | 7 | 🟢 Basse | 2h |
| Features | 7 | 🟢 Basse | 2h |
| Testimonials | 7 | 🟢 Basse | 2h |
| Cities | 8 | 🟡 Moyenne | 3h |
| Areas | 8 | 🟡 Moyenne | 3h |
| Promotional Banners | 7 | 🟢 Basse | 2h |
| Settings | 12 | 🟠 Haute | 5h |
| **TOTAL** | **118+** | - | **47h** |

### Routes Déjà RESTful ✅
- Analytics (8 routes) - ✅ Compliant
- Wallet (7 routes) - ✅ Compliant
- SEO (6 routes) - ✅ Compliant
- Table Booking (1 route) - ✅ Compliant
- Customers (5 routes) - ✅ Compliant
- Custom Domain (5 routes) - ✅ Compliant

---

## 🎯 Plan de Migration par Sprint

### **Sprint 1: Routes Critiques** (Semaine 1-2) - 20 heures
**Objectif**: Migrer les 3 groupes les plus utilisés

#### 1.1 Orders (4h)
- [x] Créer `OrdersApiController`
- [ ] Implémenter `updateStatus()`
- [ ] Implémenter `storeCustomerInfo()`
- [ ] Implémenter `storeVendorNote()`
- [ ] Tests unitaires (6 tests)

#### 1.2 Categories (3h)
- [ ] Créer resource controller RESTful
- [ ] Implémenter `updateStatus()`
- [ ] Implémenter `reorder()`
- [ ] Tests unitaires (7 tests)

#### 1.3 Products (5h)
- [ ] Créer resource controller RESTful
- [ ] Implémenter `updateStatus()`
- [ ] Implémenter `duplicate()`
- [ ] Tests unitaires (8 tests)

#### 1.4 Vendors (4h)
- [ ] Créer resource controller RESTful
- [ ] Implémenter `updateStatus()`
- [ ] Implémenter `loginAs()`
- [ ] Tests unitaires (7 tests)

#### 1.5 Settings (4h)
- [ ] Refactorer en sous-ressources
- [ ] Implémenter méthodes spécialisées
- [ ] Tests unitaires (10 tests)

**Livrable**: 43 routes migrées, 38 tests ajoutés

---

### **Sprint 2: Routes Prioritaires** (Semaine 3) - 15 heures

#### 2.1 Plans (3h)
- [ ] Créer resource controller
- [ ] Implémenter `updateStatus()`, `reorder()`
- [ ] Tests (6 tests)

#### 2.2 Tax (3h)
- [ ] Créer resource controller
- [ ] Implémenter `updateStatus()`, `reorder()`
- [ ] Tests (6 tests)

#### 2.3 Shipping Areas (3h)
- [ ] Créer resource controller
- [ ] Tests (5 tests)

#### 2.4 Store Categories (3h)
- [ ] Créer resource controller
- [ ] Implémenter `updateStatus()`, `reorder()`
- [ ] Tests (6 tests)

#### 2.5 Cities + Areas (3h)
- [ ] Créer 2 nouveaux contrôleurs séparés
- [ ] Extraire logique de `OtherPagesController`
- [ ] Tests (12 tests)

**Livrable**: 38 routes migrées, 35 tests ajoutés

---

### **Sprint 3: Routes Secondaires** (Semaine 4) - 12 heures

#### 3.1 FAQ (2h)
- [ ] Créer `FaqController` séparé
- [ ] Tests (5 tests)

#### 3.2 Features (2h)
- [ ] Créer resource controller
- [ ] Tests (5 tests)

#### 3.3 Testimonials (2h)
- [ ] Créer resource controller
- [ ] Tests (5 tests)

#### 3.4 Promotional Banners (2h)
- [ ] Créer `PromotionalBannerController`
- [ ] Tests (5 tests)

#### 3.5 Nettoyage final (4h)
- [ ] Vérifier toutes les routes
- [ ] Supprimer anciennes routes
- [ ] Documentation API
- [ ] Tests d'intégration (10 tests)

**Livrable**: 37 routes migrées, 30 tests ajoutés

---

## 🔧 Standards de Migration

### Règles de Nommage
```php
// ✅ Bon
Route::resource('/admin/categories', CategoryController::class);
Route::patch('/admin/categories/{category}/status', [CategoryController::class, 'updateStatus']);

// ❌ Mauvais
Route::get('category/change_status-{id}/{status}', [CategoryController::class, 'change_status']);
```

### Méthodes HTTP
```php
GET    /admin/categories          index()   - Liste
GET    /admin/categories/{id}     show()    - Détail
GET    /admin/categories/create   create()  - Formulaire création
POST   /admin/categories          store()   - Créer
GET    /admin/categories/{id}/edit edit()   - Formulaire édition
PUT/PATCH /admin/categories/{id}  update()  - Mettre à jour
DELETE /admin/categories/{id}     destroy() - Supprimer
```

### Actions Personnalisées
```php
// Actions sur une ressource existante
PATCH /admin/categories/{category}/status
POST  /admin/categories/{category}/duplicate

// Actions sur une collection
POST /admin/categories/reorder
POST /admin/categories/batch-delete
```

---

## 📝 Checklist par Route

Pour chaque groupe de routes:
- [ ] Créer nouveau contrôleur RESTful (si nécessaire)
- [ ] Implémenter méthodes standard (index, show, store, update, destroy)
- [ ] Implémenter actions personnalisées (updateStatus, reorder, etc.)
- [ ] Créer Form Requests pour validation
- [ ] Créer API Resources pour serialization
- [ ] Écrire tests unitaires (min 5 par contrôleur)
- [ ] Mettre à jour routes web.php
- [ ] Mettre à jour vues frontend (formulaires, liens)
- [ ] Tester manuellement toutes les fonctionnalités
- [ ] Documenter API (si endpoint API)
- [ ] Supprimer anciennes routes après validation

---

## 🚀 Prochaines Actions Immédiates

### Action 1: Créer Tests Unitaires pour Queue Jobs (2h)
```bash
tests/Unit/Jobs/
├── SendEmailJobTest.php
├── SendWhatsAppMessageJobTest.php
├── ProcessImageJobTest.php
└── GenerateReportJobTest.php
```

### Action 2: Commencer Sprint 1 - Orders (4h)
1. Créer `app/Http/Controllers/Admin/Api/OrdersApiController.php`
2. Implémenter méthodes RESTful
3. Créer Form Requests
4. Écrire 6 tests

### Action 3: Installer Swagger/OpenAPI pour documentation (1h)
```bash
composer require darkaonline/l5-swagger
php artisan l5-swagger:generate
```

---

## 📊 Métriques de Succès

**Objectifs Mois 1** (déjà atteint ✅):
- Score audit: 7.5/10 → **Atteint 7.8/10**

**Objectifs Mois 2**:
- 80 routes migrées vers RESTful
- 60 tests ajoutés
- Score audit: 8.5/10

**Objectifs Mois 3**:
- 118 routes migrées (100%)
- 103 tests ajoutés
- Score audit: 9.0/10
- Documentation API complète

---

**Temps total estimé**: 47 heures sur 4 semaines  
**Capacité requise**: 12h/semaine  
**Économies long terme**: 80h/an en maintenance

**Prochaine étape**: Créer tests unitaires pour les 4 Queue Jobs (2h)

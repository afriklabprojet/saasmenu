# 📊 SYSTÈME D'ANALYTICS ET RAPPORTS

**Date**: 23 octobre 2025  
**Version**: 1.0  
**Statut**: ✅ **OPÉRATIONNEL**

---

## 🎯 VUE D'ENSEMBLE

Système complet d'analytics pour suivre en temps réel les performances du restaurant.

### ✅ Fonctionnalités Livrées

1. **📈 Chiffre d'Affaires en Temps Réel**
   - CA aujourd'hui, cette semaine, ce mois, cette année
   - Comparaison avec période précédente
   - Variation en pourcentage
   - Panier moyen

2. **🍽️ Plats les Plus Vendus**
   - Top 10/20/50 produits
   - Quantités vendues
   - Chiffre d'affaires par produit
   - Nombre de commandes
   - Prix moyen

3. **⏰ Heures de Pointe**
   - Analyse par heure (24h)
   - Identification des 3 heures les plus chargées
   - Nombre de commandes par heure
   - CA par heure
   - Panier moyen par heure

4. **👥 Analyse Client et Fidélisation**
   - Total de clients uniques
   - Nouveaux clients vs récurrents
   - Taux de rétention
   - Top 10 clients
   - Dépenses moyennes par client

5. **📁 Performance des Catégories**
   - CA par catégorie
   - Quantités vendues
   - Nombre d'articles par catégorie

6. **🔄 Comparaison de Périodes**
   - Comparer deux périodes personnalisées
   - Évolution des métriques clés
   - Variation en %

---

## 🏗️ ARCHITECTURE

### Fichiers Créés

```
app/
  ├── Services/
  │   └── AnalyticsService.php         (520 lignes)
  └── Http/Controllers/admin/
      └── AnalyticsController.php      (200 lignes)
```

### Architecture du Service

```
AnalyticsService
├── getRevenueStats()          → CA et variations
├── getTopSellingItems()       → Top produits
├── getPeakHours()             → Heures de pointe
├── getCustomerAnalytics()     → Stats clients
├── getCategoryPerformance()   → Stats catégories
├── comparePeriods()           → Comparaison périodes
└── getCompleteDashboard()     → Dashboard complet
```

---

## 🚀 UTILISATION

### API REST Endpoints

#### 1. Dashboard Complet

```http
GET /admin/analytics/dashboard?period=today
```

**Périodes disponibles** : `today`, `week`, `month`, `year`

**Réponse** :
```json
{
  "revenue": {
    "current": {
      "revenue": 125000,
      "orders": 45,
      "avg_order": 2777.78
    },
    "previous": {
      "revenue": 98000,
      "orders": 38
    },
    "change": {
      "revenue": 27.55,
      "orders": 18.42
    }
  },
  "top_items": [...],
  "peak_hours": {...},
  "customer_analytics": {...}
}
```

#### 2. Chiffre d'Affaires

```http
GET /admin/analytics/revenue?period=month
```

**Réponse** :
```json
{
  "current": {
    "revenue": 3250000,
    "orders": 892,
    "avg_order": 3644.39
  },
  "previous": {
    "revenue": 2980000,
    "orders": 856
  },
  "change": {
    "revenue": 9.06,
    "orders": 4.21
  },
  "period": "month"
}
```

#### 3. Plats les Plus Vendus

```http
GET /admin/analytics/top-selling?limit=10&period=week
```

**Réponse** :
```json
[
  {
    "item_id": 125,
    "item_name": "Poulet Braisé",
    "total_quantity": 245,
    "total_revenue": 612500,
    "order_count": 180,
    "avg_price": 2500,
    "category": "Grillades"
  },
  ...
]
```

#### 4. Heures de Pointe

```http
GET /admin/analytics/peak-hours?period=week
```

**Réponse** :
```json
{
  "hourly_stats": [
    {
      "hour": 12,
      "hour_label": "12:00",
      "order_count": 85,
      "revenue": 320000,
      "avg_order_value": 3764.71
    },
    ...
  ],
  "peak_hours": [
    {
      "hour": 12,
      "hour_label": "12:00",
      "order_count": 85,
      "revenue": 320000
    },
    {
      "hour": 19,
      "hour_label": "19:00",
      "order_count": 78,
      "revenue": 295000
    },
    {
      "hour": 13,
      "hour_label": "13:00",
      "order_count": 62,
      "revenue": 235000
    }
  ],
  "total_orders": 450,
  "period": "week"
}
```

#### 5. Analytics Clients

```http
GET /admin/analytics/customers?period=month
```

**Réponse** :
```json
{
  "total_customers": 456,
  "new_customers": 128,
  "recurring_customers": 328,
  "retention_rate": 71.93,
  "top_customers": [
    {
      "mobile": "2250709123456",
      "name": "Jean Dupont",
      "order_count": 15,
      "total_spent": 87500,
      "avg_order": 5833.33,
      "last_order": "il y a 2 jours"
    },
    ...
  ],
  "period": "month"
}
```

#### 6. Performance des Catégories

```http
GET /admin/analytics/categories?period=month
```

**Réponse** :
```json
[
  {
    "category_name": "Grillades",
    "items_sold": 520,
    "total_quantity": 845,
    "revenue": 2100000
  },
  {
    "category_name": "Plats Africains",
    "items_sold": 380,
    "total_quantity": 620,
    "revenue": 1550000
  },
  ...
]
```

#### 7. Comparaison de Périodes

```http
GET /admin/analytics/compare
  ?current_start=2025-10-01
  &current_end=2025-10-31
  &previous_start=2025-09-01
  &previous_end=2025-09-30
```

**Réponse** :
```json
{
  "current_period": {
    "start": "2025-10-01",
    "end": "2025-10-31",
    "orders": 892,
    "revenue": 3250000,
    "avg_order": 3644.39,
    "customers": 456
  },
  "previous_period": {
    "start": "2025-09-01",
    "end": "2025-09-30",
    "orders": 856,
    "revenue": 2980000,
    "avg_order": 3481.31,
    "customers": 420
  },
  "changes": {
    "orders": 4.21,
    "revenue": 9.06,
    "avg_order": 4.68,
    "customers": 8.57
  }
}
```

#### 8. Export CSV

```http
GET /admin/analytics/export?type=revenue&period=month
GET /admin/analytics/export?type=items&period=week
GET /admin/analytics/export?type=customers&period=month
```

**Types disponibles** : `revenue`, `items`, `customers`

---

## 💻 UTILISATION DANS LE CODE

### Exemple 1: Dans un Contrôleur

```php
use App\Services\AnalyticsService;

class MyController extends Controller
{
    protected $analytics;

    public function __construct(AnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    public function showDashboard()
    {
        $vendor_id = Auth::user()->id;
        
        // CA du jour
        $todayRevenue = $this->analytics->getRevenueStats($vendor_id, 'today');
        
        // Top 10 plats du mois
        $topItems = $this->analytics->getTopSellingItems($vendor_id, 10, 'month');
        
        // Heures de pointe de la semaine
        $peakHours = $this->analytics->getPeakHours($vendor_id, 'week');
        
        // Stats clients du mois
        $customerStats = $this->analytics->getCustomerAnalytics($vendor_id, 'month');
        
        return view('dashboard', compact('todayRevenue', 'topItems', 'peakHours', 'customerStats'));
    }
}
```

### Exemple 2: Dashboard Complet

```php
use App\Services\AnalyticsService;

$analytics = new AnalyticsService();
$vendor_id = 123;

// Obtenir toutes les stats en une fois
$dashboard = $analytics->getCompleteDashboard($vendor_id, 'today');

// $dashboard contient :
// - revenue (CA)
// - top_items (top produits)
// - peak_hours (heures de pointe)
// - customer_analytics (stats clients)
```

### Exemple 3: Comparaison Personnalisée

```php
use App\Services\AnalyticsService;

$analytics = new AnalyticsService();
$vendor_id = 123;

// Comparer octobre 2025 vs septembre 2025
$comparison = $analytics->comparePeriods(
    $vendor_id,
    '2025-10-01', '2025-10-31',  // Période actuelle
    '2025-09-01', '2025-09-30'   // Période précédente
);

// Afficher la variation du CA
echo "CA actuel : " . $comparison['current_period']['revenue'];
echo "CA précédent : " . $comparison['previous_period']['revenue'];
echo "Variation : " . $comparison['changes']['revenue'] . "%";
```

---

## 📱 INTÉGRATION FRONTEND

### Exemple avec Chart.js

```html
<canvas id="revenueChart"></canvas>

<script>
// Récupérer les données
fetch('/admin/analytics/revenue?period=month')
    .then(res => res.json())
    .then(data => {
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: ['Actuel', 'Précédent'],
                datasets: [{
                    label: 'Chiffre d\'Affaires',
                    data: [data.current.revenue, data.previous.revenue],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            }
        });
    });
</script>
```

### Exemple Heures de Pointe

```html
<canvas id="peakHoursChart"></canvas>

<script>
fetch('/admin/analytics/peak-hours?period=week')
    .then(res => res.json())
    .then(data => {
        const labels = data.hourly_stats.map(h => h.hour_label);
        const orderCounts = data.hourly_stats.map(h => h.order_count);
        
        new Chart(document.getElementById('peakHoursChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Commandes par Heure',
                    data: orderCounts,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
```

### Exemple Top Produits (Table)

```html
<table id="topProductsTable">
    <thead>
        <tr>
            <th>Produit</th>
            <th>Quantité</th>
            <th>CA</th>
            <th>Commandes</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script>
fetch('/admin/analytics/top-selling?limit=10&period=month')
    .then(res => res.json())
    .then(items => {
        const tbody = document.querySelector('#topProductsTable tbody');
        items.forEach(item => {
            const row = `
                <tr>
                    <td>${item.item_name}</td>
                    <td>${item.total_quantity}</td>
                    <td>${item.total_revenue.toLocaleString()} FCFA</td>
                    <td>${item.order_count}</td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
    });
</script>
```

---

## 🎨 WIDGETS DASHBOARD

### Widget CA du Jour

```html
<div class="revenue-widget">
    <h3>CA Aujourd'hui</h3>
    <div id="todayRevenue">--</div>
    <div id="revenueChange" class="badge">--</div>
</div>

<script>
fetch('/admin/analytics/revenue?period=today')
    .then(res => res.json())
    .then(data => {
        document.getElementById('todayRevenue').textContent = 
            data.current.revenue.toLocaleString() + ' FCFA';
        
        const changeEl = document.getElementById('revenueChange');
        const change = data.change.revenue;
        
        changeEl.textContent = change > 0 ? `+${change}%` : `${change}%`;
        changeEl.className = change > 0 ? 'badge success' : 'badge danger';
    });
</script>
```

### Widget Heures de Pointe

```html
<div class="peak-hours-widget">
    <h3>Heures de Pointe</h3>
    <ul id="peakHoursList"></ul>
</div>

<script>
fetch('/admin/analytics/peak-hours?period=week')
    .then(res => res.json())
    .then(data => {
        const list = document.getElementById('peakHoursList');
        data.peak_hours.forEach((hour, index) => {
            list.innerHTML += `
                <li>
                    <span class="rank">#${index + 1}</span>
                    <span class="time">${hour.hour_label}</span>
                    <span class="count">${hour.order_count} commandes</span>
                </li>
            `;
        });
    });
</script>
```

---

## 📊 MÉTRIQUES DISPONIBLES

### Chiffre d'Affaires
- CA total de la période
- Nombre de commandes
- Panier moyen
- Variation vs période précédente (%)

### Produits
- Quantité vendue
- CA généré
- Nombre de commandes contenant le produit
- Prix moyen
- Catégorie

### Heures
- Statistiques par heure (0h à 23h)
- Nombre de commandes
- CA par heure
- Panier moyen par heure
- Top 3 heures les plus chargées

### Clients
- Total clients uniques
- Nouveaux clients
- Clients récurrents
- Taux de rétention (%)
- Top 10 clients
- Dépense moyenne par client

### Catégories
- Articles vendus
- Quantité totale
- CA par catégorie

---

## 🔍 FILTRES DISPONIBLES

### Périodes Prédéfinies

- `today` : Aujourd'hui
- `week` : Cette semaine
- `month` : Ce mois
- `year` : Cette année

### Périodes Personnalisées

Pour la comparaison, vous pouvez spécifier n'importe quelle période :

```http
GET /admin/analytics/compare
  ?current_start=2025-10-15
  &current_end=2025-10-21
  &previous_start=2025-10-08
  &previous_end=2025-10-14
```

---

## 📈 CAS D'USAGE

### 1. Optimiser les Heures d'Ouverture

```php
$peakHours = $analytics->getPeakHours($vendor_id, 'month');

// Identifier les heures creuses
$lowHours = array_filter($peakHours['hourly_stats'], function($h) {
    return $h['order_count'] < 5;
});

// Décision : fermer pendant les heures creuses ou faire des promotions
```

### 2. Gérer le Stock

```php
$topItems = $analytics->getTopSellingItems($vendor_id, 20, 'week');

// Items qui se vendent le plus → augmenter stock
// Items qui se vendent peu → réduire stock ou promotions
```

### 3. Programme de Fidélité

```php
$customerAnalytics = $analytics->getCustomerAnalytics($vendor_id, 'month');

// Clients récurrents → offrir des récompenses
// Nouveaux clients → message de bienvenue
// Clients inactifs → campagne de réactivation
```

### 4. Prévisions

```php
$comparison = $analytics->comparePeriods(...);

if ($comparison['changes']['revenue'] > 20) {
    // Croissance forte → prévoir plus de stock
} elseif ($comparison['changes']['revenue'] < -20) {
    // Baisse importante → analyser les causes
}
```

---

## 🎯 PROCHAINES ÉTAPES

### Phase 1 (Actuelle - TERMINÉE ✅)
- [x] Service Analytics complet
- [x] API REST endpoints
- [x] Export CSV
- [x] Documentation

### Phase 2 (À venir)
- [ ] Interface graphique admin
- [ ] Graphiques Chart.js intégrés
- [ ] Tableaux de bord personnalisables
- [ ] Alertes automatiques

### Phase 3 (Avancé)
- [ ] Prévisions avec ML
- [ ] Recommandations automatiques
- [ ] Rapports PDF programmés
- [ ] Analytics en temps réel (WebSockets)

---

## 🆘 SUPPORT

### Tester les Endpoints

```bash
# CA du jour
curl "http://localhost:8000/admin/analytics/revenue?period=today"

# Top 10 produits du mois
curl "http://localhost:8000/admin/analytics/top-selling?limit=10&period=month"

# Heures de pointe de la semaine
curl "http://localhost:8000/admin/analytics/peak-hours?period=week"

# Stats clients
curl "http://localhost:8000/admin/analytics/customers?period=month"

# Export CSV
curl "http://localhost:8000/admin/analytics/export?type=revenue&period=month" -o analytics.csv
```

### Logs

Les requêtes analytics sont loggées dans `storage/logs/laravel.log`.

---

**Version** : 1.0  
**Date** : 23 octobre 2025  
**Statut** : ✅ Production Ready  
**Tests** : À implémenter

🚀 **Système d'analytics complet prêt à l'emploi !**

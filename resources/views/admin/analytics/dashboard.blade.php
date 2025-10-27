@extends('admin.layout.default')

@section('content')
<div class="row justify-content-between align-items-center mb-3">
    <div class="col-12 col-md-6">
        <h5 class="pages-title fs-2">📊 {{ trans('labels.analytics') ?? 'Analytics' }}</h5>
        <p class="text-muted">Analysez les performances de votre restaurant</p>
    </div>
    <div class="col-12 col-md-6 text-end">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-sm btn-outline-primary period-filter active" data-period="today">
                {{ trans('labels.today') ?? 'Aujourd\'hui' }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary period-filter" data-period="week">
                {{ trans('labels.week') ?? 'Semaine' }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary period-filter" data-period="month">
                {{ trans('labels.month') ?? 'Mois' }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary period-filter" data-period="year">
                {{ trans('labels.year') ?? 'Année' }}
            </button>
        </div>
        <button class="btn btn-sm btn-success ms-2" id="export-btn">
            <i class="fa-solid fa-download"></i> {{ trans('labels.export') ?? 'Export CSV' }}
        </button>
    </div>
</div>

<!-- Revenue Cards -->
<div class="row mb-4">
    <div class="col-xxl-3 col-lg-3 col-md-6 col-sm-6 mb-3">
        <div class="card border-0 box-shadow h-100">
            <div class="card-body">
                <div class="dashboard-card">
                    <span>
                        <p class="fw-semibold mb-1 text-muted">{{ trans('labels.revenue') ?? 'Chiffre d\'Affaires' }}</p>
                        <h4 class="text-primary fw-bold" id="total-revenue">-</h4>
                        <small class="revenue-change"></small>
                    </span>
                    <span class="card-icon bg-primary">
                        <i class="fa-solid fa-money-bill-wave text-white"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-lg-3 col-md-6 col-sm-6 mb-3">
        <div class="card border-0 box-shadow h-100">
            <div class="card-body">
                <div class="dashboard-card">
                    <span>
                        <p class="fw-semibold mb-1 text-muted">{{ trans('labels.orders') ?? 'Commandes' }}</p>
                        <h4 class="text-success fw-bold" id="total-orders">-</h4>
                        <small class="orders-change"></small>
                    </span>
                    <span class="card-icon bg-success">
                        <i class="fa-solid fa-shopping-cart text-white"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-lg-3 col-md-6 col-sm-6 mb-3">
        <div class="card border-0 box-shadow h-100">
            <div class="card-body">
                <div class="dashboard-card">
                    <span>
                        <p class="fw-semibold mb-1 text-muted">Panier Moyen</p>
                        <h4 class="text-info fw-bold" id="avg-order">-</h4>
                        <small class="avg-change"></small>
                    </span>
                    <span class="card-icon bg-info">
                        <i class="fa-solid fa-chart-line text-white"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-lg-3 col-md-6 col-sm-6 mb-3">
        <div class="card border-0 box-shadow h-100">
            <div class="card-body">
                <div class="dashboard-card">
                    <span>
                        <p class="fw-semibold mb-1 text-muted">{{ trans('labels.customers') ?? 'Clients' }}</p>
                        <h4 class="text-warning fw-bold" id="total-customers">-</h4>
                        <small class="customers-info"></small>
                    </span>
                    <span class="card-icon bg-warning">
                        <i class="fa-solid fa-users text-white"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 1 -->
<div class="row mb-4">
    <!-- Revenue Chart -->
    <div class="col-lg-8 mb-3">
        <div class="card border-0 box-shadow h-100">
            <div class="card-header bg-white border-0">
                <h6 class="fw-bold mb-0">📈 Évolution du CA</h6>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Categories Chart -->
    <div class="col-lg-4 mb-3">
        <div class="card border-0 box-shadow h-100">
            <div class="card-header bg-white border-0">
                <h6 class="fw-bold mb-0">🥘 {{ trans('labels.categories') ?? 'Catégories' }}</h6>
            </div>
            <div class="card-body">
                <canvas id="categoriesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="row mb-4">
    <!-- Peak Hours Chart -->
    <div class="col-lg-6 mb-3">
        <div class="card border-0 box-shadow h-100">
            <div class="card-header bg-white border-0">
                <h6 class="fw-bold mb-0">⏰ Heures de Pointe</h6>
            </div>
            <div class="card-body">
                <canvas id="peakHoursChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Selling Items -->
    <div class="col-lg-6 mb-3">
        <div class="card border-0 box-shadow h-100">
            <div class="card-header bg-white border-0">
                <h6 class="fw-bold mb-0">🏆 Top Produits</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ trans('labels.product') ?? 'Produit' }}</th>
                                <th>{{ trans('labels.quantity') ?? 'Qté' }}</th>
                                <th>{{ trans('labels.revenue') ?? 'CA' }}</th>
                            </tr>
                        </thead>
                        <tbody id="top-selling-tbody">
                            <tr>
                                <td colspan="4" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- VIP Customers -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 box-shadow">
            <div class="card-header bg-white border-0">
                <h6 class="fw-bold mb-0">👑 Clients VIP</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ trans('labels.customer') ?? 'Client' }}</th>
                                <th>{{ trans('labels.orders') ?? 'Commandes' }}</th>
                                <th>Total Dépensé</th>
                                <th>Panier Moyen</th>
                            </tr>
                        </thead>
                        <tbody id="vip-customers-tbody">
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let currentPeriod = 'today';
let revenueChart, categoriesChart, peakHoursChart;

// Currency formatter
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        minimumFractionDigits: 0
    }).format(amount);
};

// Format change percentage
const formatChange = (change) => {
    const icon = change >= 0 ? '↑' : '↓';
    const color = change >= 0 ? 'text-success' : 'text-danger';
    return `<span class="${color}">${icon} ${Math.abs(change)}%</span>`;
};

// Load analytics data
async function loadAnalytics(period = 'today') {
    currentPeriod = period;

    try {
        // Load all data in parallel
        const [revenue, topSelling, peakHours, customers, categories] = await Promise.all([
            fetch(`/admin/analytics/revenue?period=${period}`).then(r => r.json()),
            fetch(`/admin/analytics/top-selling?limit=10&period=${period}`).then(r => r.json()),
            fetch(`/admin/analytics/peak-hours?period=${period}`).then(r => r.json()),
            fetch(`/admin/analytics/customers?period=${period}`).then(r => r.json()),
            fetch(`/admin/analytics/categories?period=${period}`).then(r => r.json())
        ]);

        // Update cards
        updateRevenueCards(revenue);
        updateCustomersCard(customers);

        // Update charts
        updateRevenueChart(revenue);
        updateCategoriesChart(categories);
        updatePeakHoursChart(peakHours);

        // Update tables
        updateTopSellingTable(topSelling);
        updateVIPCustomersTable(customers.top_customers);

    } catch (error) {
        console.error('Error loading analytics:', error);
    }
}

// Update revenue cards
function updateRevenueCards(data) {
    document.getElementById('total-revenue').textContent = formatCurrency(data.current.revenue);
    document.getElementById('total-orders').textContent = data.current.orders;
    document.getElementById('avg-order').textContent = formatCurrency(data.current.avg_order);

    document.querySelector('.revenue-change').innerHTML = formatChange(data.change.revenue);
    document.querySelector('.orders-change').innerHTML = formatChange(data.change.orders);
    document.querySelector('.avg-change').innerHTML = formatChange(data.change.avg_order);
}

// Update customers card
function updateCustomersCard(data) {
    document.getElementById('total-customers').textContent = data.total_customers;
    document.querySelector('.customers-info').innerHTML =
        `<span class="text-success">${data.new_customers} nouveaux</span>`;
}

// Update revenue chart
function updateRevenueChart(data) {
    const ctx = document.getElementById('revenueChart').getContext('2d');

    if (revenueChart) {
        revenueChart.destroy();
    }

    // Generate labels based on period
    const labels = generateLabels(currentPeriod);
    const revenueData = generateRevenueData(data, currentPeriod);

    revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'CA (FCFA)',
                data: revenueData,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: (context) => formatCurrency(context.parsed.y)
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => formatCurrency(value)
                    }
                }
            }
        }
    });
}

// Update categories chart
function updateCategoriesChart(data) {
    const ctx = document.getElementById('categoriesChart').getContext('2d');

    if (categoriesChart) {
        categoriesChart.destroy();
    }

    const colors = [
        'rgba(255, 99, 132, 0.8)',
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(153, 102, 255, 0.8)',
        'rgba(255, 159, 64, 0.8)'
    ];

    categoriesChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(c => c.category_name),
            datasets: [{
                data: data.map(c => c.revenue),
                backgroundColor: colors.slice(0, data.length)
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            const label = context.label || '';
                            const value = formatCurrency(context.parsed);
                            return `${label}: ${value}`;
                        }
                    }
                }
            }
        }
    });
}

// Update peak hours chart
function updatePeakHoursChart(data) {
    const ctx = document.getElementById('peakHoursChart').getContext('2d');

    if (peakHoursChart) {
        peakHoursChart.destroy();
    }

    const hourlyData = data.hourly_stats || [];

    peakHoursChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: hourlyData.map(h => h.hour_label),
            datasets: [{
                label: 'Commandes',
                data: hourlyData.map(h => h.orders),
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

// Update top selling table
function updateTopSellingTable(data) {
    const tbody = document.getElementById('top-selling-tbody');

    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Aucune donnée</td></tr>';
        return;
    }

    tbody.innerHTML = data.map((item, index) => `
        <tr>
            <td>${index + 1}</td>
            <td>
                <strong>${item.item_name}</strong><br>
                <small class="text-muted">${item.category_name || '-'}</small>
            </td>
            <td><span class="badge bg-primary">${item.total_quantity}</span></td>
            <td><strong>${formatCurrency(item.total_revenue)}</strong></td>
        </tr>
    `).join('');
}

// Update VIP customers table
function updateVIPCustomersTable(data) {
    const tbody = document.getElementById('vip-customers-tbody');

    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Aucune donnée</td></tr>';
        return;
    }

    tbody.innerHTML = data.map((customer, index) => `
        <tr>
            <td>${index + 1}</td>
            <td>
                <i class="fa-solid fa-user-circle text-warning"></i>
                <strong>${customer.customer_name || 'Client #' + customer.customer_id}</strong><br>
                <small class="text-muted">${customer.customer_email || '-'}</small>
            </td>
            <td><span class="badge bg-success">${customer.total_orders}</span></td>
            <td><strong>${formatCurrency(customer.total_spent)}</strong></td>
            <td>${formatCurrency(customer.avg_order)}</td>
        </tr>
    `).join('');
}

// Generate labels based on period
function generateLabels(period) {
    const today = new Date();
    const labels = [];

    switch(period) {
        case 'today':
            for(let i = 0; i < 24; i++) {
                labels.push(`${i}h`);
            }
            break;
        case 'week':
            const days = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
            for(let i = 6; i >= 0; i--) {
                const date = new Date(today);
                date.setDate(date.getDate() - i);
                labels.push(days[date.getDay()]);
            }
            break;
        case 'month':
            for(let i = 29; i >= 0; i--) {
                const date = new Date(today);
                date.setDate(date.getDate() - i);
                labels.push(date.getDate());
            }
            break;
        case 'year':
            const months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
            months.forEach(m => labels.push(m));
            break;
    }

    return labels;
}

// Generate revenue data (mock for demo)
function generateRevenueData(data, period) {
    const baseRevenue = data.current.revenue;
    const count = period === 'today' ? 24 : period === 'week' ? 7 : period === 'month' ? 30 : 12;

    return Array.from({length: count}, () => Math.random() * baseRevenue * 0.2);
}

// Period filter buttons
document.querySelectorAll('.period-filter').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.period-filter').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        loadAnalytics(this.dataset.period);
    });
});

// Export button
document.getElementById('export-btn').addEventListener('click', function() {
    window.location.href = `/admin/analytics/export?type=revenue&period=${currentPeriod}`;
});

// Load initial data
document.addEventListener('DOMContentLoaded', function() {
    loadAnalytics('today');
});
</script>

<style>
.card-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.dashboard-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.box-shadow {
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
}

.period-filter.active {
    background-color: #0d6efd;
    color: white;
}

.table > :not(caption) > * > * {
    padding: 0.75rem;
}
</style>
@endsection

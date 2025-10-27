@extends('admin.layout.auth')
@section('title')
    Dashboard Addons
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">Dashboard Addons RestroSaaS</h1>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary btn-sm" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt me-1"></i> Actualiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques générales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Commandes Totales
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_orders']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Revenus Totaux
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">€{{ number_format($stats['total_revenue'], 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-euro-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Membres Fidélité
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['loyalty_members']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Terminaux POS
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['pos_terminals']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cash-register fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Addons disponibles -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Addons RestroSaaS</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- POS System -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 border-left-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-cash-register fa-3x text-primary me-3"></i>
                                        <div>
                                            <h5 class="card-title mb-1">Système POS</h5>
                                            <span class="badge badge-success">Actif</span>
                                        </div>
                                    </div>
                                    <p class="card-text">Point de vente complet avec gestion des terminaux, sessions et paiements.</p>
                                    <a href="{{ route('admin.addons.pos') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-cogs me-1"></i> Gérer
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Loyalty Program -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 border-left-warning">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-star fa-3x text-warning me-3"></i>
                                        <div>
                                            <h5 class="card-title mb-1">Programme Fidélité</h5>
                                            <span class="badge badge-success">Actif</span>
                                        </div>
                                    </div>
                                    <p class="card-text">Gestion complète des points de fidélité et récompenses clients.</p>
                                    <a href="{{ route('admin.addons.loyalty') }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-cogs me-1"></i> Gérer
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Import/Export -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 border-left-info">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-exchange-alt fa-3x text-info me-3"></i>
                                        <div>
                                            <h5 class="card-title mb-1">Import/Export</h5>
                                            <span class="badge badge-success">Actif</span>
                                        </div>
                                    </div>
                                    <p class="card-text">Outils d'import et export de données (menus, clients, commandes).</p>
                                    <a href="{{ route('admin.addons.import-export') }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-cogs me-1"></i> Gérer
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Firebase Notifications -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 border-left-danger">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-bell fa-3x text-danger me-3"></i>
                                        <div>
                                            <h5 class="card-title mb-1">Notifications Push</h5>
                                            <span class="badge badge-success">Actif</span>
                                        </div>
                                    </div>
                                    <p class="card-text">Système de notifications push Firebase pour mobile.</p>
                                    <a href="{{ route('admin.addons.notifications') }}" class="btn btn-danger btn-sm">
                                        <i class="fas fa-cogs me-1"></i> Gérer
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- TableQR System -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 border-left-secondary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-qrcode fa-3x text-secondary me-3"></i>
                                        <div>
                                            <h5 class="card-title mb-1">QR Code Tables</h5>
                                            <span class="badge badge-success">Actif</span>
                                        </div>
                                    </div>
                                    <p class="card-text">Génération de QR codes pour les tables avec menus intégrés.</p>
                                    <a href="{{ route('admin.table-qr.index') }}" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-cogs me-1"></i> Gérer
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- API Routes -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 border-left-dark">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-code fa-3x text-dark me-3"></i>
                                        <div>
                                            <h5 class="card-title mb-1">API Routes</h5>
                                            <span class="badge badge-success">Actif</span>
                                        </div>
                                    </div>
                                    <p class="card-text">API REST complète avec authentification pour intégrations mobiles.</p>
                                    <a href="{{ route('admin.api.documentation') }}" class="btn btn-dark btn-sm">
                                        <i class="fas fa-book me-1"></i> Documentation
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques et activité récente -->
    <div class="row">
        <!-- Revenus quotidiens -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Revenus des 7 derniers jours</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="dailyRevenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activité récente -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Activité Récente</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($recentActivity as $activity)
                        <div class="list-group-item d-flex justify-content-between align-items-start border-0 px-0">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">
                                    <i class="fas fa-{{ $activity['icon'] }} text-{{ $activity['color'] }} me-1"></i>
                                    {{ $activity['message'] }}
                                </div>
                                <small class="text-muted">{{ $activity['created_at']->diffForHumans() }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Graphique des revenus quotidiens
const dailyRevenueData = @json($chartsData['daily_revenue']);
const ctx = document.getElementById('dailyRevenueChart').getContext('2d');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: dailyRevenueData.map(item => item.date),
        datasets: [{
            label: 'Revenus (€)',
            data: dailyRevenueData.map(item => parseFloat(item.total)),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '€' + value.toFixed(2);
                    }
                }
            }
        }
    }
});

// Fonction de rafraîchissement
function refreshDashboard() {
    location.reload();
}
</script>
@endpush

@extends('admin.layout.auth')
@section('title')
    Système POS
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Système Point de Vente</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.addons.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">POS</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.pos.terminals.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Nouveau Terminal
                    </a>
                    <button class="btn btn-secondary btn-sm" onclick="window.open('{{ route('pos.index') }}', '_blank')">
                        <i class="fas fa-external-link-alt me-1"></i> Ouvrir POS
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques POS -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Ventes Aujourd'hui
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">€{{ number_format($posStats['today_sales'], 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-euro-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Commandes Aujourd'hui
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $posStats['today_orders'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
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
                                Terminaux Actifs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $posStats['active_terminals'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-desktop fa-2x text-gray-300"></i>
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
                                Sessions Actives
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $posStats['active_sessions'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Terminaux POS -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Terminaux POS</h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshTerminals()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Terminal</th>
                                    <th>Statut</th>
                                    <th>Utilisateur</th>
                                    <th>Dernière Activité</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($terminals as $terminal)
                                <tr>
                                    <td>
                                        <strong>{{ $terminal->name }}</strong><br>
                                        <small class="text-muted">{{ $terminal->code }}</small>
                                    </td>
                                    <td>
                                        @if($terminal->status === 'active')
                                            <span class="badge badge-success">Actif</span>
                                        @elseif($terminal->status === 'inactive')
                                            <span class="badge badge-secondary">Inactif</span>
                                        @else
                                            <span class="badge badge-warning">Maintenance</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($terminal->currentUser)
                                            {{ $terminal->currentUser->name }}
                                        @else
                                            <span class="text-muted">Libre</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($terminal->last_activity)
                                            {{ $terminal->last_activity->diffForHumans() }}
                                        @else
                                            <span class="text-muted">Jamais</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('pos.terminal', $terminal->id) }}"
                                               class="btn btn-outline-primary" target="_blank"
                                               title="Ouvrir Terminal">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                            <a href="{{ route('admin.pos.terminals.edit', $terminal->id) }}"
                                               class="btn btn-outline-secondary"
                                               title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($terminal->status === 'active')
                                            <button class="btn btn-outline-warning"
                                                    onclick="toggleTerminal({{ $terminal->id }}, 'inactive')"
                                                    title="Désactiver">
                                                <i class="fas fa-pause"></i>
                                            </button>
                                            @else
                                            <button class="btn btn-outline-success"
                                                    onclick="toggleTerminal({{ $terminal->id }}, 'active')"
                                                    title="Activer">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sessions récentes -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Sessions Récentes</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($recentSessions as $session)
                        <div class="list-group-item d-flex justify-content-between align-items-start border-0 px-0">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">
                                    {{ $session->terminal->name }}
                                    @if($session->status === 'active')
                                        <span class="badge badge-success badge-sm ms-2">Actif</span>
                                    @else
                                        <span class="badge badge-secondary badge-sm ms-2">Fermé</span>
                                    @endif
                                </div>
                                <small class="text-muted">
                                    {{ $session->user->name }} •
                                    {{ $session->created_at->format('d/m H:i') }}
                                </small>
                                @if($session->status === 'closed' && $session->total_sales > 0)
                                <div class="mt-1">
                                    <small class="text-success">€{{ number_format($session->total_sales, 2) }}</small>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions Rapides</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.pos.reports') }}" class="btn btn-outline-primary">
                            <i class="fas fa-chart-bar me-2"></i> Rapports POS
                        </a>
                        <a href="{{ route('admin.pos.settings') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-cogs me-2"></i> Configuration
                        </a>
                        <button class="btn btn-outline-info" onclick="exportPOSData()">
                            <i class="fas fa-download me-2"></i> Exporter Données
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function refreshTerminals() {
    location.reload();
}

function toggleTerminal(terminalId, status) {
    if (confirm('Êtes-vous sûr de vouloir changer le statut de ce terminal ?')) {
        fetch(`/admin/pos/terminals/${terminalId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors du changement de statut');
            }
        })
        .catch(error => {
            alert('Erreur de connexion');
        });
    }
}

function exportPOSData() {
    window.open('/admin/pos/export', '_blank');
}
</script>
@endpush

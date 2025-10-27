@extends('admin.layout.auth')
@section('title')
    Programme de Fidélité
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Programme de Fidélité</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.addons.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Fidélité</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#createProgramModal">
                        <i class="fas fa-plus me-1"></i> Nouveau Programme
                    </button>
                    <button class="btn btn-info btn-sm" onclick="exportMembers()">
                        <i class="fas fa-download me-1"></i> Exporter Membres
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques Fidélité -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Membres Actifs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $loyaltyStats['active_members'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                Points Distribués
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($loyaltyStats['total_points']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
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
                                Points Utilisés
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($loyaltyStats['used_points']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-gift fa-2x text-gray-300"></i>
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
                                Taux Rétention
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($loyaltyStats['retention_rate'], 1) }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Programmes de Fidélité -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Programmes Actifs</h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshPrograms()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Programme</th>
                                    <th>Type</th>
                                    <th>Membres</th>
                                    <th>Taux de Change</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($programs as $program)
                                <tr>
                                    <td>
                                        <strong>{{ $program->name }}</strong><br>
                                        <small class="text-muted">{{ Str::limit($program->description, 50) }}</small>
                                    </td>
                                    <td>
                                        @if($program->type === 'points')
                                            <span class="badge badge-primary">Points</span>
                                        @elseif($program->type === 'cashback')
                                            <span class="badge badge-success">Cashback</span>
                                        @else
                                            <span class="badge badge-info">Niveaux</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="font-weight-bold">{{ $program->members_count ?? 0 }}</span>
                                    </td>
                                    <td>
                                        @if($program->type === 'points')
                                            1€ = {{ $program->points_per_euro }} pts
                                        @else
                                            {{ $program->cashback_percentage }}%
                                        @endif
                                    </td>
                                    <td>
                                        @if($program->is_active)
                                            <span class="badge badge-success">Actif</span>
                                        @else
                                            <span class="badge badge-secondary">Inactif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary"
                                                    onclick="viewProgram({{ $program->id }})"
                                                    title="Voir Détails">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary"
                                                    onclick="editProgram({{ $program->id }})"
                                                    title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if($program->is_active)
                                            <button class="btn btn-outline-warning"
                                                    onclick="toggleProgram({{ $program->id }}, false)"
                                                    title="Désactiver">
                                                <i class="fas fa-pause"></i>
                                            </button>
                                            @else
                                            <button class="btn btn-outline-success"
                                                    onclick="toggleProgram({{ $program->id }}, true)"
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

        <!-- Membres récents et actions -->
        <div class="col-xl-4 col-lg-5">
            <!-- Nouveaux membres -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Nouveaux Membres</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($recentMembers as $member)
                        <div class="list-group-item d-flex justify-content-between align-items-start border-0 px-0">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">
                                    {{ $member->user->name }}
                                </div>
                                <small class="text-muted">
                                    {{ $member->program->name }} •
                                    {{ $member->created_at->format('d/m/Y') }}
                                </small>
                                <div class="mt-1">
                                    <small class="text-primary">
                                        <i class="fas fa-star"></i> {{ number_format($member->points_balance) }} points
                                    </small>
                                </div>
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
                        <button class="btn btn-outline-primary" onclick="showMemberSearch()">
                            <i class="fas fa-search me-2"></i> Rechercher Membre
                        </button>
                        <button class="btn btn-outline-success" onclick="addPointsModal()">
                            <i class="fas fa-plus me-2"></i> Ajouter Points
                        </button>
                        <button class="btn btn-outline-info" onclick="showRewards()">
                            <i class="fas fa-gift me-2"></i> Gérer Récompenses
                        </button>
                        <a href="{{ route('admin.loyalty.analytics') }}" class="btn btn-outline-warning">
                            <i class="fas fa-chart-pie me-2"></i> Analytiques
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Création Programme -->
<div class="modal fade" id="createProgramModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouveau Programme de Fidélité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createProgramForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nom du Programme</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Type</label>
                                <select class="form-select" name="type" required>
                                    <option value="points">Points</option>
                                    <option value="cashback">Cashback</option>
                                    <option value="tiers">Niveaux</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Points par Euro</label>
                                <input type="number" class="form-control" name="points_per_euro" value="1" min="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Valeur du Point (€)</label>
                                <input type="number" class="form-control" name="point_value" value="0.01" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="createProgram()">Créer Programme</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function refreshPrograms() {
    location.reload();
}

function viewProgram(id) {
    window.location.href = `/admin/loyalty/programs/${id}`;
}

function editProgram(id) {
    window.location.href = `/admin/loyalty/programs/${id}/edit`;
}

function toggleProgram(id, active) {
    const action = active ? 'activer' : 'désactiver';
    if (confirm(`Êtes-vous sûr de vouloir ${action} ce programme ?`)) {
        fetch(`/admin/loyalty/programs/${id}/toggle`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ is_active: active })
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

function createProgram() {
    const form = document.getElementById('createProgramForm');
    const formData = new FormData(form);

    fetch('/admin/loyalty/programs', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur lors de la création du programme');
        }
    })
    .catch(error => {
        alert('Erreur de connexion');
    });
}

function showMemberSearch() {
    // Implémenter la recherche de membre
    const search = prompt('Rechercher un membre par nom ou email:');
    if (search) {
        window.location.href = `/admin/loyalty/members?search=${encodeURIComponent(search)}`;
    }
}

function addPointsModal() {
    // Implémenter l'ajout de points
    alert('Fonctionnalité d\'ajout de points à implémenter');
}

function showRewards() {
    window.location.href = '/admin/loyalty/rewards';
}

function exportMembers() {
    window.open('/admin/loyalty/export/members', '_blank');
}
</script>
@endpush

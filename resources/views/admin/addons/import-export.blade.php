@extends('admin.layout.auth')
@section('title')
    Import/Export
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Import/Export de Données</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.addons.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Import/Export</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fas fa-upload me-1"></i> Importer
                    </button>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
                        <i class="fas fa-download me-1"></i> Exporter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques Import/Export -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Imports Réussis
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $importStats['successful'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Imports Échoués
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $importStats['failed'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                                Exports Générés
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $exportStats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-export fa-2x text-gray-300"></i>
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
                                En Cours
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $processingJobs }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-spinner fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Historique des tâches -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Historique des Tâches</h6>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="filterType" onchange="filterJobs()">
                            <option value="">Tous types</option>
                            <option value="import">Import</option>
                            <option value="export">Export</option>
                        </select>
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshJobs()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Fichier/Format</th>
                                    <th>Statut</th>
                                    <th>Progression</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jobs as $job)
                                <tr>
                                    <td>
                                        @if($job->type === 'import')
                                            <span class="badge badge-success">
                                                <i class="fas fa-upload"></i> Import
                                            </span>
                                        @else
                                            <span class="badge badge-primary">
                                                <i class="fas fa-download"></i> Export
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $job->file_name ?? $job->export_format }}</strong><br>
                                        <small class="text-muted">{{ $job->data_type }}</small>
                                    </td>
                                    <td>
                                        @if($job->status === 'pending')
                                            <span class="badge badge-warning">En attente</span>
                                        @elseif($job->status === 'processing')
                                            <span class="badge badge-info">En cours</span>
                                        @elseif($job->status === 'completed')
                                            <span class="badge badge-success">Terminé</span>
                                        @else
                                            <span class="badge badge-danger">Échoué</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar
                                                @if($job->status === 'completed') bg-success
                                                @elseif($job->status === 'failed') bg-danger
                                                @else bg-info
                                                @endif"
                                                 style="width: {{ $job->progress }}%">
                                                {{ $job->progress }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $job->created_at->format('d/m/Y H:i') }}
                                        @if($job->completed_at)
                                            <br><small class="text-success">Terminé: {{ $job->completed_at->format('H:i') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @if($job->status === 'completed' && $job->type === 'export')
                                            <a href="{{ route('admin.import-export.download', $job->id) }}"
                                               class="btn btn-outline-success"
                                               title="Télécharger">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            @endif
                                            <button class="btn btn-outline-primary"
                                                    onclick="viewJobDetails({{ $job->id }})"
                                                    title="Détails">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if($job->status === 'failed')
                                            <button class="btn btn-outline-warning"
                                                    onclick="retryJob({{ $job->id }})"
                                                    title="Retry">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                            @endif
                                            <button class="btn btn-outline-danger"
                                                    onclick="deleteJob({{ $job->id }})"
                                                    title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
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

        <!-- Modèles et Actions rapides -->
        <div class="col-xl-4 col-lg-5">
            <!-- Modèles d'import -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Modèles d'Import</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.import-export.template', 'menu') }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-utensils me-2"></i> Menu & Articles
                        </a>
                        <a href="{{ route('admin.import-export.template', 'customers') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-users me-2"></i> Clients
                        </a>
                        <a href="{{ route('admin.import-export.template', 'orders') }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-shopping-cart me-2"></i> Commandes
                        </a>
                        <a href="{{ route('admin.import-export.template', 'inventory') }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-boxes me-2"></i> Inventaire
                        </a>
                    </div>
                </div>
            </div>

            <!-- Exports rapides -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Exports Rapides</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-success btn-sm" onclick="quickExport('menu')">
                            <i class="fas fa-utensils me-2"></i> Menu Complet
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="quickExport('customers')">
                            <i class="fas fa-users me-2"></i> Base Clients
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="quickExport('orders')">
                            <i class="fas fa-shopping-cart me-2"></i> Commandes (30j)
                        </button>
                        <button class="btn btn-outline-warning btn-sm" onclick="quickExport('analytics')">
                            <i class="fas fa-chart-bar me-2"></i> Données Analytiques
                        </button>
                    </div>
                </div>
            </div>

            <!-- Aide -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Aide & Documentation</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0">
                            <i class="fas fa-file-alt text-primary me-2"></i>
                            Guide d'Import CSV
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0">
                            <i class="fas fa-video text-success me-2"></i>
                            Tutoriel Vidéo
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0">
                            <i class="fas fa-question-circle text-info me-2"></i>
                            FAQ Import/Export
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importer des Données</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="importForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Type de données</label>
                        <select class="form-select" name="data_type" required>
                            <option value="">Sélectionner le type</option>
                            <option value="menu">Menu & Articles</option>
                            <option value="customers">Clients</option>
                            <option value="orders">Commandes</option>
                            <option value="inventory">Inventaire</option>
                            <option value="loyalty">Membres Fidélité</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fichier CSV/Excel</label>
                        <input type="file" class="form-control" name="file" accept=".csv,.xlsx,.xls" required>
                        <div class="form-text">Formats acceptés: CSV, Excel (.xlsx, .xls)</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="skip_first_row" id="skipFirstRow" checked>
                            <label class="form-check-label" for="skipFirstRow">
                                Ignorer la première ligne (en-têtes)
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" onclick="submitImport()">Démarrer Import</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Export -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Exporter des Données</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="exportForm">
                    <div class="mb-3">
                        <label class="form-label">Type de données</label>
                        <select class="form-select" name="data_type" required>
                            <option value="">Sélectionner le type</option>
                            <option value="menu">Menu & Articles</option>
                            <option value="customers">Clients</option>
                            <option value="orders">Commandes</option>
                            <option value="inventory">Inventaire</option>
                            <option value="analytics">Données Analytiques</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Format</label>
                        <select class="form-select" name="format" required>
                            <option value="csv">CSV</option>
                            <option value="xlsx">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Date de début</label>
                                <input type="date" class="form-control" name="date_from">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Date de fin</label>
                                <input type="date" class="form-control" name="date_to">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="submitExport()">Générer Export</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function refreshJobs() {
    location.reload();
}

function filterJobs() {
    const filter = document.getElementById('filterType').value;
    const url = new URL(window.location);
    if (filter) {
        url.searchParams.set('type', filter);
    } else {
        url.searchParams.delete('type');
    }
    window.location = url;
}

function submitImport() {
    const form = document.getElementById('importForm');
    const formData = new FormData(form);

    fetch('/admin/import-export/import', {
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
            alert('Erreur lors du démarrage de l\'import: ' + data.message);
        }
    })
    .catch(error => {
        alert('Erreur de connexion');
    });
}

function submitExport() {
    const form = document.getElementById('exportForm');
    const formData = new FormData(form);

    fetch('/admin/import-export/export', {
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
            alert('Erreur lors du démarrage de l\'export: ' + data.message);
        }
    })
    .catch(error => {
        alert('Erreur de connexion');
    });
}

function quickExport(type) {
    fetch('/admin/import-export/quick-export', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            data_type: type,
            format: 'xlsx'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Export démarré ! Vous recevrez une notification quand il sera prêt.');
            setTimeout(() => location.reload(), 2000);
        } else {
            alert('Erreur lors du démarrage de l\'export');
        }
    });
}

function viewJobDetails(id) {
    window.location.href = `/admin/import-export/jobs/${id}`;
}

function retryJob(id) {
    if (confirm('Relancer cette tâche ?')) {
        fetch(`/admin/import-export/jobs/${id}/retry`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la relance');
            }
        });
    }
}

function deleteJob(id) {
    if (confirm('Supprimer cette tâche de l\'historique ?')) {
        fetch(`/admin/import-export/jobs/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la suppression');
            }
        });
    }
}
</script>
@endpush

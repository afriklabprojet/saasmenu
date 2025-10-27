@extends('admin.layout.default')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-history text-primary me-2"></i>
                        Historique des Transactions
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.wallet') }}">Wallet</a>
                            </li>
                            <li class="breadcrumb-item active">Historique</li>
                        </ol>
                    </nav>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                    <i class="fas fa-filter me-2"></i>
                    Filtres
                </button>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="collapse {{ request()->has('filter') ? 'show' : '' }}" id="filtersCollapse">
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-control" name="type">
                            <option value="">Tous</option>
                            <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>Crédit</option>
                            <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>Débit</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-control" name="status">
                            <option value="">Tous</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Terminé</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Échoué</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Du</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Au</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary" name="filter" value="1">
                            <i class="fas fa-search me-2"></i>Filtrer
                        </button>
                        <a href="{{ route('admin.wallet.transactions') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Statistiques rapides -->
    @if($stats)
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Crédits
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_credits'], 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-plus-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Total Débits
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_debits'], 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-minus-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Balance Nette
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['net_balance'], 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-balance-scale fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Transactions
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_transactions'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Liste des Transactions -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Transactions
                @if(request('filter'))
                    ({{ $transactions->total() }} résultats)
                @endif
            </h6>

            @if($transactions->count() > 0)
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-download me-1"></i>Exporter
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.wallet.transactions', array_merge(request()->all(), ['export' => 'csv'])) }}">
                            <i class="fas fa-file-csv me-2"></i>CSV
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.wallet.transactions', array_merge(request()->all(), ['export' => 'pdf'])) }}">
                            <i class="fas fa-file-pdf me-2"></i>PDF
                        </a>
                    </li>
                </ul>
            </div>
            @endif
        </div>

        <div class="card-body p-0">
            @if($transactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Référence</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-end">Montant</th>
                            <th class="text-center">Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $transaction->created_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $transaction->created_at->format('H:i:s') }}</small>
                            </td>
                            <td>
                                <code>{{ $transaction->reference }}</code>
                            </td>
                            <td>
                                @if($transaction->type == 'credit')
                                <span class="badge bg-success">
                                    <i class="fas fa-plus me-1"></i>Crédit
                                </span>
                                @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-minus me-1"></i>Débit
                                </span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold">{{ $transaction->description }}</div>
                                @if($transaction->metadata)
                                <small class="text-muted">
                                    @if(isset($transaction->metadata['order_id']))
                                    Commande #{{ $transaction->metadata['order_id'] }}
                                    @endif
                                    @if(isset($transaction->metadata['withdrawal_id']))
                                    Retrait #{{ $transaction->metadata['withdrawal_id'] }}
                                    @endif
                                </small>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="fw-bold {{ $transaction->type == 'credit' ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->type == 'credit' ? '+' : '-' }}{{ number_format($transaction->amount, 0, ',', ' ') }} FCFA
                                </span>
                            </td>
                            <td class="text-center">
                                @switch($transaction->status)
                                    @case('completed')
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Terminé
                                        </span>
                                        @break
                                    @case('pending')
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i>En attente
                                        </span>
                                        @break
                                    @case('failed')
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times me-1"></i>Échoué
                                        </span>
                                        @break
                                @endswitch
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#transactionModal"
                                        onclick="showTransactionDetails({{ json_encode($transaction) }})">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($transactions->hasPages())
            <div class="card-footer">
                {{ $transactions->appends(request()->query())->links() }}
            </div>
            @endif

            @else
            <div class="text-center py-5">
                <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Aucune transaction trouvée</h4>
                @if(request('filter'))
                <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                <a href="{{ route('admin.wallet.transactions') }}" class="btn btn-outline-primary">
                    <i class="fas fa-times me-2"></i>Effacer les filtres
                </a>
                @else
                <p class="text-muted">Vos transactions apparaîtront ici dès que vous commencerez à recevoir des paiements</p>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Détails Transaction -->
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-receipt me-2"></i>
                    Détails de la Transaction
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="transactionDetails">
                <!-- Contenu dynamique -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="printTransaction()">
                    <i class="fas fa-print me-2"></i>Imprimer
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showTransactionDetails(transaction) {
    const statusBadges = {
        'completed': '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Terminé</span>',
        'pending': '<span class="badge bg-warning"><i class="fas fa-clock me-1"></i>En attente</span>',
        'failed': '<span class="badge bg-danger"><i class="fas fa-times me-1"></i>Échoué</span>'
    };

    const typeBadges = {
        'credit': '<span class="badge bg-success"><i class="fas fa-plus me-1"></i>Crédit</span>',
        'debit': '<span class="badge bg-danger"><i class="fas fa-minus me-1"></i>Débit</span>'
    };

    let metadataHtml = '';
    if (transaction.metadata) {
        metadataHtml = '<hr><h6>Informations supplémentaires</h6><ul>';
        Object.keys(transaction.metadata).forEach(key => {
            metadataHtml += `<li><strong>${key}:</strong> ${transaction.metadata[key]}</li>`;
        });
        metadataHtml += '</ul>';
    }

    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Informations générales</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Référence:</strong></td>
                        <td><code>${transaction.reference}</code></td>
                    </tr>
                    <tr>
                        <td><strong>Date:</strong></td>
                        <td>${new Date(transaction.created_at).toLocaleString('fr-FR')}</td>
                    </tr>
                    <tr>
                        <td><strong>Type:</strong></td>
                        <td>${typeBadges[transaction.type]}</td>
                    </tr>
                    <tr>
                        <td><strong>Statut:</strong></td>
                        <td>${statusBadges[transaction.status]}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Détails financiers</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Montant:</strong></td>
                        <td class="fw-bold ${transaction.type == 'credit' ? 'text-success' : 'text-danger'}">
                            ${transaction.type == 'credit' ? '+' : '-'}${new Intl.NumberFormat('fr-FR').format(transaction.amount)} FCFA
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Description:</strong></td>
                        <td>${transaction.description}</td>
                    </tr>
                    <tr>
                        <td><strong>Solde avant:</strong></td>
                        <td>${new Intl.NumberFormat('fr-FR').format(transaction.balance_before)} FCFA</td>
                    </tr>
                    <tr>
                        <td><strong>Solde après:</strong></td>
                        <td>${new Intl.NumberFormat('fr-FR').format(transaction.balance_after)} FCFA</td>
                    </tr>
                </table>
            </div>
        </div>
        ${metadataHtml}
    `;

    document.getElementById('transactionDetails').innerHTML = html;
}

function printTransaction() {
    window.print();
}

// Auto-submit form on date change
document.querySelectorAll('input[type="date"]').forEach(input => {
    input.addEventListener('change', function() {
        if (document.querySelector('input[name="date_from"]').value &&
            document.querySelector('input[name="date_to"]').value) {
            document.querySelector('button[name="filter"]').click();
        }
    });
});
</script>
@endsection

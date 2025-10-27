@extends('admin.layout.default')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-wallet text-primary me-2"></i>
                    Mon Wallet Restaurant
                </h1>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#withdrawalModal">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    Demander un Retrait
                </button>
            </div>
        </div>
    </div>

    <!-- Statistiques Wallet -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Solde Disponible
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($wallet->getAvailableBalance(), 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
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
                                Revenus du Mois
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($monthlyStats['earnings'], 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                                En Attente
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($wallet->pending_balance, 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Total Retiré
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($wallet->total_withdrawn, 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Dernières Transactions -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Dernières Transactions</h6>
                    <a href="{{ route('admin.wallet.transactions') }}" class="btn btn-sm btn-outline-primary">
                        Voir Tout
                    </a>
                </div>
                <div class="card-body">
                    @if($recentTransactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($transaction->type == 'credit')
                                        <span class="badge bg-success">Crédit</span>
                                        @else
                                        <span class="badge bg-danger">Débit</span>
                                        @endif
                                    </td>
                                    <td>{{ $transaction->description }}</td>
                                    <td class="text-end">
                                        {{ number_format($transaction->amount, 0, ',', ' ') }} FCFA
                                    </td>
                                    <td>
                                        @if($transaction->status == 'completed')
                                        <span class="badge bg-success">Terminé</span>
                                        @elseif($transaction->status == 'pending')
                                        <span class="badge bg-warning">En attente</span>
                                        @else
                                        <span class="badge bg-danger">Échoué</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucune transaction</h5>
                        <p class="text-muted">Vos transactions apparaîtront ici</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Moyens de Retrait et Demandes -->
        <div class="col-lg-4">
            <!-- Moyens de Retrait -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Moyens de Retrait</h6>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMethodModal">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="card-body">
                    @if($withdrawalMethods->count() > 0)
                    @foreach($withdrawalMethods as $method)
                    <div class="d-flex justify-content-between align-items-center mb-3 p-2 border rounded">
                        <div>
                            <strong>{{ $method->type_name }}</strong><br>
                            <small class="text-muted">{{ $method->account_number }}</small>
                        </div>
                        <div>
                            @if($method->is_verified)
                            <i class="fas fa-check-circle text-success"></i>
                            @else
                            <i class="fas fa-clock text-warning"></i>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="text-center">
                        <i class="fas fa-credit-card fa-2x text-muted mb-2"></i>
                        <p class="text-muted">Aucun moyen de retrait configuré</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Demandes de Retrait En Cours -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Retraits en Cours</h6>
                </div>
                <div class="card-body">
                    @if($pendingWithdrawals->count() > 0)
                    @foreach($pendingWithdrawals as $withdrawal)
                    <div class="mb-3 p-2 border rounded">
                        <div class="d-flex justify-content-between">
                            <strong>{{ number_format($withdrawal->amount, 0, ',', ' ') }} FCFA</strong>
                            <span class="badge bg-{{ $withdrawal->status_color }}">
                                {{ $withdrawal->status_text }}
                            </span>
                        </div>
                        <small class="text-muted">{{ $withdrawal->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    @endforeach
                    @else
                    <div class="text-center">
                        <i class="fas fa-hourglass-half fa-2x text-muted mb-2"></i>
                        <p class="text-muted">Aucun retrait en cours</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Demande de Retrait -->
<div class="modal fade" id="withdrawalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Demander un Retrait</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.wallet.withdraw') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Solde disponible: <strong>{{ number_format($wallet->getAvailableBalance(), 0, ',', ' ') }} FCFA</strong>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Montant à retirer (FCFA)</label>
                        <input type="number" class="form-control" name="amount" id="amount"
                               min="1000" max="{{ $wallet->getAvailableBalance() }}" required>
                        <div class="form-text">Minimum: 1,000 FCFA</div>
                    </div>

                    <div class="mb-3">
                        <label for="withdrawal_method_id" class="form-label">Moyen de retrait</label>
                        <select class="form-control" name="withdrawal_method_id" required>
                            <option value="">Choisir un moyen</option>
                            @foreach($withdrawalMethods->where('is_verified', true) as $method)
                            <option value="{{ $method->id }}">
                                {{ $method->type_name }} - {{ $method->account_number }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="alert alert-warning">
                        <small>
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Des frais de 2% (min. 100 FCFA) seront appliqués.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Confirmer le Retrait</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ajouter Moyen de Retrait -->
<div class="modal fade" id="addMethodModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Moyen de Retrait</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.wallet.add_method') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="type" class="form-label">Type de compte</label>
                        <select class="form-control" name="type" id="type" required>
                            <option value="">Choisir un type</option>
                            <option value="orange_money">Orange Money</option>
                            <option value="mtn_money">MTN Money</option>
                            <option value="moov_money">Moov Money</option>
                            <option value="bank_transfer">Virement Bancaire</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="account_number" class="form-label">Numéro de compte</label>
                        <input type="text" class="form-control" name="account_number" required
                               placeholder="Ex: 07 12 34 56 78">
                    </div>

                    <div class="mb-3">
                        <label for="account_name" class="form-label">Nom du titulaire</label>
                        <input type="text" class="form-control" name="account_name" required
                               placeholder="Nom complet du titulaire">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Calculer les frais en temps réel
document.getElementById('amount').addEventListener('input', function() {
    const amount = parseFloat(this.value) || 0;
    const fee = Math.max(100, amount * 0.02);
    const netAmount = amount - fee;

    // Vous pouvez afficher les frais calculés ici
    console.log('Montant:', amount, 'Frais:', fee, 'Net:', netAmount);
});
</script>
@endsection

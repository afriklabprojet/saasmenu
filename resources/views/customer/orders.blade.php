@extends('customer.layout')

@section('customer-content')
<div class="mb-4">
    <h2 class="mb-0">Mes Commandes</h2>
    <p class="text-muted">Suivez vos commandes et historique</p>
</div>

<!-- Filtres -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('customer.orders') }}" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Statut</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>En attente</option>
                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Acceptée</option>
                    <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>En préparation</option>
                    <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>En livraison</option>
                    <option value="5" {{ request('status') == '5' ? 'selected' : '' }}>Livrée</option>
                    <option value="6" {{ request('status') == '6' ? 'selected' : '' }}>Annulée</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="date_from" class="form-label">Date début</label>
                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">Date fin</label>
                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filtrer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Liste des Commandes -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        @if($orders->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>N° Commande</th>
                            <th>Restaurant</th>
                            <th>Date</th>
                            <th>Articles</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td><strong>#{{ $order->id }}</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($order->restorant && $order->restorant->logo)
                                        <img src="{{ asset($order->restorant->logo) }}" alt="{{ $order->restorant->name }}"
                                             class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                    @endif
                                    <div>
                                        <strong>{{ $order->restorant->name ?? 'N/A' }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $order->created_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $order->items->count() }} article(s)</span>
                            </td>
                            <td><strong>{{ number_format($order->order_price, 0, ',', ' ') }} XOF</strong></td>
                            <td>
                                @if($order->orderstatus_id == 1)
                                    <span class="badge bg-warning"><i class="fas fa-clock"></i> En attente</span>
                                @elseif($order->orderstatus_id == 2)
                                    <span class="badge bg-info"><i class="fas fa-check"></i> Acceptée</span>
                                @elseif($order->orderstatus_id == 3)
                                    <span class="badge bg-primary"><i class="fas fa-utensils"></i> En préparation</span>
                                @elseif($order->orderstatus_id == 4)
                                    <span class="badge bg-secondary"><i class="fas fa-truck"></i> En livraison</span>
                                @elseif($order->orderstatus_id == 5)
                                    <span class="badge bg-success"><i class="fas fa-check-circle"></i> Livrée</span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Annulée</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('customer.order.details', $order->id) }}"
                                       class="btn btn-outline-primary" title="Détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(in_array($order->orderstatus_id, [5]))
                                        <form action="{{ route('customer.order.reorder', $order->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Recommander">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if(in_array($order->orderstatus_id, [1, 2]))
                                        <button type="button" class="btn btn-outline-danger"
                                                onclick="confirmCancel({{ $order->id }})" title="Annuler">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer bg-white">
                {{ $orders->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">Aucune commande trouvée</h5>
                <p class="text-muted">Vous n'avez pas encore passé de commande</p>
                <a href="/" class="btn btn-primary mt-3">
                    <i class="fas fa-utensils me-2"></i>Commander maintenant
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Modal de Confirmation d'Annulation -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Annuler la commande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir annuler cette commande ?</p>
                <form id="cancelForm" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="cancel_reason" class="form-label">Raison de l'annulation</label>
                        <textarea name="reason" id="cancel_reason" class="form-control" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-danger" onclick="submitCancel()">
                    <i class="fas fa-times me-2"></i>Annuler la commande
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let cancelModal;
document.addEventListener('DOMContentLoaded', function() {
    cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
});

function confirmCancel(orderId) {
    document.getElementById('cancelForm').action = '/customer/orders/' + orderId + '/cancel';
    cancelModal.show();
}

function submitCancel() {
    document.getElementById('cancelForm').submit();
}
</script>
@endpush
@endsection

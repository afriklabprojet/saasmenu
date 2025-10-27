@extends('customer.layout')

@section('customer-content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="mb-0">Détails de la commande #{{ $order->id }}</h2>
        <p class="text-muted mb-0">Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
    </div>
    <a href="{{ route('customer.orders') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour
    </a>
</div>

<div class="row">
    <!-- Statut et Informations -->
    <div class="col-md-8">
        <!-- Statut de la Commande -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Statut de la Commande</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-1">Statut actuel</h6>
                        @if($order->orderstatus_id == 1)
                            <span class="badge bg-warning fs-6"><i class="fas fa-clock"></i> En attente de validation</span>
                        @elseif($order->orderstatus_id == 2)
                            <span class="badge bg-info fs-6"><i class="fas fa-check"></i> Acceptée par le restaurant</span>
                        @elseif($order->orderstatus_id == 3)
                            <span class="badge bg-primary fs-6"><i class="fas fa-utensils"></i> En cours de préparation</span>
                        @elseif($order->orderstatus_id == 4)
                            <span class="badge bg-secondary fs-6"><i class="fas fa-truck"></i> En cours de livraison</span>
                        @elseif($order->orderstatus_id == 5)
                            <span class="badge bg-success fs-6"><i class="fas fa-check-circle"></i> Livrée</span>
                        @else
                            <span class="badge bg-danger fs-6"><i class="fas fa-times-circle"></i> Annulée</span>
                        @endif
                    </div>
                    @if(in_array($order->orderstatus_id, [1, 2]))
                        <button type="button" class="btn btn-danger" onclick="confirmCancel()">
                            <i class="fas fa-times me-2"></i>Annuler
                        </button>
                    @endif
                </div>

                <!-- Timeline -->
                <div class="timeline mt-4">
                    <div class="timeline-item {{ $order->orderstatus_id >= 1 ? 'completed' : '' }}">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h6>Commande passée</h6>
                            <small class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small>
                        </div>
                    </div>
                    <div class="timeline-item {{ $order->orderstatus_id >= 2 ? 'completed' : '' }}">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h6>Acceptée</h6>
                        </div>
                    </div>
                    <div class="timeline-item {{ $order->orderstatus_id >= 3 ? 'completed' : '' }}">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h6>En préparation</h6>
                        </div>
                    </div>
                    <div class="timeline-item {{ $order->orderstatus_id >= 4 ? 'completed' : '' }}">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h6>En livraison</h6>
                        </div>
                    </div>
                    <div class="timeline-item {{ $order->orderstatus_id >= 5 ? 'completed' : '' }}">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h6>Livrée</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Articles Commandés -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Articles Commandés</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Article</th>
                                <th>Prix Unitaire</th>
                                <th>Quantité</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($item->item && $item->item->image)
                                            <img src="{{ asset($item->item->image) }}" alt="{{ $item->item->name }}"
                                                 class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                        @endif
                                        <div>
                                            <strong>{{ $item->item->name ?? 'Article supprimé' }}</strong>
                                            @if($item->extras)
                                                <br><small class="text-muted">{{ $item->extras }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ number_format($item->price, 0, ',', ' ') }} XOF</td>
                                <td><span class="badge bg-secondary">{{ $item->qty }}</span></td>
                                <td class="text-end"><strong>{{ number_format($item->price * $item->qty, 0, ',', ' ') }} XOF</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end"><strong>Sous-total</strong></td>
                                <td class="text-end"><strong>{{ number_format($order->order_price - $order->delivery_price, 0, ',', ' ') }} XOF</strong></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Frais de livraison</strong></td>
                                <td class="text-end"><strong>{{ number_format($order->delivery_price, 0, ',', ' ') }} XOF</strong></td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="3" class="text-end"><strong>Total</strong></td>
                                <td class="text-end"><h5 class="mb-0">{{ number_format($order->order_price, 0, ',', ' ') }} XOF</h5></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations Complémentaires -->
    <div class="col-md-4">
        <!-- Restaurant -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-store me-2"></i>Restaurant</h5>
            </div>
            <div class="card-body text-center">
                @if($order->restorant && $order->restorant->logo)
                    <img src="{{ asset($order->restorant->logo) }}" alt="{{ $order->restorant->name }}"
                         class="rounded mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                @endif
                <h6 class="mb-1">{{ $order->restorant->name ?? 'N/A' }}</h6>
                @if($order->restorant && $order->restorant->address)
                    <p class="text-muted small mb-0"><i class="fas fa-map-marker-alt"></i> {{ $order->restorant->address }}</p>
                @endif
                @if($order->restorant && $order->restorant->phone)
                    <p class="text-muted small mb-0"><i class="fas fa-phone"></i> {{ $order->restorant->phone }}</p>
                @endif
            </div>
        </div>

        <!-- Adresse de Livraison -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Livraison</h5>
            </div>
            <div class="card-body">
                <h6 class="mb-2">Adresse</h6>
                <p class="mb-2">{{ $order->address ?? 'N/A' }}</p>

                @if($order->client)
                    <h6 class="mb-2 mt-3">Contact</h6>
                    <p class="mb-1"><i class="fas fa-user"></i> {{ $order->client->name }}</p>
                    <p class="mb-0"><i class="fas fa-phone"></i> {{ $order->client->phone }}</p>
                @endif
            </div>
        </div>

        <!-- Mode de Paiement -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Paiement</h5>
            </div>
            <div class="card-body">
                <h6 class="mb-2">Méthode</h6>
                <p class="mb-0">
                    @if($order->payment_method == 'cod')
                        <span class="badge bg-secondary"><i class="fas fa-money-bill"></i> Paiement à la livraison</span>
                    @elseif($order->payment_method == 'card')
                        <span class="badge bg-primary"><i class="fas fa-credit-card"></i> Carte bancaire</span>
                    @elseif($order->payment_method == 'cinetpay')
                        <span class="badge bg-info"><i class="fas fa-wallet"></i> CinetPay</span>
                    @else
                        <span class="badge bg-secondary">{{ $order->payment_method }}</span>
                    @endif
                </p>
            </div>
        </div>

        <!-- Actions -->
        @if($order->orderstatus_id == 5)
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h6 class="mb-3">Vous avez aimé ?</h6>
                <form action="{{ route('customer.order.reorder', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success w-100 mb-2">
                        <i class="fas fa-redo me-2"></i>Recommander
                    </button>
                </form>
            </div>
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
            <form action="{{ route('customer.order.cancel', $order->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir annuler cette commande ?</p>
                    <div class="mb-3">
                        <label for="cancel_reason" class="form-label">Raison de l'annulation</label>
                        <textarea name="reason" id="cancel_reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i>Annuler la commande
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 40px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}
.timeline-item {
    position: relative;
    padding-bottom: 20px;
}
.timeline-marker {
    position: absolute;
    left: -33px;
    top: 5px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #e9ecef;
    border: 2px solid #dee2e6;
}
.timeline-item.completed .timeline-marker {
    background: #198754;
    border-color: #198754;
}
.timeline-item.completed .timeline-marker::after {
    content: '✓';
    color: white;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 10px;
}
</style>
@endpush

@push('scripts')
<script>
let cancelModal;
document.addEventListener('DOMContentLoaded', function() {
    cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
});

function confirmCancel() {
    cancelModal.show();
}
</script>
@endpush
@endsection

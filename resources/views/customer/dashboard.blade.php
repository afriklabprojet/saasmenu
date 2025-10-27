@extends('customer.layout')

@section('customer-content')
<div class="mb-4">
    <h2 class="mb-0">Tableau de bord</h2>
    <p class="text-muted">Bienvenue, {{ $user->name }} !</p>
</div>

<!-- Statistiques -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center shadow-sm border-0 bg-primary text-white">
            <div class="card-body">
                <i class="fas fa-shopping-bag fa-2x mb-2"></i>
                <h3 class="mb-0">{{ $stats['total_orders'] }}</h3>
                <small>Total Commandes</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center shadow-sm border-0 bg-warning text-white">
            <div class="card-body">
                <i class="fas fa-clock fa-2x mb-2"></i>
                <h3 class="mb-0">{{ $stats['pending_orders'] }}</h3>
                <small>En cours</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center shadow-sm border-0 bg-success text-white">
            <div class="card-body">
                <i class="fas fa-check-circle fa-2x mb-2"></i>
                <h3 class="mb-0">{{ $stats['completed_orders'] }}</h3>
                <small>Complétées</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center shadow-sm border-0 bg-info text-white">
            <div class="card-body">
                <i class="fas fa-coins fa-2x mb-2"></i>
                <h3 class="mb-0">{{ number_format($stats['total_spent'], 0, ',', ' ') }} XOF</h3>
                <small>Total Dépensé</small>
            </div>
        </div>
    </div>
</div>

<!-- Dernières Commandes -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Dernières Commandes</h5>
        <a href="{{ route('customer.orders') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
    </div>
    <div class="card-body p-0">
        @if($recentOrders->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>N° Commande</th>
                            <th>Restaurant</th>
                            <th>Date</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                        <tr>
                            <td><strong>#{{ $order->id }}</strong></td>
                            <td>{{ $order->restorant->name ?? 'N/A' }}</td>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td><strong>{{ number_format($order->order_price, 0, ',', ' ') }} XOF</strong></td>
                            <td>
                                @if($order->orderstatus_id == 1)
                                    <span class="badge bg-warning">En attente</span>
                                @elseif($order->orderstatus_id == 2)
                                    <span class="badge bg-info">Acceptée</span>
                                @elseif($order->orderstatus_id == 3)
                                    <span class="badge bg-primary">En préparation</span>
                                @elseif($order->orderstatus_id == 4)
                                    <span class="badge bg-secondary">En livraison</span>
                                @elseif($order->orderstatus_id == 5)
                                    <span class="badge bg-success">Livrée</span>
                                @else
                                    <span class="badge bg-danger">Annulée</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('customer.order.details', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucune commande pour le moment</p>
                <a href="/" class="btn btn-primary">Commander maintenant</a>
            </div>
        @endif
    </div>
</div>

<div class="row">
    <!-- Mes Adresses -->
    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Mes Adresses</h5>
                <a href="{{ route('customer.addresses') }}" class="btn btn-sm btn-outline-primary">Gérer</a>
            </div>
            <div class="card-body">
                @if($addresses->count() > 0)
                    @foreach($addresses as $address)
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    {{ $address->address_name }}
                                    @if($address->is_default)
                                        <span class="badge bg-primary badge-sm">Par défaut</span>
                                    @endif
                                </h6>
                                <p class="text-muted small mb-1">{{ $address->address }}</p>
                                <p class="text-muted small mb-0"><i class="fas fa-phone"></i> {{ $address->phone }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-map-marker-alt fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Aucune adresse enregistrée</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Mes Favoris -->
    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-heart me-2"></i>Mes Favoris</h5>
                <a href="{{ route('customer.wishlist') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body">
                <div class="text-center py-4">
                    <i class="fas fa-heart fa-3x text-danger mb-3"></i>
                    <h4 class="mb-2">{{ $wishlistCount }}</h4>
                    <p class="text-muted mb-0">Produit(s) favori(s)</p>
                    @if($wishlistCount > 0)
                        <a href="{{ route('customer.wishlist') }}" class="btn btn-outline-danger btn-sm mt-3">Voir ma wishlist</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

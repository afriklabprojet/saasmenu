@extends('customer.layout')

@section('customer-content')
<div class="mb-4">
    <h2 class="mb-0">Ma Wishlist</h2>
    <p class="text-muted">Vos produits favoris ({{ $wishlistItems->count() }} article(s))</p>
</div>

@if($wishlistItems->count() > 0)
    <div class="row">
        @foreach($wishlistItems as $wishlistItem)
            @php
                $item = $wishlistItem->item;
            @endphp
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card shadow-sm h-100">
                    <!-- Image du Produit -->
                    <div class="position-relative">
                        @if($item && $item->image)
                            <img src="{{ asset($item->image) }}" class="card-img-top" alt="{{ $item->name }}"
                                 style="height: 200px; object-fit: cover;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-utensils fa-3x text-muted"></i>
                            </div>
                        @endif

                        <!-- Bouton Supprimer -->
                        <form action="{{ route('customer.wishlist.remove', $wishlistItem->id) }}" method="POST" class="position-absolute top-0 end-0 m-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm rounded-circle" title="Retirer des favoris">
                                <i class="fas fa-heart-broken"></i>
                            </button>
                        </form>

                        <!-- Badge Disponibilité -->
                        @if($item && $item->available == 0)
                            <span class="position-absolute bottom-0 start-0 m-2 badge bg-danger">Indisponible</span>
                        @endif
                    </div>

                    <div class="card-body d-flex flex-column">
                        <!-- Nom du Restaurant -->
                        @if($item && $item->restaurant)
                            <p class="text-muted small mb-1">
                                <i class="fas fa-store me-1"></i>{{ $item->restaurant->name }}
                            </p>
                        @endif

                        <!-- Nom du Produit -->
                        <h6 class="card-title mb-2">{{ $item->name ?? 'Produit supprimé' }}</h6>

                        <!-- Description -->
                        @if($item && $item->description)
                            <p class="card-text text-muted small mb-3" style="height: 40px; overflow: hidden;">
                                {{ Str::limit($item->description, 80) }}
                            </p>
                        @endif

                        <!-- Prix -->
                        <div class="mt-auto">
                            @if($item)
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h5 class="mb-0 text-primary">{{ number_format($item->price, 0, ',', ' ') }} XOF</h5>
                                    </div>
                                    @if($item->available == 1)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Disponible
                                        </span>
                                    @endif
                                </div>

                                <!-- Boutons d'Action -->
                                <div class="d-grid gap-2">
                                    @if($item->available == 1)
                                        <a href="{{ route('vendor', ['restaurant' => $item->restaurant_id]) }}#item-{{ $item->id }}"
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-shopping-cart me-1"></i>Ajouter au panier
                                        </a>
                                    @else
                                        <button class="btn btn-secondary btn-sm" disabled>
                                            <i class="fas fa-times me-1"></i>Indisponible
                                        </button>
                                    @endif

                                    <a href="{{ route('vendor', ['restaurant' => $item->restaurant_id]) }}"
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>Voir le restaurant
                                    </a>
                                </div>
                            @else
                                <div class="alert alert-warning mb-0">
                                    <small><i class="fas fa-exclamation-triangle"></i> Ce produit n'est plus disponible</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Actions Groupées -->
    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0"><i class="fas fa-heart text-danger me-2"></i>{{ $wishlistItems->count() }} produit(s) dans votre wishlist</h6>
                </div>
                <div>
                    <form action="{{ route('customer.wishlist.clear') }}" method="POST"
                          onsubmit="return confirm('Êtes-vous sûr de vouloir vider votre wishlist ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-trash me-2"></i>Vider la wishlist
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-heart-crack fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">Votre wishlist est vide</h5>
            <p class="text-muted">Ajoutez vos produits préférés pour les retrouver facilement</p>
            <a href="/" class="btn btn-primary mt-3">
                <i class="fas fa-utensils me-2"></i>Découvrir les restaurants
            </a>
        </div>
    </div>
@endif

@endsection

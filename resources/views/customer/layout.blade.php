@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <!-- User Info -->
                    <div class="text-center p-4 border-bottom">
                        @if(auth()->user()->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="rounded-circle mb-3" width="80" height="80" style="object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 32px;">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <h6 class="mb-1">{{ auth()->user()->name }}</h6>
                        <small class="text-muted">{{ auth()->user()->email }}</small>
                    </div>

                    <!-- Navigation Menu -->
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <a href="{{ route('customer.dashboard') }}" class="text-decoration-none d-flex align-items-center {{ request()->routeIs('customer.dashboard') ? 'text-primary fw-bold' : 'text-dark' }}">
                                <i class="fas fa-th-large me-2"></i> Tableau de bord
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="{{ route('customer.orders') }}" class="text-decoration-none d-flex align-items-center {{ request()->routeIs('customer.orders*') ? 'text-primary fw-bold' : 'text-dark' }}">
                                <i class="fas fa-shopping-bag me-2"></i> Mes commandes
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="{{ route('customer.addresses') }}" class="text-decoration-none d-flex align-items-center {{ request()->routeIs('customer.addresses') ? 'text-primary fw-bold' : 'text-dark' }}">
                                <i class="fas fa-map-marker-alt me-2"></i> Mes adresses
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="{{ route('customer.wishlist') }}" class="text-decoration-none d-flex align-items-center {{ request()->routeIs('customer.wishlist') ? 'text-primary fw-bold' : 'text-dark' }}">
                                <i class="fas fa-heart me-2"></i> Mes favoris
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="{{ route('customer.profile') }}" class="text-decoration-none d-flex align-items-center {{ request()->routeIs('customer.profile') ? 'text-primary fw-bold' : 'text-dark' }}">
                                <i class="fas fa-user me-2"></i> Mon profil
                            </a>
                        </li>
                        <li class="list-group-item">
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-link text-danger text-decoration-none d-flex align-items-center p-0 w-100 text-start">
                                    <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('customer-content')
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .list-group-item {
        border-left: none;
        border-right: none;
    }
    .list-group-item:first-child {
        border-top: none;
    }
    .list-group-item:last-child {
        border-bottom: none;
    }
    .list-group-item a:hover {
        color: var(--bs-primary) !important;
    }
</style>
@endsection

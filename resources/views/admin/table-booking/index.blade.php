@extends('admin.layout.app')

@section('page-title')
    {{ __('Réservations de tables') }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">{{ __('Liste des réservations') }}</h3>
                        <a href="{{ route('admin.table-booking.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> {{ __('Nouvelle réservation') }}
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filtres -->
                    <form method="GET" action="{{ route('admin.table-booking.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="status" class="form-control">
                                    <option value="">{{ __('Tous les statuts') }}</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('En attente') }}</option>
                                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>{{ __('Confirmé') }}</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Annulé') }}</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('Terminé') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="{{ __('Date de début') }}">
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="{{ __('Date de fin') }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('Rechercher...') }}">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-secondary btn-block">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    @if($bookings->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('ID') }}</th>
                                    <th>{{ __('Restaurant') }}</th>
                                    <th>{{ __('Client') }}</th>
                                    <th>{{ __('Date & Heure') }}</th>
                                    <th>{{ __('Personnes') }}</th>
                                    <th>{{ __('Statut') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bookings as $booking)
                                <tr>
                                    <td>{{ $booking->id }}</td>
                                    <td>{{ $booking->vendor->name ?? '-' }}</td>
                                    <td>
                                        <div>{{ $booking->customer_name }}</div>
                                        <small class="text-muted">{{ $booking->customer_email }}</small><br>
                                        <small class="text-muted">{{ $booking->customer_phone }}</small>
                                    </td>
                                    <td>{{ $booking->formatted_date_time }}</td>
                                    <td>{{ $booking->guests_count }}</td>
                                    <td>
                                        <span class="badge badge-{{ $booking->status_badge }}">
                                            @if($booking->status == 'pending') {{ __('En attente') }}
                                            @elseif($booking->status == 'confirmed') {{ __('Confirmé') }}
                                            @elseif($booking->status == 'cancelled') {{ __('Annulé') }}
                                            @elseif($booking->status == 'completed') {{ __('Terminé') }}
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.table-booking.show', $booking) }}" class="btn btn-sm btn-info" title="{{ __('Voir') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.table-booking.edit', $booking) }}" class="btn btn-sm btn-warning" title="{{ __('Modifier') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.table-booking.destroy', $booking) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cette réservation ?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="{{ __('Supprimer') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $bookings->links() }}
                    </div>
                    @else
                    <div class="alert alert-info">
                        {{ __('Aucune réservation trouvée.') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

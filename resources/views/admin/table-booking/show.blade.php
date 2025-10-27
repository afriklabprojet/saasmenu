@extends('admin.layout.app')

@section('page-title')
    {{ __('Détails de la réservation') }} #{{ $tableBooking->id }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">{{ __('Réservation') }} #{{ $tableBooking->id }}</h3>
                        <div>
                            <a href="{{ route('admin.table-booking.edit', $tableBooking) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> {{ __('Modifier') }}
                            </a>
                            <a href="{{ route('admin.table-booking.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> {{ __('Retour') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Informations du restaurant -->
                        <div class="col-md-6">
                            <h5>{{ __('Restaurant') }}</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">{{ __('Nom') }}</th>
                                    <td>{{ $tableBooking->vendor->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Email') }}</th>
                                    <td>{{ $tableBooking->vendor->email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Téléphone') }}</th>
                                    <td>{{ $tableBooking->vendor->mobile ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Informations du client -->
                        <div class="col-md-6">
                            <h5>{{ __('Client') }}</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">{{ __('Nom') }}</th>
                                    <td>{{ $tableBooking->customer_name }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Email') }}</th>
                                    <td>{{ $tableBooking->customer_email }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Téléphone') }}</th>
                                    <td>{{ $tableBooking->customer_phone }}</td>
                                </tr>
                                @if($tableBooking->user)
                                <tr>
                                    <th>{{ __('Compte utilisateur') }}</th>
                                    <td>
                                        <span class="badge badge-success">{{ __('Enregistré') }}</span>
                                        (ID: {{ $tableBooking->user->id }})
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <!-- Détails de la réservation -->
                        <div class="col-md-6">
                            <h5>{{ __('Détails de la réservation') }}</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">{{ __('Date') }}</th>
                                    <td>{{ $tableBooking->booking_date->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Heure') }}</th>
                                    <td>{{ $tableBooking->booking_time->format('H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Nombre de personnes') }}</th>
                                    <td>{{ $tableBooking->guests_count }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Statut') }}</th>
                                    <td>
                                        <span class="badge badge-{{ $tableBooking->status_badge }}">
                                            @if($tableBooking->status == 'pending') {{ __('En attente') }}
                                            @elseif($tableBooking->status == 'confirmed') {{ __('Confirmé') }}
                                            @elseif($tableBooking->status == 'cancelled') {{ __('Annulé') }}
                                            @elseif($tableBooking->status == 'completed') {{ __('Terminé') }}
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Changement rapide de statut -->
                        <div class="col-md-6">
                            <h5>{{ __('Changer le statut') }}</h5>
                            <form action="{{ route('admin.table-booking.update-status', $tableBooking) }}" method="POST">
                                @csrf
                                @method('PATCH')

                                <div class="form-group">
                                    <label for="status">{{ __('Nouveau statut') }}</label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="pending" {{ $tableBooking->status == 'pending' ? 'selected' : '' }}>{{ __('En attente') }}</option>
                                        <option value="confirmed" {{ $tableBooking->status == 'confirmed' ? 'selected' : '' }}>{{ __('Confirmé') }}</option>
                                        <option value="cancelled" {{ $tableBooking->status == 'cancelled' ? 'selected' : '' }}>{{ __('Annulé') }}</option>
                                        <option value="completed" {{ $tableBooking->status == 'completed' ? 'selected' : '' }}>{{ __('Terminé') }}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="admin_notes">{{ __('Notes administratives') }}</label>
                                    <textarea name="admin_notes" id="admin_notes" rows="3" class="form-control" placeholder="{{ __('Ajouter une note...') }}">{{ $tableBooking->admin_notes }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ __('Mettre à jour le statut') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    @if($tableBooking->special_requests)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>{{ __('Demandes spéciales') }}</h5>
                            <div class="alert alert-info">
                                {{ $tableBooking->special_requests }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($tableBooking->admin_notes)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>{{ __('Notes administratives') }}</h5>
                            <div class="alert alert-secondary">
                                {{ $tableBooking->admin_notes }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-12">
                            <small class="text-muted">
                                {{ __('Créée le') }}: {{ $tableBooking->created_at->format('d/m/Y H:i') }}
                                @if($tableBooking->updated_at != $tableBooking->created_at)
                                | {{ __('Modifiée le') }}: {{ $tableBooking->updated_at->format('d/m/Y H:i') }}
                                @endif
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <form action="{{ route('admin.table-booking.destroy', $tableBooking) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer cette réservation ?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> {{ __('Supprimer la réservation') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('admin.layout.app')

@section('page-title')
    {{ __('Nouvelle réservation') }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">{{ __('Créer une réservation') }}</h3>
                        <a href="{{ route('admin.table-booking.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('Retour') }}
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.table-booking.store') }}" method="POST">
                    @csrf

                    <div class="card-body">
                        @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vendor_id">{{ __('Restaurant') }} <span class="text-danger">*</span></label>
                                    <select name="vendor_id" id="vendor_id" class="form-control @error('vendor_id') is-invalid @enderror" required>
                                        <option value="">{{ __('Sélectionner un restaurant') }}</option>
                                        @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('vendor_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">{{ __('Statut') }} <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>{{ __('En attente') }}</option>
                                        <option value="confirmed" {{ old('status') == 'confirmed' ? 'selected' : '' }}>{{ __('Confirmé') }}</option>
                                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Annulé') }}</option>
                                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>{{ __('Terminé') }}</option>
                                    </select>
                                    @error('status')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3">{{ __('Informations du client') }}</h5>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="customer_name">{{ __('Nom complet') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="customer_name" id="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name') }}" required>
                                    @error('customer_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="customer_email">{{ __('Email') }} <span class="text-danger">*</span></label>
                                    <input type="email" name="customer_email" id="customer_email" class="form-control @error('customer_email') is-invalid @enderror" value="{{ old('customer_email') }}" required>
                                    @error('customer_email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="customer_phone">{{ __('Téléphone') }} <span class="text-danger">*</span></label>
                                    <input type="tel" name="customer_phone" id="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" value="{{ old('customer_phone') }}" required>
                                    @error('customer_phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3">{{ __('Détails de la réservation') }}</h5>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="booking_date">{{ __('Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="booking_date" id="booking_date" class="form-control @error('booking_date') is-invalid @enderror" value="{{ old('booking_date') }}" min="{{ date('Y-m-d') }}" required>
                                    @error('booking_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="booking_time">{{ __('Heure') }} <span class="text-danger">*</span></label>
                                    <input type="time" name="booking_time" id="booking_time" class="form-control @error('booking_time') is-invalid @enderror" value="{{ old('booking_time') }}" required>
                                    @error('booking_time')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="guests_count">{{ __('Nombre de personnes') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="guests_count" id="guests_count" class="form-control @error('guests_count') is-invalid @enderror" value="{{ old('guests_count', 2) }}" min="1" max="50" required>
                                    @error('guests_count')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="special_requests">{{ __('Demandes spéciales') }}</label>
                                    <textarea name="special_requests" id="special_requests" rows="3" class="form-control @error('special_requests') is-invalid @enderror" placeholder="{{ __('Allergies, préférences, occasions spéciales...') }}">{{ old('special_requests') }}</textarea>
                                    @error('special_requests')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="admin_notes">{{ __('Notes administratives') }}</label>
                                    <textarea name="admin_notes" id="admin_notes" rows="3" class="form-control @error('admin_notes') is-invalid @enderror" placeholder="{{ __('Notes internes non visibles par le client') }}">{{ old('admin_notes') }}</textarea>
                                    @error('admin_notes')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ __('Créer la réservation') }}
                        </button>
                        <a href="{{ route('admin.table-booking.index') }}" class="btn btn-secondary">
                            {{ __('Annuler') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">{{ __('Réserver une table') }}</h3>
                    <p class="mb-0">{{ $vendor->name }}</p>
                </div>

                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('customer.table-booking.store', $vendor->unique_slug) }}" method="POST">
                        @csrf

                        <h5 class="mb-3">{{ __('Vos informations') }}</h5>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="customer_name">{{ __('Nom complet') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="customer_name" id="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name', auth()->user()->name ?? '') }}" required placeholder="{{ __('Votre nom complet') }}">
                                    @error('customer_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_email">{{ __('Email') }} <span class="text-danger">*</span></label>
                                    <input type="email" name="customer_email" id="customer_email" class="form-control @error('customer_email') is-invalid @enderror" value="{{ old('customer_email', auth()->user()->email ?? '') }}" required placeholder="{{ __('votre@email.com') }}">
                                    @error('customer_email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_phone">{{ __('Téléphone') }} <span class="text-danger">*</span></label>
                                    <input type="tel" name="customer_phone" id="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" value="{{ old('customer_phone', auth()->user()->mobile ?? '') }}" required placeholder="{{ __('+225 XX XX XX XX XX') }}">
                                    @error('customer_phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">{{ __('Détails de votre réservation') }}</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="booking_date">{{ __('Date de réservation') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="booking_date" id="booking_date" class="form-control @error('booking_date') is-invalid @enderror" value="{{ old('booking_date') }}" min="{{ date('Y-m-d') }}" required>
                                    @error('booking_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="booking_time">{{ __('Heure souhaitée') }} <span class="text-danger">*</span></label>
                                    <input type="time" name="booking_time" id="booking_time" class="form-control @error('booking_time') is-invalid @enderror" value="{{ old('booking_time') }}" required>
                                    @error('booking_time')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        {{ __('Horaires d\'ouverture: 12h00 - 15h00 et 19h00 - 23h00') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="guests_count">{{ __('Nombre de personnes') }} <span class="text-danger">*</span></label>
                                    <select name="guests_count" id="guests_count" class="form-control @error('guests_count') is-invalid @enderror" required>
                                        <option value="">{{ __('Sélectionner...') }}</option>
                                        @for($i = 1; $i <= 20; $i++)
                                        <option value="{{ $i }}" {{ old('guests_count') == $i ? 'selected' : '' }}>
                                            {{ $i }} {{ $i == 1 ? __('personne') : __('personnes') }}
                                        </option>
                                        @endfor
                                        <option value="20+" {{ old('guests_count') == '20+' ? 'selected' : '' }}>{{ __('Plus de 20 personnes (nous contacter)') }}</option>
                                    </select>
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
                                    <textarea name="special_requests" id="special_requests" rows="4" class="form-control @error('special_requests') is-invalid @enderror" placeholder="{{ __('Allergies, régime alimentaire, occasions spéciales, préférences de table...') }}">{{ old('special_requests') }}</textarea>
                                    @error('special_requests')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        {{ __('Mentionnez toute information qui nous aidera à mieux vous servir') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>{{ __('Important:') }}</strong>
                            {{ __('Votre réservation sera confirmée par le restaurant dans les plus brefs délais. Vous recevrez un email de confirmation.') }}
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-calendar-check"></i> {{ __('Envoyer ma réservation') }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card-footer text-center text-muted">
                    <small>
                        <i class="fas fa-shield-alt"></i>
                        {{ __('Vos données sont protégées et utilisées uniquement pour cette réservation') }}
                    </small>
                </div>
            </div>

            <!-- Informations du restaurant -->
            <div class="card mt-4 shadow-sm">
                <div class="card-body">
                    <h5>{{ __('Contact du restaurant') }}</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1">
                                <i class="fas fa-envelope text-primary"></i>
                                <strong>{{ __('Email:') }}</strong> {{ $vendor->email }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1">
                                <i class="fas fa-phone text-primary"></i>
                                <strong>{{ __('Téléphone:') }}</strong> {{ $vendor->mobile ?? __('Non renseigné') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

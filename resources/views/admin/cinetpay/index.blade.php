@extends('admin.layout.master')
@section('title')
    {{ __('CinetPay Settings') }}
@endsection
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __('CinetPay Settings') }}</h1>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.cinetpay.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('CinetPay Status') }}</label>
                                    <select class="form-control" name="status" required>
                                        <option value="active" {{ (isset($cinetpay) && $cinetpay->status == 'active') ? 'selected' : '' }}>
                                            {{ __('Active') }}
                                        </option>
                                        <option value="inactive" {{ (isset($cinetpay) && $cinetpay->status == 'inactive') ? 'selected' : '' }}>
                                            {{ __('Inactive') }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Environment') }}</label>
                                    <select class="form-control" name="environment" required>
                                        <option value="sandbox" {{ (isset($credentials['environment']) && $credentials['environment'] == 'sandbox') ? 'selected' : '' }}>
                                            {{ __('Sandbox (Test)') }}
                                        </option>
                                        <option value="production" {{ (isset($credentials['environment']) && $credentials['environment'] == 'production') ? 'selected' : '' }}>
                                            {{ __('Production (Live)') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('Site ID') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="site_id"
                                           value="{{ $credentials['site_id'] ?? '' }}"
                                           placeholder="{{ __('Enter your CinetPay Site ID') }}" required>
                                    <small class="form-text text-muted">{{ __('Your CinetPay Site ID from your dashboard') }}</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('API Key') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="api_key"
                                           value="{{ $credentials['api_key'] ?? '' }}"
                                           placeholder="{{ __('Enter your CinetPay API Key') }}" required>
                                    <small class="form-text text-muted">{{ __('Your CinetPay API Key from your dashboard') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{ __('Secret Key') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="secret_key"
                                           value="{{ $credentials['secret_key'] ?? '' }}"
                                           placeholder="{{ __('Enter your CinetPay Secret Key') }}" required>
                                    <small class="form-text text-muted">{{ __('Your CinetPay Secret Key for webhook verification') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> {{ __('Webhook Configuration') }}</h6>
                                    <p class="mb-1">{{ __('Configure the following webhook URL in your CinetPay dashboard:') }}</p>
                                    <code>{{ route('cinetpay.notify') }}</code>
                                    <p class="mt-2 mb-0">{{ __('This will allow CinetPay to send payment notifications to your application.') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle"></i> {{ __('Important Notes') }}</h6>
                                    <ul class="mb-0">
                                        <li>{{ __('Make sure to test your integration in sandbox mode before going live.') }}</li>
                                        <li>{{ __('CinetPay supports payments in XOF, XAF, USD, and EUR currencies.') }}</li>
                                        <li>{{ __('Keep your API credentials secure and never share them publicly.') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> {{ __('Update CinetPay Settings') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection

{{-- Custom Domain Settings Form --}}
<div class="row">
    <div class="col-12">
        <div class="card border-0 box-shadow">
            <div class="card-header bg-white border-0">
                <h5 class="fw-bold mb-0">🌐 {{ trans('labels.custom_domain') }}</h5>
            </div>
            <div class="card-body">
                @if (helper::getPlanInfo($vendor_id)->custom_domain == 1)
                    <form action="{{ URL::to('/admin/settings') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="custom_domain">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ trans('labels.custom_domain') }}</label>
                                    <input type="text" class="form-control" name="custom_domain"
                                        value="{{ $settingdata->custom_domain ?? '' }}" placeholder="exemple.com">
                                    <small class="form-text text-muted">
                                        {{ trans('labels.custom_domain_help') }}
                                    </small>
                                    @error('custom_domain')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ trans('labels.status') }}</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_custom_domain"
                                            value="1" id="is_custom_domain"
                                            {{ ($settingdata->is_custom_domain ?? 0) == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_custom_domain">
                                            {{ trans('labels.enable_custom_domain') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (!empty($settingdata->custom_domain))
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> {{ trans('labels.dns_configuration') }}</h6>
                                <p class="mb-2">{{ trans('labels.dns_help_text') }}</p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Type:</strong> CNAME<br>
                                        <strong>Name:</strong> www<br>
                                        <strong>Value:</strong> {{ env('APP_URL') }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Type:</strong> A<br>
                                        <strong>Name:</strong> @<br>
                                        <strong>Value:</strong> {{ request()->server('SERVER_ADDR') }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="form-group">
                            <button type="submit" class="btn btn-secondary">
                                {{ trans('labels.save') }}
                            </button>
                        </div>
                    </form>
                @else
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-lock"></i> {{ trans('labels.feature_not_available') }}</h6>
                        <p class="mb-0">{{ trans('labels.upgrade_plan_for_custom_domain') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

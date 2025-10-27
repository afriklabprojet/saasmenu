<div id="recaptcha" class="hidechild">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 box-shadow">
                <div class="card-header bg-transparent py-3 d-flex align-items-center text-dark">
                    <i class="fa-solid fa-shield-halved fs-5"></i>
                    <h5 class="px-2">{{ trans('labels.google_recaptcha') }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ URL::to('admin/settings/updaterecaptcha') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('labels.recaptcha_version') }} <span class="text-danger"> * </span></label>
                                    <select class="form-select" name="recaptcha_version" id="recaptcha_version" required>
                                        <option value="">{{ trans('labels.select') }}</option>
                                        <option value="v2" {{ @$settingdata->recaptcha_version == 'v2' ? 'selected' : '' }}>{{ trans('labels.v2') }}</option>
                                        <option value="v3" {{ @$settingdata->recaptcha_version == 'v3' ? 'selected' : '' }}>{{ trans('labels.v3') }}</option>
                                    </select>
                                    @error('recaptcha_version')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('labels.google_recaptcha_site_key') }} <span class="text-danger"> * </span></label>
                                    <input type="text" class="form-control" name="google_recaptcha_site_key" value="{{ @$settingdata->google_recaptcha_site_key }}" placeholder="{{ trans('labels.google_recaptcha_site_key') }}" required>
                                    @error('google_recaptcha_site_key')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('labels.google_recaptcha_secret_key') }} <span class="text-danger"> * </span></label>
                                    <input type="text" class="form-control" name="google_recaptcha_secret_key" value="{{ @$settingdata->google_recaptcha_secret_key }}" placeholder="{{ trans('labels.google_recaptcha_secret_key') }}" required>
                                    @error('google_recaptcha_secret_key')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6" id="score_threshold_div" style="display: {{ @$settingdata->recaptcha_version == 'v3' ? 'block' : 'none' }};">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('labels.score_threshold') }}</label>
                                    <input type="number" class="form-control" name="score_threshold" value="{{ @$settingdata->score_threshold }}" placeholder="{{ trans('labels.score_threshold') }}" step="0.1" min="0" max="1">
                                    @error('score_threshold')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">{{ trans('labels.score_threshold_help') }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('labels.cookie_text') }} <span class="text-danger"> * </span></label>
                                    <textarea class="form-control" name="cookie_text" rows="3" placeholder="{{ trans('labels.cookie_text') }}" required>{{ @$settingdata->cookie_text }}</textarea>
                                    @error('cookie_text')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('labels.cookie_button_text') }} <span class="text-danger"> * </span></label>
                                    <input type="text" class="form-control" name="cookie_button_text" value="{{ @$settingdata->cookie_button_text }}" placeholder="{{ trans('labels.cookie_button_text') }}" required>
                                    @error('cookie_button_text')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group text-end">
                            <button class="btn btn-secondary" type="submit">{{ trans('labels.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Show/hide score threshold based on reCAPTCHA version
        $('#recaptcha_version').on('change', function() {
            var version = $(this).val();
            if (version === 'v3') {
                $('#score_threshold_div').show();
            } else {
                $('#score_threshold_div').hide();
            }
        });
    });
</script>

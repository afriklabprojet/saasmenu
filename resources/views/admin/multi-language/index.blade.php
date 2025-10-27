@extends('admin.layout.default')
@section('content')
{{-- Admin page pour l'addon multi_language --}}

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="text-secondary fw-bold">
        {{ trans('labels.multi_language_management') }}
    </h5>
</div>

<div class="row">
    <!-- Langue actuelle -->
    <div class="col-lg-4">
        <div class="card border-0 box-shadow h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-globe text-primary fa-2x me-3"></i>
                    <div>
                        <h6 class="card-title mb-1">{{ trans('labels.current_language') }}</h6>
                        <p class="card-text text-muted">
                            {{ $supported_languages[app()->getLocale()]['name'] ?? 'Unknown' }}
                            {{ $supported_languages[app()->getLocale()]['flag'] ?? '' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Langues supportées -->
    <div class="col-lg-4">
        <div class="card border-0 box-shadow h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-language text-success fa-2x me-3"></i>
                    <div>
                        <h6 class="card-title mb-1">{{ trans('labels.supported_languages') }}</h6>
                        <p class="card-text text-muted">{{ count($supported_languages) }} langues</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statut addon -->
    <div class="col-lg-4">
        <div class="card border-0 box-shadow h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-check-circle text-warning fa-2x me-3"></i>
                    <div>
                        <h6 class="card-title mb-1">{{ trans('labels.addon_status') }}</h6>
                        <span class="badge bg-success">{{ trans('labels.active') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Configuration des langues -->
<div class="card border-0 box-shadow mt-4">
    <div class="card-header bg-white">
        <h6 class="mb-0">{{ trans('labels.language_configuration') }}</h6>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($supported_languages as $locale => $info)
            <div class="col-md-4 mb-3">
                <div class="card {{ app()->getLocale() == $locale ? 'border-primary' : '' }}">
                    <div class="card-body text-center">
                        <div class="h1 mb-3">{{ $info['flag'] }}</div>
                        <h6 class="card-title">{{ $info['name'] }}</h6>
                        <p class="text-muted small">
                            Direction: {{ $info['direction'] }}<br>
                            Code: {{ strtoupper($locale) }}
                        </p>
                        @if(app()->getLocale() == $locale)
                            <span class="badge bg-primary">{{ trans('labels.current') }}</span>
                        @else
                            <button class="btn btn-sm btn-outline-primary change-language" data-locale="{{ $locale }}">
                                {{ trans('labels.switch_to') }} {{ $info['name'] }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Sélecteur de langue en direct -->
<div class="card border-0 box-shadow mt-4">
    <div class="card-header bg-white">
        <h6 class="mb-0">{{ trans('labels.language_switcher') }}</h6>
    </div>
    <div class="card-body">
        <p class="text-muted">{{ trans('labels.language_switcher_description') }}</p>

        {{-- Inclusion du composant multi_language --}}
        @include('components.language-switcher')

        <div class="mt-3">
            <button class="btn btn-sm btn-success" onclick="testTranslations()">
                <i class="fa-solid fa-vial me-2"></i>{{ trans('labels.test_translations') }}
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Script pour l'addon multi_language
$(document).ready(function() {
    // Changement de langue
    $('.change-language').on('click', function() {
        const locale = $(this).data('locale');
        changeLanguage(locale);
    });
});

function changeLanguage(locale) {
    $.ajax({
        url: '{{ route("admin.localization.change") }}',
        type: 'POST',
        data: {
            locale: locale,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        },
        error: function() {
            toastr.error('{{ trans("labels.error_changing_language") }}');
        }
    });
}

function testTranslations() {
    $.ajax({
        url: '{{ route("admin.multi-language.test") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                console.log('Translations test:', response.translations);
                toastr.success('{{ trans("labels.translations_test_success") }}');
            }
        },
        error: function() {
            toastr.error('{{ trans("labels.translations_test_error") }}');
        }
    });
}
</script>
@endsection

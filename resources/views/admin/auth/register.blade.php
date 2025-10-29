@extends('admin.layout.auth_default')
@section('content')

    <body class="bg-white">
        <div class="wrapper">
            <section>
                <div class="row justify-content-center align-items-center g-0 h-100vh">
                    <div class="col-lg-6 col-xl-6 col-12 bg-white">
                        <div class="row horizontal-schroll g-0 vh-100 d-flex justify-content-center align-items-center">
                            <div class="col-md-8 col-lg-10 col-xl-7">
                                <div class="card overflow-hidden border-0 w-100 bg-transparent">
                                    <div class="card-body pt-4">
                                        <h4 class="fw-bold text-dark fs-1 pb-0 mb-0">{{ trans('labels.register') }}</h4>

                                        <div class="d-flex align-items-center py-3">
                                            <p class="fs-7 text-center fw-500 text-muted">
                                                {{ trans('labels.already_have_an_account') }}</p>
                                            <a href="{{ URL::to('/admin') }}"
                                                class="text-primary fw-semibold px-1">{{ trans('labels.login') }}</a>
                                        </div>
                                        <form class="my-3" method="POST" action="{{ URL::to('admin/register_vendor') }}">
                                            @csrf
                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <label for="store_name" class="form-label">Nom du
                                                        Restaurant/Commerce<span class="text-danger"> * </span></label>
                                                    <input type="text" class="form-control" name="store"
                                                        value="{{ old('store') }}" id="store_name"
                                                        placeholder="Ex: Restaurant Le Bon Goût" required>
                                                    @error('store')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="form-group col-md-6">

                                                    <label for="name" class="form-label">{{ trans('labels.name') }}<span
                                                            class="text-danger"> * </span></label>

                                                    @if (session()->has('social_login'))
                                                        <input type="text" class="form-control" name="name"
                                                            value="{{ session()->get('social_login')['name'] }}"
                                                            id="name" placeholder="{{ trans('labels.name') }}">
                                                    @else
                                                        <input type="text" class="form-control" name="name"
                                                            value="{{ old('name') }}" id="name"
                                                            placeholder="{{ trans('labels.name') }}" required>
                                                    @endif
                                                    @error('name')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="email"
                                                        class="form-label">{{ trans('labels.email') }}<span
                                                            class="text-danger"> * </span></label>
                                                    @if (session()->has('social_login'))
                                                        <input type="email" class="form-control" name="email"
                                                            value="{{ session()->get('social_login')['email'] }}"
                                                            id="email" placeholder="{{ trans('labels.email') }}"
                                                            required>
                                                    @else
                                                        <input type="email" class="form-control" name="email"
                                                            value="{{ old('email') }}" id="email"
                                                            placeholder="{{ trans('labels.email') }}" required>
                                                    @endif
                                                    @error('email')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="mobile"
                                                        class="form-label">{{ trans('labels.mobile') }}<span
                                                            class="text-danger"> * </span></label>
                                                    <input type="number" class="form-control" name="mobile"
                                                        value="{{ old('mobile') }}" id="mobile"
                                                        placeholder="{{ trans('labels.mobile') }}" required>
                                                    @error('mobile')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                @if (!session()->has('social_login'))
                                                    <div class="form-group col-md-6">
                                                        <label for="password"
                                                            class="form-label">{{ trans('labels.password') }}<span
                                                                class="text-danger"> * </span></label>
                                                        <input type="password" class="form-control" name="password"
                                                            value="{{ old('password') }}" id="password"
                                                            placeholder="{{ trans('labels.password') }}" required>
                                                        @error('password')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                @endif
                                                <div class="form-group col-md-6">
                                                    <label for="city"
                                                        class="form-label">{{ trans('labels.city') }}<span
                                                            class="text-danger"> * </span></label>
                                                    <select name="city" class="form-select" id="city" required>
                                                        <option value="">{{ trans('labels.select') }}</option>
                                                        @foreach ($cities as $city)
                                                            <option value="{{ $city->id }}">{{ $city->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="area"
                                                        class="form-label">{{ trans('labels.area') }}<span
                                                            class="text-danger"> * </span></label>
                                                    <select name="area" class="form-select" id="area" required>
                                                        <option value="">{{ trans('labels.select') }}</option>
                                                    </select>

                                                </div>
                                                @if (App\Models\SystemAddons::where('unique_identifier', 'unique_slug')->first() != null &&
                                                        App\Models\SystemAddons::where('unique_identifier', 'unique_slug')->first()->activated == 1)
                                                    <div class="form-group">
                                                        <label for="basic-url"
                                                            class="form-label">{{ trans('labels.personalized_link') }}<span
                                                                class="text-danger"> * </span></label>
                                                        <div class="input-group">
                                                            <span
                                                                class="input-group-text col-5 col-md-auto overflow-scroll fs-8">{{ URL::to('/') }}/</span>
                                                            <input type="text" class="form-control mb-0"
                                                                id="slug" name="slug"
                                                                value="{{ old('slug') }}"
                                                                pattern="^[a-z0-9]+(?:-[a-z0-9]+)*$"
                                                                title="Uniquement des lettres minuscules, chiffres et tirets. Pas d'espaces."
                                                                placeholder="mon-restaurant-123" required>
                                                        </div>
                                                        <small class="form-text text-muted">Exemple: mon-restaurant,
                                                            cafe-123, bistro-central</small>
                                                        @error('slug')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                @endif
                                                <div class="form-group my-3">
                                                    <input class="form-check-input" type="checkbox" value=""
                                                        name="check_terms" id="check_terms" checked required>
                                                    <label class="form-check-label" for="check_terms">
                                                        {{ trans('labels.i_accept_the') }} <span class="fw-600"><a
                                                                href="{{ URL::to('/terms_condition') }}"
                                                                target="_blank">{{ trans('labels.terms') }}</a> </span>
                                                    </label>
                                                </div>
                                            </div>

                                            @include('landing.layout.recaptcha')

                                            <button class="btn btn-primary w-100 mt-3"
                                                @if (env('Environment') == 'sendbox') type="button" onclick="myFunction()" @else type="submit" @endif>{{ trans('labels.register') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-xl-6 col-12 d-none d-lg-block">
                        <div class="vh-100 d-flex justify-content-center align-items-center m-auto">
                            <img src="{{ url(env('ASSETSPATHURL') . '/admin-assets/images/about/login.jpg') }}"
                                alt="" class="formimg">
                        </div>
                    </div>
                </div>
            </section>
        </div>
    @endsection
    @section('scripts')
        <script>
            var areaurl = "{{ URL::to('admin/getarea') }}";
            var select = "{{ trans('labels.select') }}";
            var areaid = '0';

            // Validation du slug en temps réel
            document.addEventListener('DOMContentLoaded', function() {
                const slugInput = document.getElementById('slug');
                if (slugInput) {
                    slugInput.addEventListener('input', function(e) {
                        // Supprimer les espaces et caractères non autorisés
                        let value = e.target.value.toLowerCase();
                        value = value.replace(/[^a-z0-9-]/g,
                        ''); // Supprimer tout sauf lettres, chiffres et tirets
                        value = value.replace(/--+/g,
                        '-'); // Remplacer plusieurs tirets consécutifs par un seul
                        value = value.replace(/^-|-$/g, ''); // Supprimer les tirets en début et fin
                        e.target.value = value;
                    });

                    // Validation finale avant soumission
                    slugInput.addEventListener('blur', function(e) {
                        const value = e.target.value;
                        const regex = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;
                        const errorDiv = document.getElementById('slug-error');

                        // Supprimer l'ancienne erreur
                        if (errorDiv) {
                            errorDiv.remove();
                        }

                        if (value && !regex.test(value)) {
                            const error = document.createElement('span');
                            error.id = 'slug-error';
                            error.className = 'text-danger';
                            error.textContent =
                                'Le lien doit contenir uniquement des lettres minuscules, chiffres et tirets. Pas d\'espaces.';
                            e.target.parentNode.parentNode.appendChild(error);
                        }
                    });
                }
            });
        </script>
        <script src="{{ url(env('ASSETSPATHURL') . '/admin-assets/js/user.js') }}"></script>
    @endsection

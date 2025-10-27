@extends('landing.layout.default')
<!-- IF VERSION 2  -->
@if (helper::appdata('')->recaptcha_version == 'v2')
    <script src='https://www.google.com/recaptcha/api.js'></script>
@endif
<!-- IF VERSION 3  -->
@if (helper::appdata('')->recaptcha_version == 'v3')
    {!! RecaptchaV3::initJs() !!}
@endif
@section('content')
    <main>
        <!--------------------------------- home-banner start --------------------------------->
        <section id="home" class="home-banner my-5">
            <div class="container">
                <div class="row align-items-center justify-content-between">
                    <div class="col-md-6 col-12">
                        <div class="banner-content me-xl-5 me-lg-3 me-md-2">
                            <h1 class="banner-title text-primary">{{ trans('landing.hero_banner_title') }}</h1>
                            <p class="fw-normal mb-lg-4 mb-3 text-muted fs-5">{{ trans('landing.hero_banner_description') }}</p>
                            <div class="d-flex flex-wrap gap-3 mb-lg-4 mb-3">
                                <a href="@if (env('Environment') == 'sendbox') {{ URL::to('/admin') }} @else {{ helper::appdata('')->vendor_register == 1 ?  URL::to('/admin/register') :  URL::to('/admin') }} @endif"
                                   class="btn btn-secondary btn-lg rounded-pill px-4 shadow-lg"
                                   target="_blank">
                                    <i class="fa-solid fa-rocket me-2"></i>{{ trans('landing.get_started') }} Gratuitement
                                </a>
                                <a href="#pricing-plans" class="btn btn-outline-primary btn-lg rounded-pill px-4">
                                    <i class="fa-solid fa-tag me-2"></i>Voir les Tarifs
                                </a>
                            </div>
                            <div class="d-flex align-items-center gap-4 text-muted small">
                                <div><i class="fa-solid fa-check-circle text-success me-1"></i> Pas de carte bancaire</div>
                                <div><i class="fa-solid fa-check-circle text-success me-1"></i> Installation en 5min</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-12 d-none d-md-block">
                        <img src="{{ url(env('ASSETSPATHURL') . 'landing/images/Photo.webp') }}" alt=""
                            class="img-fluid">
                    </div>
                </div>
            </div>
        </section>
        <!---------------------------------- home-banner end ---------------------------------->

        <!-------------------------------- work section start -------------------------------->
        <section class="work bg-primary mb-5">
            <div class="container">
                <div class="row align-items-end">
                    <div class="col-xl-4 col-lg-4 d-none d-lg-block">
                        <div class="work-img">
                            <img src="{{ url(env('ASSETSPATHURL') . 'landing/images/imag-1.webp') }}" class="w-100 img-fluid"
                                alt="imag-1">
                        </div>
                    </div>
                    <div class="col-xl-8 col-lg-8 col-md-12">
                        <div class="work-content ms-xl-5 ms-lg-4 px-3 sec-title mb-5">
                            <h2 class="text-white">{{ trans('landing.how_it_work') }}</h2>
                            <p class="sub-title text-white">{{ trans('landing.how_it_work_description') }}</p>
                        </div>
                        <div class="row ms-xl-5 ms-lg-4">
                            <div class="col-xl-4 col-lg-4 col-md-4 col-12 mb-4 mb-md-0">
                                <div class="card h-100 border-0 rounded-0 pb-xl-5">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <img class="card-img-top"
                                            src="{{ url(env('ASSETSPATHURL') . 'landing/images/png/signup.png') }}"
                                            alt="" class="rounded-circle">
                                        <div class="numbers">01</div>
                                    </div>
                                    <div class="card-body p-0 ms-3">
                                        <div class="border-start border-2 border-secondary-color ps-4 mb-xl-4 mb-lg-3">
                                            <h4 class="card-title">{{ trans('landing.how_it_work_step_one') }}</h4>
                                            <p class="card-text text-muted fs-7 text-truncate-2">
                                                {{ trans('landing.how_it_work_step_one_description') }}</p>
                                        </div>
                                    </div>
                                    <div class="card-footer border-0 bg-transparent">
                                        <a href="@if (env('Environment') == 'sendbox') {{ URL::to('/admin') }} @else {{ helper::appdata('')->vendor_register == 1 ?  URL::to('/admin/register') :  URL::to('/admin') }} @endif" class="border-bottom ms-4 fw-500 ms-lg-0 ms-xl-4"
                                            target="_blank">{{ trans('landing.get_started') }}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-12 mb-4 mb-md-0">
                                <div class="card h-100 border-0 rounded-0 pb-xl-5 bg-secondary">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <img class="card-img-top"
                                            src="{{ url(env('ASSETSPATHURL') . 'landing/images/png/add-product.png') }}"
                                            alt="" class="rounded-circle">
                                        <div class="numbers text-white">02</div>
                                    </div>
                                    <div class="card-body p-0 ms-3">
                                        <div class="border-start border-2 border-white ps-4 mb-xl-4 mb-lg-3">
                                            <h4 class="card-title text-white">{{ trans('landing.how_it_work_step_two') }}
                                            </h4>
                                            <p class="card-text fs-7 text-truncate-2 text-white">
                                                {{ trans('landing.how_it_work_step_two_description') }}</p>
                                        </div>
                                    </div>
                                    <div class="card-footer border-0 bg-transparent">
                                        <a href="@if (env('Environment') == 'sendbox') {{ URL::to('/admin') }} @else {{ helper::appdata('')->vendor_register == 1 ?  URL::to('/admin/register') :  URL::to('/admin') }} @endif"
                                            class="border-bottom ms-4 fw-500 ms-lg-0 ms-xl-4 text-white"
                                            target="_blank">{{ trans('landing.get_started') }}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-12 mb-4 mb-md-0">
                                <div class="card h-100 border-0 rounded-0 pb-xl-5">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <img class="card-img-top"
                                            src="{{ url(env('ASSETSPATHURL') . 'landing/images/png/ready.png') }}"
                                            alt="" class="rounded-circle">
                                        <div class="numbers">03</div>
                                    </div>
                                    <div class="card-body p-0 ms-3">
                                        <div class="border-start border-2 border-secondary-color ps-4 mb-xl-4 mb-lg-3">
                                            <h4 class="card-title">{{ trans('landing.how_it_work_step_three') }}</h4>
                                            <p class="card-text text-muted fs-7 text-truncate-2">
                                                {{ trans('landing.how_it_work_step_three_description') }}</p>
                                        </div>
                                    </div>
                                    <div class="card-footer border-0 bg-transparent">
                                        <a href="@if (env('Environment') == 'sendbox') {{ URL::to('/admin') }} @else {{ helper::appdata('')->vendor_register == 1 ?  URL::to('/admin/register') :  URL::to('/admin') }} @endif" class="border-bottom ms-4 fw-500 ms-lg-0 ms-xl-4"
                                            target="_blank">{{ trans('landing.get_started') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!---------------------------------- work section end ---------------------------------->

        <!--------------------------- Trust Badges section start --------------------------->
        <section class="trust-badges py-4 bg-white border-top border-bottom">
            <div class="container">
                <div class="row align-items-center justify-content-center g-4 text-center">
                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="trust-item">
                            <i class="fa-solid fa-shield-halved text-success fs-1 mb-2"></i>
                            <h6 class="fw-bold mb-1">100% Sécurisé</h6>
                            <small class="text-muted">SSL & RGPD</small>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="trust-item">
                            <i class="fa-solid fa-clock text-primary fs-1 mb-2"></i>
                            <h6 class="fw-bold mb-1">Support 24/7</h6>
                            <small class="text-muted">Assistance rapide</small>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="trust-item">
                            <i class="fa-solid fa-rocket text-info fs-1 mb-2"></i>
                            <h6 class="fw-bold mb-1">Setup en 5min</h6>
                            <small class="text-muted">Prêt à l'emploi</small>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="trust-item">
                            <i class="fa-solid fa-sync text-warning fs-1 mb-2"></i>
                            <h6 class="fw-bold mb-1">Mises à jour</h6>
                            <small class="text-muted">Automatiques</small>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="trust-item">
                            <i class="fa-solid fa-cloud text-danger fs-1 mb-2"></i>
                            <h6 class="fw-bold mb-1">Cloud Hosting</h6>
                            <small class="text-muted">99.9% uptime</small>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="trust-item">
                            <i class="fa-solid fa-money-bill-wave text-success fs-1 mb-2"></i>
                            <h6 class="fw-bold mb-1">Garantie 30j</h6>
                            <small class="text-muted">Satisfait ou remboursé</small>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--------------------------- Trust Badges section end --------------------------->

        <!--------------------------- Premium Features section start --------------------------->
        @if (count($features) > 0)
            <section id="features" class="premium-features-sec pb-5">
                <div class="container">
                    <div class="sec-title col-lg-6 text-strat mb-5">
                        <h2 class="">{{ trans('landing.premium_features') }}</h2>
                        <p class="sub-title">{{ trans('landing.premium_features_description') }}</p>
                    </div>
                    <div class="premium-features owl-carousel owl-theme">
                        @foreach ($features as $feature)
                            <div class="item px-2">
                                <div class="card h-100 pb-5">
                                    <div class="card-body">
                                        <div class="features-circle mb-4">
                                            <img src="{{ url(env('ASSETSPATHURL') . 'admin-assets/images/feature/' . $feature->image) }}"
                                                alt="">
                                        </div>
                                        <p class="features-card-title text-truncate-2 m-0">{{ $feature->title }}</p>
                                    </div>
                                    <div class="card-footer bg-transparent border-0">
                                        <span class="description text-truncate-3 m-0">{{ $feature->description }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
        <!---------------------------- Premium Features section end ---------------------------->


        <!--  Store Section Start -->

        @if (count($userdata) > 0)
            <section id="our-stores">
                <div class="card-section-bg-color pb-5">
                    <div class="container card-section-container">
                        <div class="sec-title text-center col-xl-6 col-lg-8 col-md-10 mx-auto mb-5">
                            <h2>{{ trans('landing.our_partners') }}</h2>
                            <h5 class="sub-title">{{ trans('landing.our_partners_description') }}</h5>
                        </div>
                        <div
                            class="row row-cols-1 mt-2 row-cols-sm-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 row-cols-xll-4` g-2">

                            @foreach ($userdata as $user)
                                <div class="col">
                                    <a href="{{ URL::to($user->slug . '/') }}" target="_blank">
                                        <div class="card mx-1 rounded-4 h-100 border-0">
                                            <img src="{{ helper::image_path($user->cover_image) }}"
                                                class="card-img-top our_stores_images" alt="...">
                                            <div class="card-body px-0">
                                                <h5 class="card-title hotel-title">{{ $user->website_title }}</h5>
                                                <p class="hotel-subtitle text-muted text-truncate-2">
                                                    {{ $user->description }}
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach

                        </div>
                        <div class="d-flex justify-content-center mt-4">
                            <a href="{{ URL::to('stores') }}"
                                class="btn text-dark border border-dark fw-500 px-3">{{ trans('landing.see_all') }} <i
                                    class="fa-solid {{ session()->get('direction') == 2 ? 'fa-arrow-left' : 'fa-arrow-right' }} px-2"></i></a>
                        </div>
                    </div>
                </div>
            </section>
        @endif



        <!--  Store Section End -->

        <!---------------------------- Key Features section start ---------------------------->
        <section class="key-features py-5 bg-light">
            <div class="container">
                <div class="sec-title text-center col-xl-6 col-lg-8 col-md-10 mx-auto mb-5">
                    <h2 class="fw-bold">✨ Fonctionnalités Qui Font la Différence</h2>
                    <p class="sub-title text-muted">Tout ce dont vous avez besoin pour gérer un restaurant moderne et performant</p>
                </div>
                <div class="row g-4">
                    <!-- Feature 1: WhatsApp -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100 hover-lift">
                            <div class="card-body p-4">
                                <div class="feature-icon bg-success bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                                    <i class="fa-brands fa-whatsapp fa-2x text-success"></i>
                                </div>
                                <h4 class="fw-bold mb-3">📱 Commandes WhatsApp</h4>
                                <p class="text-muted mb-3">Recevez et gérez vos commandes directement via WhatsApp. Notifications instantanées, confirmation automatique et suivi en temps réel.</p>
                                <ul class="list-unstyled small text-muted">
                                    <li class="mb-2">✓ Intégration WhatsApp Business</li>
                                    <li class="mb-2">✓ Notifications en temps réel</li>
                                    <li class="mb-2">✓ Messages automatisés</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Feature 2: QR Code -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100 hover-lift">
                            <div class="card-body p-4">
                                <div class="feature-icon bg-primary bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                                    <i class="fa-solid fa-qrcode fa-2x text-primary"></i>
                                </div>
                                <h4 class="fw-bold mb-3">🎯 Menu QR Code</h4>
                                <p class="text-muted mb-3">Menu digital sans contact. Vos clients scannent et commandent en 30 secondes. Parfait pour restaurants et tables.</p>
                                <ul class="list-unstyled small text-muted">
                                    <li class="mb-2">✓ Menu digital sans contact</li>
                                    <li class="mb-2">✓ QR Code personnalisable</li>
                                    <li class="mb-2">✓ Commande directe depuis table</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Feature 3: Analytics -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100 hover-lift">
                            <div class="card-body p-4">
                                <div class="feature-icon bg-info bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                                    <i class="fa-solid fa-chart-line fa-2x text-info"></i>
                                </div>
                                <h4 class="fw-bold mb-3">📊 Analytics Avancés</h4>
                                <p class="text-muted mb-3">Tableaux de bord complets avec statistiques en temps réel. Suivez ventes, clients, heures de pointe et performance.</p>
                                <ul class="list-unstyled small text-muted">
                                    <li class="mb-2">✓ Dashboard en temps réel</li>
                                    <li class="mb-2">✓ Rapports détaillés</li>
                                    <li class="mb-2">✓ Export CSV/PDF</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Feature 4: Multi-langue -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100 hover-lift">
                            <div class="card-body p-4">
                                <div class="feature-icon bg-warning bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                                    <i class="fa-solid fa-language fa-2x text-warning"></i>
                                </div>
                                <h4 class="fw-bold mb-3">🌍 Multi-Langue</h4>
                                <p class="text-muted mb-3">Interface en français, anglais, arabe. Adaptez votre menu à votre clientèle internationale facilement.</p>
                                <ul class="list-unstyled small text-muted">
                                    <li class="mb-2">✓ Français / Anglais / Arabe</li>
                                    <li class="mb-2">✓ Interface RTL/LTR</li>
                                    <li class="mb-2">✓ Traductions complètes</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Feature 5: Gestion Stock -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100 hover-lift">
                            <div class="card-body p-4">
                                <div class="feature-icon bg-danger bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                                    <i class="fa-solid fa-boxes-stacked fa-2x text-danger"></i>
                                </div>
                                <h4 class="fw-bold mb-3">📦 Gestion Stock</h4>
                                <p class="text-muted mb-3">Suivez vos stocks en temps réel. Alertes automatiques quand stock faible. Évitez les ruptures.</p>
                                <ul class="list-unstyled small text-muted">
                                    <li class="mb-2">✓ Suivi stock en temps réel</li>
                                    <li class="mb-2">✓ Alertes stock faible</li>
                                    <li class="mb-2">✓ Gestion variantes</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Feature 6: Domaine Personnalisé -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100 hover-lift">
                            <div class="card-body p-4">
                                <div class="feature-icon bg-purple bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                                    <i class="fa-solid fa-globe fa-2x text-purple"></i>
                                </div>
                                <h4 class="fw-bold mb-3">🌐 Domaine Personnalisé</h4>
                                <p class="text-muted mb-3">Utilisez votre propre nom de domaine. Renforcez votre image de marque avec une URL professionnelle.</p>
                                <ul class="list-unstyled small text-muted">
                                    <li class="mb-2">✓ Domaine personnalisé</li>
                                    <li class="mb-2">✓ SSL inclus gratuit</li>
                                    <li class="mb-2">✓ Configuration assistée</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!---------------------------- Key Features section end ---------------------------->

        @php
            $checktheme = App\Models\SystemAddons::where('unique_identifier', 'LIKE', '%' . 'theme_' . '%')->where('activated','1')->get();
            $themes = array();
            foreach ($checktheme as $ttlthemes) {
                array_push($themes,str_replace("theme_","",$ttlthemes->unique_identifier));
            }
        @endphp
        <!------------------------------ Templates section start ------------------------------->
        <section class="template bg-primary py-5">
            <div class="container">
                <div class="sec-title text-center col-xl-6 col-lg-8 col-md-10 mx-auto mb-5">
                    <h2 class="text-white">{{ trans('landing.awesome_templates') }}</h2>
                    <h5 class="sub-title text-white">{{ trans('landing.awesome_templates_description') }}</h5>
                </div>
                <!-- theme-preview-content -->
                <div class="templates-owl owl-carousel owl-theme text-white">
                    @foreach ($themes as $item)
                        <div class="item h-100 temp-active">
                            <img src="{{ helper::image_path('theme-' . $item . '.png') }}" alt=""
                                class="object-fit-cover h-100">
                        </div>
                    @endforeach
                </div>
                <!-- theme-preview-content -->
            </div>
        </section>
        <!-------------------------------- Templates section end ------------------------------>

        <!------------------------------- plan section start ------------------------------->

        @if (App\Models\SystemAddons::where('unique_identifier', 'subscription')->first() != null &&
                App\Models\SystemAddons::where('unique_identifier', 'subscription')->first()->activated == 1)
            <section id="pricing-plans" class="our-plan py-5 bg-light">
                <div class="container">
                    <div class="sec-title text-center col-xl-6 col-lg-8 col-md-10 col-12 mx-auto mb-5">
                        <h2 class="text-capitalize fw-bold mb-3">{{ trans('landing.pricing_plan_title') }}</h2>
                        <p class="sub-title text-muted fs-5">{{ trans('landing.pricing_plan_description') }}</p>
                        <div class="mt-4">
                            <span class="badge bg-success px-3 py-2 me-2">✓ Essai Gratuit</span>
                            <span class="badge bg-info px-3 py-2 me-2">✓ Sans Engagement</span>
                            <span class="badge bg-primary px-3 py-2">✓ Support 24/7</span>
                        </div>
                    </div>
                    <div class="row mb-3 plan justify-content-center">
                        @foreach ($planlist as $key => $plan)
                            <div class="col-lg-4 col-md-6 col-12 mb-4">
                                <div class="card border-0 rounded-4 shadow-lg p-4 mb-4 h-100 hover-lift {{ $key == 1 ? 'border-primary border-3' : '' }}" style="{{ $key == 1 ? 'transform: scale(1.05);' : '' }}">
                                    @if($key == 1)
                                        <div class="position-absolute top-0 start-50 translate-middle">
                                            <span class="badge bg-primary px-4 py-2 rounded-pill shadow">⭐ POPULAIRE</span>
                                        </div>
                                    @endif
                                    <div class="card-body p-0 mt-3">
                                        <div class="text-center mb-4">
                                            <p class="fw-bold text-uppercase text-primary mb-2">{{ $plan->name }}</p>
                                            <h1 class="fw-bold display-4 mb-0">{{ helper::currency_formate($plan->price, '') }}</h1>
                                            <p class="text-muted">
                                                /
                                                @if ($plan->duration == 1)
                                                    {{ trans('landing.one_month') }}
                                                @elseif($plan->duration == 2)
                                                    {{ trans('landing.three_month') }}
                                                @elseif($plan->duration == 3)
                                                    {{ trans('landing.six_month') }}
                                                @elseif($plan->duration == 4)
                                                    {{ trans('landing.one_year') }}
                                                @elseif($plan->duration == 5)
                                                    {{ trans('landing.lifetime') }}
                                                @elseif($plan->duration == null)
                                                    {{ trans('landing.free') }}
                                                @endif
                                            </p>
                                            <p class="text-muted small">{{ $plan->description }}</p>
                                        </div>
                                        <hr>
                                        <div class="plan-detals mt-4">
                                            <ul class="m-0 p-0 list-unstyled">

                                                @php $features = explode('|', $plan->features); @endphp
                                                <li class="d-flex align-items-start mb-3"> <i
                                                        class="fa-regular fa-circle-check text-secondary me-2 fs-5"></i>
                                                    <span class="mx-2">
                                                        {{ $plan->order_limit == -1 ? trans('landing.unlimited') : $plan->order_limit }}
                                                        {{ $plan->order_limit > 1 || $plan->order_limit == -1 ? trans('landing.products') : trans('landing.product') }}
                                                    </span>
                                                </li>
                                                <li class="d-flex align-items-start mb-3"> <i
                                                        class="fa-regular fa-circle-check text-secondary me-2 fs-5"></i>
                                                    <span class="mx-2">
                                                        {{ $plan->appointment_limit == -1 ? trans('landing.unlimited') : $plan->appointment_limit }}
                                                        {{ $plan->appointment_limit > 1 || $plan->appointment_limit == -1 ? trans('landing.orders') : trans('landing.order') }}
                                                    </span>
                                                </li>
                                                @php
                                                    $themes = [];
                                                    if ($plan->themes_id != '' && $plan->themes_id != null) {
                                                        $themes = explode(',', $plan->themes_id);
                                                } @endphp
                                                <li class="d-flex align-items-start mb-3"> <i
                                                        class="fa-regular fa-circle-check text-secondary me-2 fs-5 "></i>
                                                    <span class="mx-2">{{ count($themes) }}
                                                        {{ count($themes) > 1 ? trans('landing.themes') : trans('landing.theme') }}</span>
                                                </li>

                                                @if ($plan->coupons == 1)
                                                    <li class="d-flex align-items-start mb-3">
                                                        <i
                                                            class="fa-regular fa-circle-check text-secondary me-2 fs-5 "></i>
                                                        <span class="mx-2">{{ trans('landing.coupons') }}</span>
                                                    </li>
                                                @endif

                                                @if ($plan->custom_domain == 1)
                                                    <li class="d-flex align-items-start mb-3">
                                                        <i
                                                            class="fa-regular fa-circle-check text-secondary me-2 fs-5 "></i>
                                                        <span
                                                            class="mx-2">{{ trans('landing.custome_domain_available') }}</span>
                                                    </li>
                                                @endif
                                                @if ($plan->vendor_app == 1)
                                                    <li class="d-flex align-items-start mb-3">
                                                        <i
                                                            class="fa-regular fa-circle-check text-secondary me-2 fs-5 "></i>
                                                        <span
                                                            class="mx-2">{{ trans('landing.vendor_app_available') }}</span>
                                                    </li>
                                                @endif
                                                @if ($plan->google_analytics == 1)
                                                    <li class="d-flex align-items-start mb-3">
                                                        <i
                                                            class="fa-regular fa-circle-check text-secondary me-2 fs-5 "></i>
                                                        <span
                                                            class="mx-2">{{ trans('landing.google_analytics_available') }}</span>
                                                    </li>
                                                @endif

                                                @if ($plan->blogs == 1)
                                                    <li class="d-flex align-items-start mb-3">
                                                        <i
                                                            class="fa-regular fa-circle-check text-secondary me-2 fs-5 "></i>
                                                        <span class="mx-2">{{ trans('landing.blogs') }}</span>
                                                    </li>
                                                @endif
                                                @if ($plan->google_login == 1)
                                                    <li class="d-flex align-items-start mb-3">
                                                        <i
                                                            class="fa-regular fa-circle-check text-secondary me-2 fs-5 "></i>
                                                        <span class="mx-2">{{ trans('landing.google_login') }}</span>
                                                    </li>
                                                @endif
                                                @if ($plan->facebook_login == 1)
                                                    <li class="d-flex align-items-start mb-3">
                                                        <i
                                                            class="fa-regular fa-circle-check text-secondary me-2 fs-5 "></i>
                                                        <span class="mx-2">{{ trans('landing.facebook_login') }}</span>
                                                    </li>
                                                @endif
                                                @if ($plan->sound_notification == 1)
                                                    <li class="d-flex align-items-start mb-3">
                                                        <i
                                                            class="fa-regular fa-circle-check text-secondary me-2 fs-5 "></i>
                                                        <span
                                                            class="mx-2">{{ trans('landing.sound_notification') }}</span>
                                                    </li>
                                                @endif
                                                @if ($plan->whatsapp_message == 1)
                                                    <li class="d-flex align-items-start mb-3">
                                                        <i
                                                            class="fa-regular fa-circle-check text-secondary me-2 fs-5 "></i>
                                                        <span
                                                            class="mx-2">{{ trans('landing.whatsapp_message') }}</span>
                                                    </li>
                                                @endif
                                                @if ($plan->telegram_message == 1)
                                                    <li class="d-flex align-items-start mb-3">
                                                        <i
                                                            class="fa-regular fa-circle-check text-secondary me-2 fs-5 "></i>
                                                        <span
                                                            class="mx-2">{{ trans('landing.telegram_message') }}</span>
                                                    </li>
                                                @endif
                                                @if ($plan->pos == 1)
                                                    <li class="d-flex align-items-start mb-3">
                                                        <i
                                                            class="fa-regular fa-circle-check text-secondary me-2 fs-5 "></i>
                                                        <span class="mx-2">{{ trans('landing.pos') }}</span>
                                                    </li>
                                                @endif
                                                @if ($plan->pwa == 1)
                                                <li class="d-flex align-items-start mb-3">
                                                    <i
                                                        class="fa-regular fa-circle-check text-secondary me-2 fs-5 "></i>
                                                    <span class="mx-2">{{ trans('labels.pwa') }}</span>
                                                </li>

                                                    {{-- <li class="mb-3 d-flex"> <i
                                                            class="fa-regular fa-circle-check text-success "></i>
                                                        <span class="mx-2">{{ trans('labels.pwa') }}</span>
                                                    </li> --}}
                                                @endif
                                                @php $features = explode('|',$plan->features); @endphp
                                                @foreach ($features as $feature)
                                                    <li class="d-flex align-items-start mb-3"><i
                                                            class="fa-regular fa-circle-check text-secondary me-2 fs-5"></i>
                                                        <span class="mx-2">{{ $feature }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="card-footer border-0 bg-transparent py-4 px-0">
                                        <a href="{{ URL::to('/admin') }}"
                                            class="btn w-100 btn-secondary py-3">{{ trans('landing.subscribe') }}</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
        <!-------------------------------- plan section end -------------------------------->

        <!-------------------------------- Trusted section start -------------------------------->
        <section class="trusted py-5">
            <div class="container bg-primary">
                <div class="row align-items-center justify-content-between trusted-box">
                    <div class="col-md-4 col-12 ps-0 d-none d-lg-block">
                        <img src="{{ url(env('ASSETSPATHURL') . 'landing/images/png/trusted.png') }}"
                            alt="digital connection images" class="w-100 object-fit-content">
                    </div>
                    <div class="col-md-7 col-12">
                        <div>
                            <h3 class="mb-4 text-center text-md-start trusted-title">{{ trans('landing.trusted_by') }}
                            </h3>
                            <div class="d-flex">
                                <div class="col-lg-6 col-md-10 col-6 mb-5 text-white border-start ps-2">
                                    <h2 class="num" data-val="65">65</h2>
                                    <h3 class="num-title">{{ trans('landing.fun_fact_one') }}</h3>
                                </div>
                                <div class="col-lg-6 col-md-10 col-6 mb-5 text-white border-start ps-2">
                                    <h2 class="num" data-val="10">10</h2>
                                    <h3 class="num-title">{{ trans('landing.fun_fact_two') }}</h3>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="col-lg-6 col-md-10 col-6 mb-5 text-white border-start ps-2">
                                    <h2 class="num" data-val="275">275</h2>
                                    <h3 class="num-title">{{ trans('landing.fun_fact_three') }}</h3>
                                </div>
                                <div class="col-lg-6 col-md-10 col-6 mb-5 text-white border-start ps-2">
                                    <h2 class="num" data-val="60">60</h2>
                                    <h3 class="num-title">{{ trans('landing.fun_fact_four') }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-------------------------------- Trusted section end -------------------------------->

        <!----------------------------- testimonila section start ----------------------------->
        <section class="testimonila py-5 bg-light">
            <div class="container">
                <div class="sec-title text-center col-xl-6 col-lg-8 col-md-10 col-12 mx-auto mb-5">
                    <h2 class="fw-bold mb-3">{{ trans('landing.client_says') }}</h2>
                    <p class="sub-title text-muted fs-5">{{ trans('landing.client_says_description') }}</p>
                    <div class="mt-3">
                        <span class="text-warning fs-4">★★★★★</span>
                        <p class="text-muted mb-0 small">Note moyenne : 4.9/5 basée sur 500+ avis</p>
                    </div>
                </div>
                <div id="testimonila-owl" class="owl-carousel owl-theme mt-5">

                    @foreach ($testimonials as $testimonial)
                        <div class="item">
                            <div class="card border-0 rounded-4 shadow-lg p-4 hover-lift h-100">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <span class="text-warning fs-5">★★★★★</span>
                                    </div>
                                    <div class="d-md-flex align-items-start justify-content-between d-block">
                                        <div class="col-lg-7 col-md-6 order-2 order-md-1">
                                            <div class="test-content">
                                                <i class="fa-solid fa-quote-left text-primary fs-3 mb-3"></i>
                                                <p class="text-muted fst-italic mb-4">"{{ $testimonial->description }}"</p>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-5 mb-4 mb-md-0 mx-auto mx-md-0 order-1 order-md-2">
                                            <div class="text-center">
                                                <img src="{{ url(env('ASSETSPATHURL') . 'admin-assets/images/testimonials/' . $testimonial->image) }}"
                                                    alt="{{ $testimonial->name }}"
                                                    class="rounded-circle shadow"
                                                    style="width: 100px; height: 100px; object-fit: cover; border: 4px solid var(--bs-primary);">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer border-0 bg-transparent pt-0">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h5 class="mb-0 fw-bold">{{ $testimonial->name }}</h5>
                                            <p class="text-muted small mb-0">{{ $testimonial->position }}</p>
                                        </div>
                                        <div class="ms-auto">
                                            <i class="fa-solid fa-check-circle text-success fs-4"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach



                </div>
            </div>
        </section>
        <!------------------------------ testimonila section end ------------------------------>


        <!------------------------------ Statistics section start ------------------------------>
        <section class="statistics py-5 gradient-success text-white position-relative overflow-hidden">
            <div class="position-absolute top-0 start-0 w-100 h-100" style="opacity: 0.05;">
                <div class="position-absolute" style="top: -100px; right: 10%; width: 300px; height: 300px; background: white; border-radius: 50%;"></div>
                <div class="position-absolute" style="bottom: -80px; left: 15%; width: 250px; height: 250px; background: white; border-radius: 50%;"></div>
                <div class="position-absolute" style="top: 50%; right: 5%; width: 150px; height: 150px; background: white; border-radius: 50%;"></div>
            </div>
            <div class="container position-relative">
                <div class="row text-center g-4">
                    <div class="col-lg-3 col-md-6 col-6">
                        <div class="stat-card p-4">
                            <div class="stat-icon mb-3">
                                <i class="fa-solid fa-store fs-1"></i>
                            </div>
                            <div class="stat-number display-3 fw-bold mb-2" data-target="500">0</div>
                            <p class="stat-label fs-5 mb-0 opacity-75">{{ trans('landing.stats_restaurants') }}</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-6">
                        <div class="stat-card p-4">
                            <div class="stat-icon mb-3">
                                <i class="fa-solid fa-shopping-bag fs-1"></i>
                            </div>
                            <div class="stat-number display-3 fw-bold mb-2" data-target="50000">0</div>
                            <p class="stat-label fs-5 mb-0 opacity-75">{{ trans('landing.stats_orders') }}</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-6">
                        <div class="stat-card p-4">
                            <div class="stat-icon mb-3">
                                <i class="fa-solid fa-heart fs-1"></i>
                            </div>
                            <div class="stat-number display-3 fw-bold mb-2" data-target="99">0</div>
                            <p class="stat-label fs-5 mb-0 opacity-75">{{ trans('landing.stats_satisfaction') }}%</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-6">
                        <div class="stat-card p-4">
                            <div class="stat-icon mb-3">
                                <i class="fa-solid fa-globe fs-1"></i>
                            </div>
                            <div class="stat-number display-3 fw-bold mb-2" data-target="15">0</div>
                            <p class="stat-label fs-5 mb-0 opacity-75">{{ trans('landing.stats_countries') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!------------------------------ Statistics section end ------------------------------>


        <!------------------------------ blog section end ------------------------------>
        @if (count($blogs) > 0)
            <section id="blog" class="blog py-5">
                <div class="container">
                    <div class="sec-title text-center mb-5" data-aos="zoom-in" data-aos-easing="ease-out-cubic"
                        data-aos-duration="2000">
                        <h2 class="text-capitalize fw-semibold">{{ trans('landing.blogs') }}</h2>
                        <h5 class="sub-title"> {{ trans('landing.blog_desc') }}</h5>
                    </div>

                    <div id="blog-owl" class="owl-carousel owl-theme">

                        @foreach ($blogs as $blog)
                            <div class="item" data-aos="zoom-in" data-aos-easing="ease-out-cubic"
                                data-aos-duration="2000">
                                <div class="card border-0 rounded-0">
                                    <img class="card-img-top blog-image"
                                        src="{{ url(env('ASSETSPATHURL') . 'admin-assets/images/blog/' . $blog->image) }}"
                                        alt="">
                                    <div class="card-body px-0">
                                        <div class="d-flex align-items-start">
                                            <div>
                                                <a href="{{ URL::to('blog_details-' . $blog->id) }}">
                                                    <h4 class="card-title text-truncate-2">{{ $blog->title }}</h4>
                                                </a>
                                                <p class="card-text text-truncate-3">{!! Str::limit(@$blog->description, 100) !!}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-center align-items-center">
                        <a href="{{ URL::to('blog_list') }}"
                            class="btn text-dark mt-4 border border-dark fw-500 px-3">{{ trans('landing.see_all') }} <i
                                class="fa-solid {{ session()->get('direction') == 2 ? 'fa-arrow-left' : 'fa-arrow-right' }} px-2"></i></a>
                    </div>
                </div>
            </section>
        @endif
        <!------------------------------ blog section end ------------------------------>


                <!------------------------------- blog section end ------------------------------->


        <!------------------------------- Why Choose Us section start ------------------------------->
        <section class="why-choose-us py-5 bg-light">
            <div class="container">
                <div class="sec-title text-center col-xl-7 col-lg-8 col-md-10 col-12 mx-auto mb-5">
                    <h2 class="text-capitalize fw-bold mb-3">{{ trans('landing.why_choose_title') }}</h2>
                    <p class="sub-title text-muted fs-5">{{ trans('landing.why_choose_description') }}</p>
                </div>
                <div class="row g-4">
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card border-0 shadow-sm h-100 text-center p-4 hover-lift">
                            <div class="mb-4">
                                <div class="feature-icon bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="fa-solid fa-rocket fs-2"></i>
                                </div>
                            </div>
                            <h5 class="fw-bold mb-3">{{ trans('landing.why_feature_1_title') }}</h5>
                            <p class="text-muted mb-0">{{ trans('landing.why_feature_1_desc') }}</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card border-0 shadow-sm h-100 text-center p-4 hover-lift">
                            <div class="mb-4">
                                <div class="feature-icon bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="fa-solid fa-headset fs-2"></i>
                                </div>
                            </div>
                            <h5 class="fw-bold mb-3">{{ trans('landing.why_feature_2_title') }}</h5>
                            <p class="text-muted mb-0">{{ trans('landing.why_feature_2_desc') }}</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card border-0 shadow-sm h-100 text-center p-4 hover-lift">
                            <div class="mb-4">
                                <div class="feature-icon bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="fa-solid fa-sync fs-2"></i>
                                </div>
                            </div>
                            <h5 class="fw-bold mb-3">{{ trans('landing.why_feature_3_title') }}</h5>
                            <p class="text-muted mb-0">{{ trans('landing.why_feature_3_desc') }}</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card border-0 shadow-sm h-100 text-center p-4 hover-lift">
                            <div class="mb-4">
                                <div class="feature-icon bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="fa-solid fa-shield-halved fs-2"></i>
                                </div>
                            </div>
                            <h5 class="fw-bold mb-3">{{ trans('landing.why_feature_4_title') }}</h5>
                            <p class="text-muted mb-0">{{ trans('landing.why_feature_4_desc') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!------------------------------- Why Choose Us section end ------------------------------->


        <!------------------------------- FAQ section start ------------------------------->
        <section id="faq" class="faq py-5 mb-5">
            <div class="container">
                <div class="sec-title text-center col-xl-7 col-lg-8 col-md-10 col-12 mx-auto mb-5">
                    <h2 class="text-capitalize fw-bold mb-3">{{ trans('landing.faq_section_title') }}</h2>
                    <p class="sub-title text-muted fs-5">{{ trans('landing.faq_section_description') }}</p>
                </div>
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-12">
                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                                <h2 class="accordion-header" id="faqHeading1">
                                    <button class="accordion-button fw-semibold fs-5 bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1" aria-expanded="true" aria-controls="faqCollapse1">
                                        <i class="fa-solid fa-circle-question text-primary me-3 fs-4"></i>
                                        {{ trans('landing.faq_q1') }}
                                    </button>
                                </h2>
                                <div id="faqCollapse1" class="accordion-collapse collapse show" aria-labelledby="faqHeading1" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted bg-light">
                                        {{ trans('landing.faq_a1') }}
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                                <h2 class="accordion-header" id="faqHeading2">
                                    <button class="accordion-button collapsed fw-semibold fs-5 bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
                                        <i class="fa-solid fa-circle-question text-primary me-3 fs-4"></i>
                                        {{ trans('landing.faq_q2') }}
                                    </button>
                                </h2>
                                <div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted bg-light">
                                        {{ trans('landing.faq_a2') }}
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                                <h2 class="accordion-header" id="faqHeading3">
                                    <button class="accordion-button collapsed fw-semibold fs-5 bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
                                        <i class="fa-solid fa-circle-question text-primary me-3 fs-4"></i>
                                        {{ trans('landing.faq_q3') }}
                                    </button>
                                </h2>
                                <div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted bg-light">
                                        {{ trans('landing.faq_a3') }}
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                                <h2 class="accordion-header" id="faqHeading4">
                                    <button class="accordion-button collapsed fw-semibold fs-5 bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4" aria-expanded="false" aria-controls="faqCollapse4">
                                        <i class="fa-solid fa-circle-question text-primary me-3 fs-4"></i>
                                        {{ trans('landing.faq_q4') }}
                                    </button>
                                </h2>
                                <div id="faqCollapse4" class="accordion-collapse collapse" aria-labelledby="faqHeading4" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted bg-light">
                                        {{ trans('landing.faq_a4') }}
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                                <h2 class="accordion-header" id="faqHeading5">
                                    <button class="accordion-button collapsed fw-semibold fs-5 bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse5" aria-expanded="false" aria-controls="faqCollapse5">
                                        <i class="fa-solid fa-circle-question text-primary me-3 fs-4"></i>
                                        {{ trans('landing.faq_q5') }}
                                    </button>
                                </h2>
                                <div id="faqCollapse5" class="accordion-collapse collapse" aria-labelledby="faqHeading5" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted bg-light">
                                        {{ trans('landing.faq_a5') }}
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                                <h2 class="accordion-header" id="faqHeading6">
                                    <button class="accordion-button collapsed fw-semibold fs-5 bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse6" aria-expanded="false" aria-controls="faqCollapse6">
                                        <i class="fa-solid fa-circle-question text-primary me-3 fs-4"></i>
                                        {{ trans('landing.faq_q6') }}
                                    </button>
                                </h2>
                                <div id="faqCollapse6" class="accordion-collapse collapse" aria-labelledby="faqHeading6" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted bg-light">
                                        {{ trans('landing.faq_a6') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-5">
                            <p class="fs-5 text-muted mb-3">Vous avez encore des questions ?</p>
                            <a href="#contect-us" class="btn btn-primary btn-lg rounded-pill px-5">
                                <i class="fa-solid fa-comments me-2"></i>Contactez-nous
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!------------------------------- FAQ section end ------------------------------->


        <!------------------------------ Final CTA section start ------------------------------>

        <section class="final-cta py-5 gradient-primary text-white position-relative overflow-hidden">
            <div class="position-absolute top-0 start-0 w-100 h-100" style="opacity: 0.1;">
                <div class="position-absolute" style="top: -50px; right: -50px; width: 400px; height: 400px; background: white; border-radius: 50%;"></div>
                <div class="position-absolute" style="bottom: -100px; left: -100px; width: 500px; height: 500px; background: white; border-radius: 50%;"></div>
            </div>
            <div class="container position-relative">
                <div class="row align-items-center">
                    <div class="col-lg-8 col-md-7 text-center text-md-start mb-4 mb-md-0">
                        <h2 class="display-5 fw-bold mb-3">🚀 Prêt à Transformer Votre Restaurant ?</h2>
                        <p class="fs-5 mb-0">Rejoignez 500+ restaurants qui ont déjà digitalisé leur activité. Lancez-vous aujourd'hui, sans risque !</p>
                        <div class="mt-4">
                            <span class="badge bg-white text-primary px-3 py-2 me-2 fs-6">✓ Installation en 5 minutes</span>
                            <span class="badge bg-white text-primary px-3 py-2 me-2 fs-6">✓ Support gratuit</span>
                            <span class="badge bg-white text-primary px-3 py-2 fs-6">✓ Satisfaction garantie</span>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-5 text-center">
                        <a href="@if (env('Environment') == 'sendbox') {{ URL::to('/admin') }} @else {{ helper::appdata('')->vendor_register == 1 ?  URL::to('/admin/register') :  URL::to('/admin') }} @endif"
                           class="btn btn-light btn-lg px-5 py-3 rounded-pill shadow-lg fw-bold"
                           target="_blank">
                            <i class="fa-solid fa-rocket me-2"></i>Commencer Gratuitement
                        </a>
                        <p class="text-white mt-3 mb-0 small">Pas de carte bancaire requise • Annulation facile</p>
                    </div>
                </div>
            </div>
        </section>
        <!------------------------------ Final CTA section end ------------------------------>


        <!------------------------------ newsletter start ------------------------------>
        <section class="newsletter bg-secondary mb-5">
            <div class="container text-center text-white">
                <div class="py-5">
                    <h2 class="py-4 m-0 newsletter-title">{{ trans('landing.subscribe_section_title_msg') }}</h2>
                    <h5 class="newsletter-subtitle col-xl-8 col-lg-10 col-auto m-auto text-white">
                        {{ trans('landing.subscribe_section_description') }}</h5>
                    <form action="{{ URL::to('emailsubscribe') }}" method="POST">
                        @csrf
                        <div class="col-xl-6 col-lg-7 col-md-10 mx-md-auto mt-5">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control rounded h-45 fs-6"
                                    placeholder="Enter your email" name="email" id="email"
                                    aria-label="Recipient's username" aria-describedby="subscribe_button" required>
                                @error('email')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <button
                                    class="btn btn-secondary rounded h-45 {{ session()->get('direction') == 2 ? 'me-md-3 me-2' : 'ms-md-3 ms-2' }}"
                                    type="submit" id="subscribe_button">Subscribe</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
        <!------------------------------- newsletter end ------------------------------->

        <!------------------------------- Contact start ------------------------------->
        <section id="contect-us" class="contact py-5 mb-4 bg-light">
            <div class="container">
                <div class="sec-title text-center col-xl-5 col-lg-7 col-md-9 col-12 mx-auto mb-5">
                    <h2 class="text-capitalize fw-bold mb-3">{{ trans('landing.contact_us') }}</h2>
                    <p class="sub-title text-muted fs-5">{{ trans('landing.contact_section_title') }}</p>
                </div>
                <div class="row align-items-stretch justify-content-between g-4">
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="card card-info bg-gradient-primary border-0 text-white position-relative h-100 shadow-lg rounded-4">
                            <div class="card-body p-4">
                                <div class="info mb-4">
                                    <h4 class="fw-bold mb-3">
                                        <i class="fa-solid fa-headset me-2"></i>{{ trans('landing.contact_info') }}
                                    </h4>
                                    <p class="opacity-75">{{ trans('landing.contact_info_msg') }}</p>
                                </div>
                                <div class="d-flex align-items-center mb-4 p-3 bg-white bg-opacity-10 rounded-3">
                                    <div class="icon-box bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                        <i class="fa-solid fa-phone-volume"></i>
                                    </div>
                                    <div>
                                        <small class="d-block opacity-75">Téléphone</small>
                                        <strong>{{ helper::appdata('')->contact }}</strong>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-4 p-3 bg-white bg-opacity-10 rounded-3">
                                    <div class="icon-box bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                        <i class="fa-solid fa-envelope"></i>
                                    </div>
                                    <div>
                                        <small class="d-block opacity-75">Email</small>
                                        <strong>{{ helper::appdata('')->email }}</strong>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start p-3 bg-white bg-opacity-10 rounded-3">
                                    <div class="icon-box bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; flex-shrink: 0;">
                                        <i class="fa-solid fa-location-dot"></i>
                                    </div>
                                    <div>
                                        <small class="d-block opacity-75">Adresse</small>
                                        <strong>{{ helper::appdata('')->address }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7 col-md-6 col-12">
                        <div class="card border-0 shadow-sm h-100 rounded-4">
                            <div class="card-body p-4">
                                <h4 class="fw-bold mb-4">
                                    <i class="fa-solid fa-paper-plane text-primary me-2"></i>Envoyez-nous un Message
                                </h4>

                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                                        <i class="fa-solid fa-circle-check me-2"></i>
                                        <strong>Merci!</strong> Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                                        <i class="fa-solid fa-circle-exclamation me-2"></i>
                                        <strong>Erreur!</strong> {{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                @if($errors->any())
                                    <div class="alert alert-warning alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                        <strong>Attention!</strong>
                                        <ul class="mb-0 mt-2">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                <form class="row g-3" action="{{ URL::to('inquiry') }}" method="POST">
                                    @csrf
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label fw-semibold">
                                            {{ trans('landing.first_name') }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-lg rounded-3 shadow-sm border-0 bg-light"
                                               name="first_name" id="first_name" placeholder="Jean" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label fw-semibold">
                                            {{ trans('landing.last_name') }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-lg rounded-3 shadow-sm border-0 bg-light"
                                               name="last_name" id="last_name" placeholder="Dupont" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="emaill" class="form-label fw-semibold">
                                            {{ trans('landing.email') }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" class="form-control form-control-lg rounded-3 shadow-sm border-0 bg-light"
                                               name="emaill" id="emaill" placeholder="jean@example.com" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label fw-semibold">
                                            {{ trans('landing.mobile') }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control form-control-lg rounded-3 shadow-sm border-0 bg-light"
                                               name="mobile" id="phone" placeholder="+33 6 12 34 56 78"
                                               onKeyPress="if(this.value.length==10) return false;" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="Message" class="form-label fw-semibold">
                                            {{ trans('landing.message') }} <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control form-control-lg rounded-3 shadow-sm border-0 bg-light"
                                                  name="message" id="Message" rows="5"
                                                  placeholder="Décrivez votre projet ou posez vos questions..." required></textarea>
                                    </div>

                                    @include('landing.layout.recaptcha')

                                    <div class="col-12 d-flex justify-content-end gap-2">
                                        <button class="btn btn-primary btn-lg px-5 rounded-pill shadow" type="submit">
                                            <i class="fa-solid fa-paper-plane me-2"></i>{{ trans('landing.send_msg') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!------------------------------- Contact end ------------------------------->
    </main>
@endsection

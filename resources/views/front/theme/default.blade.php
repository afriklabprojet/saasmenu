<!DOCTYPE html>
<html lang="en" dir="{{ session()->get('direction') == 2 ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:title" content="{{ @helper::appdata($vdata)->meta_title }}" />
    <meta property="og:description" content="{{ @helper::appdata($vdata)->meta_description }}" />
    <meta property="og:image" content='{{ @helper::image_path(helper::appdata($vdata)->og_image) }}' />
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

    <title>{{ @helper::appdata($vdata)->website_title }}</title>
    <link rel="icon" href="{{ @helper::image_path(@helper::appdata(@$vdata)->favicon) }}" type="image"
        sizes="16x16">
    <link rel="stylesheet" href="{{ url(env('ASSETSPATHURL') . 'admin-assets/css/fontawesome/all.min.css') }}">
    <!-- FontAwesome CSS -->
    <!--Aos animetion  -->
    <link href="{{ url(env('ASSETSPATHURL') . 'web-assets/css/unpkg.com_aos@2.3.1_dist_aos.css') }}" rel="stylesheet">
    <!-- swiper Css -->
    <link rel="stylesheet" href="{{ url(env('ASSETSPATHURL') . 'web-assets/css/swiper-bundle.min.css') }}">
    <!-- Font-Family -->
    <link rel="stylesheet" href="{{ url(env('ASSETSPATHURL') . 'web-assets/font/outfit.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ url(env('ASSETSPATHURL') . 'web-assets/font-awesome/css/all.min.css') }}">
    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="{{ url(env('ASSETSPATHURL') . 'web-assets/css/toastr/toastr.min.css') }}">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ url(env('ASSETSPATHURL') . 'web-assets/css/dataTables.bootstrap4.min.css') }}">
    <!-- Owl Carousel Css -->
    <link rel="stylesheet" href="{{ url(env('ASSETSPATHURL') . 'web-assets/css/owl.carousel.min.css') }}">
    <!-- Owl Carousel Css -->
    <link rel="stylesheet" href="{{ url(env('ASSETSPATHURL') . 'web-assets/css/owl.theme.default.css') }}">
    <!-- Bootstrap Min Css -->
    <link rel="stylesheet" href="{{ url(env('ASSETSPATHURL') . 'web-assets/css/bootstrap.min.css') }}">
    <!-- Style Css -->
    <link rel="stylesheet" href="{{ url(env('ASSETSPATHURL') . 'web-assets/css/style.css') }}">
    <!-- Responsive Css -->
    <link rel="stylesheet" href="{{ url(env('ASSETSPATHURL') . 'web-assets/css/responsive.css') }}">
    <!-- Sweetalert CSS -->
    <link rel="stylesheet" href="{{ url(env('ASSETSPATHURL') . 'web-assets/css/sweetalert/sweetalert2.min.css') }}">
    @yield('recaptcha_script')

    <!-- PWA Meta Tags E-menu -->
    <meta name="theme-color" content="{{ @helper::appdata($vdata)->primary_color ?? '#007bff' }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ @helper::appdata($vdata)->website_title ?? 'E-menu' }}">
    <meta name="application-name" content="{{ @helper::appdata($vdata)->website_title ?? 'E-menu' }}">
    <meta name="msapplication-TileColor" content="{{ @helper::appdata($vdata)->primary_color ?? '#007bff' }}">
    <meta name="msapplication-tap-highlight" content="no">

    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ route('pwa.manifest') }}">

    <!-- PWA Icons -->
    <link rel="apple-touch-icon" href="{{ @helper::image_path(@helper::appdata($vdata)->favicon) }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ @helper::image_path(@helper::appdata($vdata)->favicon) }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ @helper::image_path(@helper::appdata($vdata)->favicon) }}">

    <!-- PWA Startup Images pour iOS -->
    <link rel="apple-touch-startup-image" href="{{ @helper::image_path(@helper::appdata($vdata)->favicon) }}">

    <!-- PWA Compatibilité Windows -->
    <meta name="msapplication-config" content="browserconfig.xml">

    <!-- PWA  -->

    @if (App\Models\SystemAddons::where('unique_identifier', 'subscription')->first() != null &&
            App\Models\SystemAddons::where('unique_identifier', 'subscription')->first()->activated == 1)
        @if (App\Models\SystemAddons::where('unique_identifier', 'pwa')->first() != null &&
                App\Models\SystemAddons::where('unique_identifier', 'pwa')->first()->activated == 1)
            @php
                $checkplan = App\Models\Transaction::where('vendor_id', $vdata)
                    ->orderByDesc('id')
                    ->first();
                $user = App\Models\User::where('id', $vdata)->first();
                if ($user->allow_without_subscription == 1) {
                    $pwa = 1;
                } else {
                    $pwa = @$checkplan->pwa;
                }
            @endphp
            @if ($pwa == 1)
                @if (helper::appdata($vdata)->pwa == 1)
                    @include('front.pwa.pwa')
                @endif
            @endif
        @else
            @if (App\Models\SystemAddons::where('unique_identifier', 'pwa')->first() != null &&
                    App\Models\SystemAddons::where('unique_identifier', 'pwa')->first()->activated == 1)
                @if (helper::appdata($vdata)->pwa == 1)
                    @include('front.pwa.pwa')
                @endif
            @endif
        @endif
    @endif
    <style>
        :root {
            --bs-primary: #ce6a19;
            --bs-secondary: #5a0bee;

            @if (helper::appdata($vdata)->primary_color != null)
                --bs-primary: {{ helper::appdata($vdata)->primary_color }};
            @endif
            @if (helper::appdata($vdata)->secondary_color != null)
                --bs-secondary: {{ helper::appdata($vdata)->secondary_color }};
            @endif
            --secondary-color: #000;
            --font-family: 'Outfit',
            sans-serif;
        }
    </style>
</head>

<body>
    @php
        $baseurl = url('/') . '/' . $storeinfo->slug;
        $basecaturl = url('/') . '/' . $storeinfo->slug . '/categories';
    @endphp
    @include('front.theme.preloader')

    @if ((helper::appdata(@$vdata)->template ?? 1) != 3 && (helper::appdata(@$vdata)->template ?? 1) != 5)
        @include('front.theme.header')
    @else
        @if ($baseurl != request()->url() && $basecaturl != request()->url())
            @include('front.theme.header')
        @endif
    @endif
    @yield('content')

    @if ((helper::appdata(@$vdata)->template ?? 1) != 3 && (helper::appdata(@$vdata)->template ?? 1) != 5)
        @include('front.theme.footer')
    @else
        @if ($baseurl != request()->url() && $basecaturl != request()->url())
            @include('front.theme.footer')
        @endif
    @endif

    {{-- PWA Interface E-menu --}}
    @include('front.components.pwa-ui')
    <!-- Modal -->
    <div class="d-flex align-items-center float-end">
        <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content search-modal-content rounded-5">
                    <div class="modal-header align-items-center px-3 px-md-4">
                        <h3 class="page-title mb-0 fs-2 text-dark fw-bolder">{{ trans('labels.search') }}</h3>
                        <button type="button" class="btn-close m-0" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-3 px-md-4 mb-0">
                        <form class="" action="{{ URL::to($storeinfo->slug . '/search/') }}" method="get">
                            <div class="col-12">
                                <div class="row align-items-center justify-content-between g-0">
                                    <span>{{ trans('labels.search_desc') }}</span>
                                    <div class="col-12">
                                        <input type="hidden" name="vendor_id" value="{{ $vdata }}">
                                        <input type="text" placeholder="{{ trans('labels.search_here') }}"
                                            name="search" id="searchText"
                                            class="py-2 input-width px-2 mt-3 mb-1 w-100 border rounded-5 fs-7 search_input"
                                            value="">
                                        <div class="search-btn-group">
                                            <div class="d-flex justify-content-between align-items-center mt-3 mt-md-4">
                                                <a type="submit"
                                                    class="btn btn-danger w-100 rounded-0 rounded-3 m-1 text-center"
                                                    data-bs-dismiss="modal">{{ trans('labels.cancel') }} </a>
                                                <input type="submit"
                                                    class="btn-primary w-100 rounded-0 rounded-3 m-1 text-center"
                                                    value="{{ trans('labels.submit') }}" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Hours Modal Start -->
    <div class="modal fade" id="examplehours" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content rounded-5">
                <div class="modal-header px-4">
                    <p class="title pb-1 fs-5"> {{ trans('labels.working_hours') }}</p>
                    <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4">
                    <ul>
                        @if (is_array(@helper::timings($vdata)) || is_object(@helper::timings($vdata)))
                            @foreach (@helper::timings($vdata) as $time)
                                @if ($time->is_always_close != 1)
                                    <li class="working-hours-main pb-3">
                                        <p>
                                            <i class="fa-regular fa-calendar-days hours-to"></i>
                                            <span class="px-2 fw-600">
                                                {{ trans('labels.' . strtolower($time->day)) }}</span>
                                        </p>
                                        <div class="hours-list">
                                            <button type="button"
                                                class="btn border hours-to fs-7">{{ $time->open_time }}</button>
                                            <p class="to">{{ trans('labels.to') }}</p>
                                            <button type="button"
                                                class="btn border hours-to fs-7">{{ $time->close_time }}</button>
                                        </div>
                                    </li>
                                @else
                                    <li class="d-flex align-items-center justify-content-end pb-3">
                                        <p class="sunday">
                                            <i class="fa-regular fa-calendar-days hours-to"></i>
                                            <span
                                                class="px-2 fw-600 text-danger sunday">{{ trans('labels.' . strtolower($time->day)) }}
                                            </span>
                                        </p>
                                        <div class="hours-list justify-content-center m-auto">
                                            <button type="button"
                                                class="btn border text-dark bg-danger text-white fs-7"
                                                data-bs-dismiss="modal">{{ trans('labels.closed') }}</button>
                                        </div>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Hours Modal end -->
    <div class="modal fade" id="additems" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div id="viewproduct_body"></div>
            {{-- <div class="modal-content rounded-5">
                <div class="modal-header border-0 px-4">
                    <p class="title pb-1 fs-5" id="viewitem_name"></p>
                    <button type="button" class="btn-close m-0" onclick="cleardata()" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>



                <div class="modal-body px-sm-4 p-2">
                    <div id="carouselExampleIndicators" class="carousel slide position-relative">
                        <div class="carousel-indicators" id="image_buttons">

                            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0"
                                class="active" aria-current="true" aria-label="Slide 1"></button>
                            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"
                                aria-label="Slide 2"></button>
                            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"
                                aria-label="Slide 3"></button>

                        </div>
                        <div class="carousel-inner card-modal-iages" id="item_images">

                        </div>
                        <button class="carousel-control-prev" type="button"
                            data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">{{ trans('labels.previous') }}</span>
                        </button>
                        <button class="carousel-control-next" type="button"
                            data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">{{ trans('labels.next') }}</span>
                        </button>
                    </div>
                    <div class="mt-4">
                        <div class="products-price">
                            <span class="price fs-5" id="viewitem_price"></span>
                            <del id="viewitem_originalprice"></del>
                        </div>
                        <p class="title mt-3 mb-1">{{ trans('labels.description') }}</p>
                        <p class="description-cart" id="item_desc">

                        </p>
                        <p class="title pb-1 pt-3 variants" id="variants_title">{{ trans('labels.variants') }}</p>
                        <div id="variants">

                        </div>
                        <p class="title pb-1 pt-3 variants" id="extras_title">{{ trans('labels.extras') }}</p>
                        <form class="extras-form" id="extras">
                        </form>
                    </div>

                </div>

                <input type="hidden" id="item_id" value="" />
                <input type="hidden" id="item_name" value="" />
                <input type="hidden" id="item_price" value="" />
                <input type="hidden" id="item_tax" value="" />
                <input type="hidden" id="orignal_price" value="" />
                <input type="hidden" id="item_image" value="" />
                <input type="hidden" id="vendor_id" value="{{ @$vdata }}" />


                <div class="modal-footer border-0 d-block">
                    <div class="row d-flex justify-content-between align-items-center gx-2">
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <nav aria-label="Page navigation example">
                                <ul class="pagination mb-0">
                                    <li class="page-item">
                                        <a class="page-link {{ session()->get('direction') == 2 ? 'rounded-end rounded-start-0' : 'rounded-start rounded-end-0' }}"
                                            href="javascript:void(0)" aria-label="Previous" id="minusqty">
                                            <span aria-hidden="true">
                                                <i class="fa-solid fa-minus fs-8"></i>
                                            </span>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <input type="text" class="page-link px-2 px-md-3 bg-light" id="qty"
                                            value="1" readonly />
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link {{ session()->get('direction') == 2 ? 'rounded-start rounded-end-0' : 'rounded-end rounded-start-0' }}"
                                            href="javascript:void(0)" aria-label="Next" id="plusqty">
                                            <span aria-hidden="true">
                                                <i class="fa-solid fa-plus fs-8"></i>
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        <div class="col-6 col-md-4">
                            <a class="btn-secondary rounded-3 w-100 text-center" id="enquiries" href=""
                                target="_blank">{{ trans('labels.enquiries') }}</a>
                        </div>
                        <div class="col-6 col-md-4">
                            <a class="btn-primary rounded-3 w-100 text-center" href="javascript:void(0)"
                                onclick="calladdtocart()">{{ trans('labels.add_to_cart') }}</a>
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>

    <div class="col-md-6 d-flex justify-content-center m-auto">
        <div class="offcanvas offcanvas-bottom categories_theme6_offcanvas" tabindex="-1" id="offcanvasBottom"
            aria-labelledby="offcanvasBottomLabel">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title" id="offcanvascategori">
                    {{ trans('labels.categories') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>

            <!-- ----- modal start ---- -->
            <div class="offcanvas-body small overflow-auto ">
                <div class="tab-row" id="menu-center">
                    <ul
                        class="swiper-wrapper navgation_lower pb-1 d-block theme-7-category-card pb-1 mb-0 category-card">
                        @if (is_array(@$getcategory) || is_object(@$getcategory))
                            @foreach (@$getcategory as $key => $category)
                                @php
                                    $check_cat_count = 0;
                                @endphp
                                @foreach ($getitem as $item)
                                    @if ($category->id == $item->cat_id)
                                        @php
                                            $check_cat_count++;
                                        @endphp
                                    @endif
                                @endforeach
                                @if ($check_cat_count > 0)
                                    <li class="{{ $key == 0 ? 'active1' : '' }} d-flex py-2 swiper-slide w-100 justify-content-between border-bottom-1"
                                        data-bs-dismiss="offcanvas" id="specs-{{ $category->id }}">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ helper::image_path($category->image) }}" alt="">
                                            <p class="act-7 fw-500 px-2 text-start line-limit-1 fs-7">
                                                {{ $category->name }}</p>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="m-0">{{ $check_cat_count }}</span>
                                        </div>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- whatsapp modal start -->

    @if (App\Models\SystemAddons::where('unique_identifier', 'subscription')->first() != null &&
        App\Models\SystemAddons::where('unique_identifier', 'subscription')->first()->activated == 1)
    @if (App\Models\SystemAddons::where('unique_identifier', 'whatsapp_message')->first() != null &&
            App\Models\SystemAddons::where('unique_identifier', 'whatsapp_message')->first()->activated == 1)
        @php
            $checkplan = App\Models\Transaction::where('vendor_id', $vdata)
                ->orderByDesc('id')
                ->first();
            $user = App\Models\User::where('id', $vdata)->first();
            if (@$user->allow_without_subscription == 1) {
                $whatsapp_message = 1;
            } else {
                $whatsapp_message = @$checkplan->whatsapp_message;
            }

        @endphp
        @if ($whatsapp_message == 1 && (helper::appdata($vdata)->whatsapp_chat_on_off ?? 0) == 1)
        <input type="checkbox" id="check">
        <div class="whatsapp_icon {{ session()->get('direction') == 2 ? 'whatsapp_icon_rtl' : 'whatsapp_icon_ltr' }}">
            <label class="chat-btn {{ session()->get('direction') == 2 ? 'chat-btn_rtl' : 'chat-btn_ltr' }}"
                for="check">
                <i class="fa-brands fa-whatsapp comment"></i>
                <i class="fa fa-close close"></i>
            </label>
        </div>
        <div class=" {{ session()->get('direction') == 2 ? 'wrapper_rtl' : 'wrapper' }}  wp_chat_box d-none">
            <div class="msg_header">
                <h6>{{ helper::appdata(@$vdata)->website_title }}</h6>
            </div>
            <div class="text-start p-3 bg-msg">
                <div class="card p-2 msg">
                    {{ trans('labels.whatsapp_modal_description') }}
                </div>
            </div>
            <div class="chat-form">
                <form action="https://api.whatsapp.com/send" method="get" target="_blank"
                    class="d-flex align-items-center d-grid gap-2">
                    <textarea class="form-control" name="text" placeholder="Your Text Message" required></textarea>
                    <input type="hidden" name="phone" value="{{ helper::appdata(@$vdata)->contact }}">
                    <button type="submit" class="btn btn-success btn-block hover-1">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
        @endif
    @endif
@else
    @if (App\Models\SystemAddons::where('unique_identifier', 'whatsapp_message')->first() != null &&
            App\Models\SystemAddons::where('unique_identifier', 'whatsapp_message')->first()->activated == 1)
        @if (helper::appdata($vdata)->whatsapp_chat_on_off == 1)
        <input type="checkbox" id="check">
        <div class="whatsapp_icon {{ session()->get('direction') == 2 ? 'whatsapp_icon_rtl' : 'whatsapp_icon_ltr' }}">
            <label class="chat-btn {{ session()->get('direction') == 2 ? 'chat-btn_rtl' : 'chat-btn_ltr' }}"
                for="check">
                <i class="fa-brands fa-whatsapp comment"></i>
                <i class="fa fa-close close"></i>
            </label>
        </div>
        <div class=" {{ session()->get('direction') == 2 ? 'wrapper_rtl' : 'wrapper' }}  wp_chat_box d-none">
            <div class="msg_header">
                <h6>{{ helper::appdata(@$vdata)->website_title }}</h6>
            </div>
            <div class="text-start p-3 bg-msg">
                <div class="card p-2 msg">
                    {{ trans('labels.whatsapp_modal_description') }}
                </div>
            </div>
            <div class="chat-form">
                <form action="https://api.whatsapp.com/send" method="get" target="_blank"
                    class="d-flex align-items-center d-grid gap-2">
                    <textarea class="form-control" name="text" placeholder="Your Text Message" required></textarea>
                    <input type="hidden" name="phone" value="{{ helper::appdata(@$vdata)->contact }}">
                    <button type="submit" class="btn btn-success btn-block hover-1">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
        @endif
    @endif
@endif


    <!-- whatsapp modal end -->
    <input type="hidden" id="addtocarturl" value="{{ url('/add-to-cart') }}" />
    <input type="hidden" id="showitemurl" value="{{ url('/product-details') }}" />
    <!-- Google tag (gtag.js) -->
    @if(!empty(helper::appdata(1)->tracking_id))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ helper::appdata(1)->tracking_id }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', '{{ helper::appdata(1)->tracking_id }}');
    </script>
    @endif

    <!--Start of Tawk.to Script-->
    @if ((helper::appdata(@$vdata)->tawk_on_off ?? 0) == 1)
        <script type="text/javascript">
            var Tawk_API = Tawk_API || {},
                Tawk_LoadStart = new Date();
            (function() {
                var s1 = document.createElement("script"),
                    s0 = document.getElementsByTagName("script")[0];
                s1.async = true;
                s1.src =
                    'https://embed.tawk.to/65d7258a9131ed19d9700056/{{ helper::appdata(@$vdata)->tawk_widget_id }}';
                s1.charset = 'UTF-8';
                s1.setAttribute('crossorigin', '*');
                s0.parentNode.insertBefore(s1, s0);
            })();
        </script>
    @endif
    <!--End of Tawk.to Script-->

    <script src="{{ url(env('ASSETSPATHURL') . 'web-assets/js/jquery-3.6.3.min.js') }}"></script>
    <script src="{{ url(env('ASSETSPATHURL') . 'web-assets/js/custom.js') }}"></script>
    <!-- Bootstrap Bundle Min Js -->

    <script src="{{ url(env('ASSETSPATHURL') . 'web-assets/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Owl Carousel Min Js -->

    <script src="{{ url(env('ASSETSPATHURL') . 'web-assets/js/owl.carousel.min.js') }}"></script>

    <script src="{{ url(env('ASSETSPATHURL') . 'web-assets/js/toastr/toastr.min.js') }}"></script><!-- Toastr JS -->

    <!-- Jquery DataTables Min Js -->

    <script src="{{ url(env('ASSETSPATHURL') . 'web-assets/js/jquery.dataTables.min.js') }}"></script>

    <!-- DataTables Bootstrap4 Min Js -->

    <script src="{{ url(env('ASSETSPATHURL') . 'web-assets/js/dataTables.bootstrap4.min.js') }}"></script>

    <!-- Sweetalert2@11 Js -->

    <script src="{{ url(env('ASSETSPATHURL') . 'web-assets/js/sweetalert2@11.js') }}"></script>

    <!-- Aos Js -->

    <script src="{{ url(env('ASSETSPATHURL') . 'web-assets/js/unpkg.com_aos@2.3.1_dist_aos.js') }}"></script>

    <!-- Swiper Bundle Min Js -->

    <script src="{{ url(env('ASSETSPATHURL') . 'web-assets/js/cdn.jsdelivr.net_npm_swiper@9_swiper-bundle.min.js') }}">
    </script>

    <script src="{{ url(env('ASSETSPATHURL') . 'web-assets/js/jquery.number.min.js') }}"></script>
    <script>
        var are_you_sure = "{{ trans('messages.are_you_sure') }}";
        var yes = "{{ trans('messages.yes') }}";
        var no = "{{ trans('messages.no') }}";
        var cancel = "{{ trans('labels.cancel') }}";
        let wrong = "{{ trans('messages.wrong') }}";
        let env = "{{ env('Environment') }}";
        let whatsappnumber = "{{ @helper::appdata(@$vdata)->contact }}";
        let direction = "{{ session('direction') }}";
        var vendor_id = "{{ $vdata }}";
        var formate = "{{ helper::appdata($vdata)->currency_formate }}";
        toastr.options = {
            "closeButton": true,
            "positionClass": "toast-bottom-center",
        }
        @if (Session::has('success'))
            toastr.success("{{ session('success') }}");
        @endif
        @if (Session::has('error'))
            toastr.error("{{ session('error') }}");
        @endif
    </script>

    <script>
        // top deals parameter
        var start_date = "{{ @$topdeals->start_date }}";
        var start_time = "{{ @$topdeals->start_time }}";
        var end_date = "{{ @$topdeals->end_date }}";
        var end_time = "{{ @$topdeals->end_time }}";
        var topdeals = "{{ !empty($topdealsproducts) ? 1 : 0 }}";
        var time_zone = "{{ helper::appdata($vdata)->timezone }}";
        var current_date = "{{ \Carbon\Carbon::now()->toDateString() }}";
        var deal_type = "{{ @$topdeals->deal_type }}";
    </script>
    <script>
        function currency_formate(price) {

            if ("{{ @helper::appdata($vdata)->currency_position }}" == "left") {

                if ("{{ helper::appdata($vdata)->decimal_separator }}" == 1) {
                    var oldprice = $.number(price, formate);
                    if ("{{ @helper::appdata($vdata)->currency_space }}" == 1) {
                        newprice = "{{ @helper::appdata($vdata)->currency }}" + ' ' + oldprice;
                    } else {
                        newprice = "{{ @helper::appdata($vdata)->currency }}" + oldprice;
                    }

                } else {
                    var oldprice = $.number(price, formate, ',', '.');
                    if ("{{ @helper::appdata($vdata)->currency_space }}" == 1) {
                        newprice = "{{ @helper::appdata($vdata)->currency }}" + ' ' + oldprice;
                    } else {

                        newprice = "{{ @helper::appdata($vdata)->currency }}" + oldprice;
                    }
                }
                return newprice;
            } else {
                if ("{{ helper::appdata($vdata)->decimal_separator }}" == 1) {
                    var oldprice = $.number(price, formate);
                    if ("{{ @helper::appdata($vdata)->currency_space }}" == 1) {
                        newprice = oldprice + ' ' + "{{ @helper::appdata($vdata)->currency }}";
                    } else {
                        newprice = oldprice + "{{ @helper::appdata($vdata)->currency }}";
                    }

                } else {
                    var oldprice = $.number(price, formate, ',', '.');
                    if ("{{ @helper::appdata($vdata)->currency_space }}" == 1) {
                        newprice = oldprice + ' ' + "{{ @helper::appdata($vdata)->currency }}";
                    } else {
                        newprice = oldprice + "{{ @helper::appdata($vdata)->currency }}";
                    }
                }
                return newprice;
            }
        }
        $('.whatsapp_icon').on("click", function(event) {
            $(".wp_chat_box").toggleClass("d-none");
        });

        // Theme-1 owlCarousel js
        $('.categories-slider').owlCarousel({
            rtl: direction == '2' ? true : false,
            loop: false,
            nav: false,
            // margin: 15,
            dots: false,
            responsive: {
                0: {
                    items: 2
                },
                600: {
                    items: 5
                },
                1024: {
                    items: 6
                },
                1000: {
                    items: 8
                }
            }
        })
        $('.blogs-slider').owlCarousel({
            rtl: direction == '2' ? true : false,
            loop: false,
            nav: false,
            margin: 15,
            dots: false,
            responsive: {
                0: {
                    items: 1
                },
                768: {
                    items: 2
                },
                1024: {
                    items: 3

                },
                1440: {
                    items: 4

                },
                1660: {
                    items: 4

                }
            }
        })
        $('.banner-imges-slider').owlCarousel({
            rtl: direction == '2' ? true : false,
            loop: false,
            nav: true,
            margin: 25,
            dots: false,
            navText: ["<i class='fa-solid fa-arrow-left-long'></i>",
                "<i class='fa-solid fa-arrow-right-long'></i>"
            ],
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 2
                },
                1024: {
                    items: 2
                },
                1660: {
                    items: 2
                }
            }
        })
        $('.banner-imges-slider-2').owlCarousel({
            rtl: direction == '2' ? true : false,
            loop: false,
            nav: true,
            margin: 25,
            dots: false,
            navText: ["<i class='fa-solid fa-arrow-left-long'></i>",
                "<i class='fa-solid fa-arrow-right-long'></i>"
            ],
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 2
                },
                1024: {
                    items: 3
                },
                1660: {
                    items: 3
                }
            }
        })

        // Theme-2 owlCarousel js
        $('.theme-2-categories').owlCarousel({
            rtl: direction == '2' ? true : false,
            loop: false,
            nav: false,
            margin: 30,
            padding: 30,
            dots: false,
            responsive: {
                0: {
                    items: 2
                },
                600: {
                    items: 3
                },
                1000: {
                    items: 5
                }
            }
        })
        $('.theme-2blogs-slider').owlCarousel({
            rtl: direction == '2' ? true : false,
            loop: false,
            nav: false,
            margin: 15,
            dots: false,
            responsive: {
                0: {
                    items: 1
                },
                768: {
                    items: 2
                },
                1024: {
                    items: 3

                },
                1440: {
                    items: 4

                },
                1660: {
                    items: 4

                }
            }
        })
        $('.cart-modal').owlCarousel({
            rtl: direction == '2' ? true : false,
            loop: false,
            nav: false,
            margin: 25,
            dots: false,
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 1
                },
                1024: {
                    items: 2
                },
                1660: {
                    items: 2
                }
            }
        })

        $('.category-slider-theme-10').owlCarousel({
            loop: false,
            margin: 16,
            nav: true,
            dots: false,
            navText: ["<i class='fa-solid fa-arrow-left-long'></i>",
                "<i class='fa-solid fa-arrow-right-long'></i>"
            ],
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 3
                },
                1000: {
                    items: 4
                },
                1200: {
                    items: 4
                }
            }
        })

        // aos js important
        AOS.init();

        AOS.init({
            // Global settings:
            disable: false,
            startEvent: 'DOMContentLoaded',
            initClassName: 'aos-init',
            animatedClassName: 'aos-animate',
            useClassNames: false,
            disableMutationObserver: false,
            debounceDelay: 50,
            throttleDelay: 99,


            // Settings that can be overridden on per-element basis, by `data-aos-*` attributes:
            offset: 120,
            delay: 0,
            duration: 400,
            easing: 'ease',
            once: false,
            mirror: false,
            anchorPlacement: 'top-bottom',

        });
    </script>
    {{-- pwa js --}}
    <!-- PWA Script E-menu -->
    <script src="{{ url('/js/pwa-manager.js') }}"></script>

    @if (App\Models\SystemAddons::where('unique_identifier', 'subscription')->first() != null &&
            App\Models\SystemAddons::where('unique_identifier', 'subscription')->first()->activated == 1)
        @if (App\Models\SystemAddons::where('unique_identifier', 'pwa')->first() != null &&
                App\Models\SystemAddons::where('unique_identifier', 'pwa')->first()->activated == 1)
            @php
                $checkplan = App\Models\Transaction::where('vendor_id', $vdata)
                    ->orderByDesc('id')
                    ->first();
                $user = App\Models\User::where('id', $vdata)->first();
                if ($user->allow_without_subscription == 1) {
                    $pwa = 1;
                } else {
                    $pwa = @$checkplan->pwa;
                }
            @endphp
            @if ($pwa == 1)
                <script>
                    // Configuration PWA E-menu
                    console.log('🚀 PWA E-menu activée pour {{ @helper::appdata($vdata)->website_title }}');
                </script>
            @endif
        @endif
    @else
        @if (App\Models\SystemAddons::where('unique_identifier', 'pwa')->first() != null &&
                App\Models\SystemAddons::where('unique_identifier', 'pwa')->first()->activated == 1)
            <script>
                // Configuration PWA E-menu (mode libre)
                console.log('🚀 PWA E-menu activée (mode libre)');
            </script>
        @endif
    @endif
    @if (App\Models\SystemAddons::where('unique_identifier', 'pixel')->first() != null &&
    App\Models\SystemAddons::where('unique_identifier', 'pixel')->first()->activated == 1)
@include('front.pixel.pixel')
@endif
    <script>
        let deferredPrompt = null;
        window.addEventListener('beforeinstallprompt', (e) => {
            $("#foo").trigger("click");
            deferredPrompt = e;
        });

        const mobile_install_app = document.getElementById('mobile-install-app');
        if (mobile_install_app != null) {
            mobile_install_app.addEventListener('click', async () => {
                if (deferredPrompt !== null) {
                    deferredPrompt.prompt();
                    const {
                        outcome
                    } = await deferredPrompt.userChoice;
                    if (outcome === 'accepted') {
                        deferredPrompt = null;

                    }
                }
            });
        }
        $('.nav02').click(function() {
            $('.mobile_drop_down').animate({
                bottom: "-100vh"
            }, 200);
        });
        $(document).ready(function() {
            window.addEventListener('beforeinstallprompt', (e) => {
                $('.install-app-btn-container').show();
                $('.mobile_drop_down').animate({
                    bottom: "0px"
                }, 200);
                deferredPrompt = e;
            });

        });

        // Enregistrer le Service Worker pour PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('Service Worker enregistré:', registration.scope);
                    })
                    .catch((error) => {
                        console.log('Erreur Service Worker:', error);
                    });
            });
        }

    </script>
    @yield('script')
</body>

</html>

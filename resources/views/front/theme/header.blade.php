<header>
    <div class="header-main fixed-top">
        @if (env('Environment') == 'sendbox')
        <div class="sale">
            <div class="container">
                <div class="d-block d-md-flex justify-content-center align-items-center">
                    <p class="text-center"> <a href="https://1.envato.market/XxMgjX" target="_blank">This is a demo website - Buy genuine Restro SaaS using our official link! Click Now >>> Buy Now</a></p>
                </div>
            </div>
        </div>
        @endif
        <div class="container">
            <div class="Navbar">
                <a href="{{ URL::to(@$storeinfo->slug) }}" class="logo mx-2">
                    <img src="{{ helper::image_path(helper::appdata(@$storeinfo->id)->logo ?? 'default-logo.png') }}" alt="">
                </a>
                <div class="d-flex align-items-center gap-3">
                    <nav class="align-items-center {{session()->get('direction') == 2 ? 'menu-rtl' : 'menu'}}">
                        <div id="deletebtn">
                            <i class="fa-solid fa-xmark"></i>
                        </div>
                        <ul class="navbar-nav header-menu-items">
                            <li class="nav-item dropdown header-dropdown-menu px-4">
                                <a class="nav-link {{ request()->is(@$storeinfo->slug) ? 'active' : '' }} {{ request()->is('/') ? 'active' : '' }}" href="{{ URL::to(@$storeinfo->slug) }}">
                                    {{ trans('labels.home') }}
                                </a>
                            </li>
                            <li class="nav-item dropdown header-dropdown-menu px-4">
                                <a class="nav-link {{ request()->is(@$storeinfo->slug.'/aboutus') ? 'active' : '' }} {{ request()->is('aboutus') ? 'active' : '' }}" href="{{ URL::to(@$storeinfo->slug . '/aboutus') }}">
                                    {{ trans('labels.about_us') }}
                                </a>
                            </li>
                            <li class="nav-item dropdown header-dropdown-menu px-4">
                                <a class="nav-link {{ request()->is(@$storeinfo->slug.'/contact') ? 'active' : '' }} {{ request()->is('contact') ? 'active' : '' }}" href="{{ URL::to(@$storeinfo->slug . '/contact') }}">
                                    {{ trans('labels.contact_us') }}
                                </a>
                            </li>

                            <li class="nav-item dropdown header-dropdown-menu px-4">
                                <a href="{{ URL::to(@$storeinfo->slug . '/tablebook') }}" class="nav-link {{ request()->is(@$storeinfo->slug.'/tablebook') ? 'active' : '' }} {{ request()->is('tablebook') ? 'active' : '' }}">
                                    {{ trans('labels.table_book') }}
                                </a>
                            </li>

                            <li class="nav-item dropdown header-dropdown-menu px-4">
                                <a href="javascript:void(0)" class="nav-link" data-bs-toggle="modal" data-bs-target="#searchModal">
                                    {{ trans('labels.search') }}
                                </a>
                            </li>
                            <li class="nav-item dropdown header-dropdown-menu px-4 d-flex align-items-center d-none d-lg-inline-block">
                                <div class="d-flex align-items-center">
                                    <a class="nav-link position-relative {{ request()->is(@$storeinfo->slug.'/cart') ? 'active' : '' }} {{ request()->is('cart') ? 'active' : '' }}" href="{{ URL::to(@$storeinfo->slug . '/cart') }}">
                                        <span>
                                            {{ trans('labels.my_cart') }}
                                        </span>
                                        <a class="cart-counting mx-2" id="cartcount">{{ helper::getcartcount($storeinfo->id, @Auth::user()->id) }}</a>
                                    </a>
                                </div>
                            </li>


                            @if (App\Models\SystemAddons::where('unique_identifier', 'customer_login')->first() != null &&
                            App\Models\SystemAddons::where('unique_identifier', 'customer_login')->first()->activated == 1)

                            @if (Auth::user() && Auth::user()->type == 3)
                            <li class="nav-item dropdown header-dropdown-menu px-4 d-flex align-items-center d-lg-none">
                                <a class="nav-link position-relative" href="{{ URL::to($storeinfo->slug . '/profile/') }}">
                                    <span>
                                        {{ trans('labels.profile') }}
                                    </span>
                                </a>
                            </li>
                            <a onclick="statusupdate('{{ URL::to($storeinfo->slug . '/logout/')}}')" class="login-button-mobile login-buuton d-lg-none cursor-pointer">{{ trans('labels.logout') }}</a>
                            @else
                                @if ((helper::appdata(@$storeinfo->id)->checkout_login_required ?? 0) == 1)
                                <a href="{{ URL::to($storeinfo->slug . '/login/') }}" class="login-button-mobile login-buuton d-lg-none">{{ trans('labels.login') }}</a>
                                @endif
                            @endif

                            @endif

                        </ul>
                    </nav>
                  @php
                    $languages = explode('|', helper::appdata(@$storeinfo->id)->languages ?? 'en');
                @endphp
                    @if (App\Models\SystemAddons::where('unique_identifier', 'language')->first() != null &&
                    App\Models\SystemAddons::where('unique_identifier', 'language')->first()->activated == 1)
  @if (count($languages) > 1)
                    <div class="btn-group">
                        <a class="nav-link d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ helper::image_path(session()->get('flag')) }}" alt="" class="language-dropdown-image">
                        </a>
                        <ul class="dropdown-menu user-dropdown-menu {{ session()->get('direction') == 2 ? 'drop-menu-rtl' : 'drop-menu' }}">

                               @foreach (helper::available_language(@$storeinfo->id) as $languagelist)
                                @if (in_array($languagelist->code, explode('|', helper::appdata(@$storeinfo->id)->languages ?? 'en')))
                            <li>
                                <a class="dropdown-item language-items d-flex text-start" href="{{ URL::to('/lang/change?lang=' . $languagelist->code) }}">
                                    <img src="{{ helper::image_path($languagelist->image) }}" alt="" class="language-items-img">
                                    <span class="px-2">{{ $languagelist->name }}</span>
                                </a>
                            </li>
                            @endif
                            @endforeach


                        </ul>
                    </div>
@endif
                    @endif


                    @if (App\Models\SystemAddons::where('unique_identifier', 'customer_login')->first() != null &&
                    App\Models\SystemAddons::where('unique_identifier', 'customer_login')->first()->activated == 1)
                        @if (Auth::user() && Auth::user()->type == 3)
                        <a class="nav-link d-flex align-items-center mx-2 mx-md-0 d-none d-lg-block text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ helper::image_path(@Auth::user()->image) }}" alt="" class="profile_image">
                        </a>
                        <ul class="dropdown-menu user-dropdown-menu">
                            <li>
                                <a class="dropdown-item language-items" href="{{ URL::to($storeinfo->slug . '/profile/') }}">
                                    <i class="fa fa-user"></i>
                                    <p>{{ trans('labels.profile') }}</p>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item language-items cursor-pointer" onclick="statusupdate('{{ URL::to($storeinfo->slug . '/logout/')}}')">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    <p>{{ trans('labels.logout') }}</p>
                                </a>
                            </li>

                        </ul>
                        @else
                            @if ((helper::appdata(@$storeinfo->id)->checkout_login_required ?? 0) == 1)
                            <a href="{{ URL::to($storeinfo->slug . '/login/') }}" class="login-buuton d-none d-lg-block">{{ trans('labels.login') }}</a>
                            @endif
                        @endif
                    @endif
                </div>
            </div>
            <div class="bg-layer"></div>
        </div>
    </div>

</header>



<!--------------- mobile menu Section start ------------------>

<div class="mobile-menu-footer  d-lg-none">
    <ul class="d-flex align-items-center mobile-menu-active p-0 m-0">
        <li class="nav-link position-relative">
            <a class="{{ request()->is(@$storeinfo->slug) ? 'active' : '' }} {{ request()->is('/') ? 'active' : '' }}" href="{{ URL::to(@$storeinfo->slug) }}">
                <i class="fa-light fa-house"></i>
                <span class="act-8">{{trans('labels.home')}}</span>
            </a>
        </li>
        <li class="nav-link position-relative">
            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#searchModal">
                <i class="fa-light fa-search"></i>
                <span class="act-8">{{trans('labels.search')}}</span>
            </a>
        </li>
        @if (request()->route()->getName() == 'front.home')
        <li class="nav-link position-relative">
            <a href="javascript:void(0)" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottom" aria-controls="offcanvasBottom">
                <i class="fa-light fa-box-archive"></i>
                <span class="act-8">{{trans('labels.menu')}}</span>
            </a>
        </li>
        @endif
        <li class="nav-link position-relative">
            <a href="{{ URL::to(@$storeinfo->slug . '/cart') }}" class="{{ request()->is(@$storeinfo->slug.'/cart') ? 'active' : '' }}">
                <i class="fa-light fa-bag-shopping position-relative">
                    <div class="cart-3 mx-2 d-lg-none " id="cartcount_mobile">{{ helper::getcartcount($storeinfo->id, @Auth::user()->id) }}</div>
                </i>
                <span>{{trans('labels.menu_cart')}}</span>
            </a>
        </li>
        <li class="nav-link position-relative">
            <a href="javascript:void(0)" class="togl-btn text-dark toggle_button">
                <i class="fa-light fa-ellipsis-vertical fs-6"></i>
                <span>{{trans('labels.more')}}</span>
            </a>
        </li>
    </ul>
</div>
<!--------------- mobile menu Section End ------------------>
@if (App\Models\SystemAddons::where('unique_identifier', 'cookie_consent')->first() != null &&
                            App\Models\SystemAddons::where('unique_identifier', 'cookie_consent')->first()->activated)
@include('cookie-consent::index')
@endif

<div class="d-lg-none mb-2">

    <div class="setting-page-profile-mobile shadow d-flex gap-3 align-items-center bg-white rounded-5 p-2 mb-2">

        <img src="{{ helper::image_path(@Auth::user()->image) }}" alt="">

        <div class="">

            <h5 class="mb-1">{{ @Auth::user()->name }}</h5>

            <a>{{ @Auth::user()->email }}</a>

        </div>

    </div>

    <div class="accordion accordion-flush d-lg-none" id="mobileaccountmenu">

        <div class="accordion-item border rounded-5  shadow overflow-hidden my-0">

            <h2 class="accordion-header">

                <button class="accordion-button fw-500 bg-white accordion_button d-flex gap-2 align-items-center collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">

                    <i class="fa-light fa-bars-staggered"></i>

                    <p class="fw-600">Dashboard Navigation</p>

                </button>

            </h2>

            <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#mobileaccountmenu">

                <div class="accordion-body border-top">

                    <!--------- ACCOUNT MENU --------->

                    <div class="account_menu">

                        <p class="setting-left-sidetitle mt-0">{{ trans('labels.account') }}</p>

                        <ul class="setting-left-sidebar mt-0">

                            <li>

                                <a href="{{ URL::to($storeinfo->slug . '/profile/') }}">

                                    <i class="fa-regular fa-user"></i>

                                    <span class="px-3">{{ trans('labels.profile') }}</span>

                                </a>

                            </li>

                            <li>

                                @if(@Auth::user()->google_id == "" && @Auth::user()->facebook_id == "")

                                <a href="{{ URL::to($storeinfo->slug . '/change-password/') }}">

                                    <i class="fa-solid fa-lock"></i>

                                    <span class="px-3">{{ trans('labels.change_password') }}</span>

                                </a>

                                @endif

                            </li>

                        </ul>

                        <p class="setting-left-sidetitle mt-0">{{ trans('labels.dashboard') }}</p>

                        <ul class="setting-left-sidebar mt-0">

                            <li>

                                <a href="{{ URL::to($storeinfo->slug . '/orders/') }}">

                                    <i class="fa-solid fa-cart-shopping"></i>

                                    <span class="px-3">{{ trans('labels.orders') }}</span>

                                </a>

                            </li>

                            @if (App\Models\SystemAddons::where('unique_identifier', 'loyalty_program')->first() != null &&

                                    App\Models\SystemAddons::where('unique_identifier', 'loyalty_program')->first()->activated == 1)

                                <li>

                                    <a href="{{ URL::to($storeinfo->slug . '/loyality/') }}">

                                        <i class="fa-solid fa-trophy"></i>

                                        <span class="px-3">{{ trans('labels.loyalty_program') }}</span>

                                    </a>

                                </li>

                            @endif
              
                            <li>

                                <a href="{{ URL::to($storeinfo->slug . '/favorites/') }}">

                                    <i class="fa-regular fa-heart"></i>

                                    <span class="px-3">{{ trans('labels.favourites') }}</span>

                                </a>

                            </li>
                             <li>

                                <a href="{{ URL::to($storeinfo->slug . '/delete-password/') }}">

                                    <i class="fa-light fa-trash"></i>

                                    <span class="px-3">{{ trans('labels.delete_profile') }}</span>

                                </a>

                            </li>

                            <li class="cursor-pointer">

                                <a onclick="statusupdate('{{ URL::to($storeinfo->slug . '/logout/')}}')">

                                    <i class="fa-solid fa-right-from-bracket"></i>

                                    <span class="px-3">{{ trans('labels.logout') }}</span>

                                </a>

                            </li>

                        </ul>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>



<div class="col-md-3 d-lg-block d-none">

    <div class="setting-page-profile">

        <img src="{{ helper::image_path(@Auth::user()->image) }}" alt="" class="mb-3">

        <h3 class="mb-1">{{ @Auth::user()->name }}</h3>

        <a>{{ @Auth::user()->email }}</a>

    </div>

    <p class="setting-left-sidetitle">{{ trans('labels.account') }}</p>

    <ul class="setting-left-sidebar">

        <li>

            <a href="{{ URL::to($storeinfo->slug . '/profile/') }}">

                <i class="fa-regular fa-user"></i>

                <span class="px-3">{{ trans('labels.profile') }}</span>

            </a>

        </li>

        <li>

            @if(@Auth::user()->google_id == "" && @Auth::user()->facebook_id == "")

            <a href="{{ URL::to($storeinfo->slug . '/change-password/') }}">

                <i class="fa-solid fa-lock"></i>

                <span class="px-3">{{ trans('labels.change_password') }}</span>

            </a>

            @endif

        </li>

    </ul>

    <p class="setting-left-sidetitle">{{ trans('labels.dashboard') }}</p>

    <ul class="setting-left-sidebar">

        <li>

            <a href="{{ URL::to($storeinfo->slug . '/orders/') }}">

                <i class="fa-solid fa-cart-shopping"></i>

                <span class="px-3">{{ trans('labels.orders') }}</span>

            </a>

        </li>

        @if (App\Models\SystemAddons::where('unique_identifier', 'loyalty_program')->first() != null &&

            App\Models\SystemAddons::where('unique_identifier', 'loyalty_program')->first()->activated == 1)

        <li>

            <a href="{{ URL::to($storeinfo->slug . '/loyality/') }}">

                <i class="fa-solid fa-trophy"></i>

                <span class="px-3">{{ trans('labels.loyalty_program') }}</span>

            </a>

        </li>

        @endif

        <li>

            <a href="{{ URL::to($storeinfo->slug . '/favorites/') }}">

                <i class="fa-regular fa-heart"></i>

                <span class="px-3">{{ trans('labels.favourites') }}</span>

            </a>

        </li>
 <li>

                                <a href="{{ URL::to($storeinfo->slug . '/delete-password/') }}">

                                    <i class="fa-light fa-trash"></i>

                                    <span class="px-3">{{ trans('labels.delete_profile') }}</span>

                                </a>

                            </li>
        <li class="cursor-pointer">

            <a onclick="statusupdate('{{ URL::to($storeinfo->slug . '/logout/')}}')">

                <i class="fa-solid fa-right-from-bracket"></i>

                <span class="px-3">{{ trans('labels.logout') }}</span>

            </a>

        </li>

    </ul>

</div>















<div class="offcanvas offcanvas-start bg-light offcanvas-width" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">

    <div class="offcanvas-header justify-content-end mx-4">

        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>

    </div>

    <div class="offcanvas-body px-5">

        <div class="setting-page-profile">

            <img src="{{ helper::image_path(@Auth::user()->image) }}" alt="" class="mb-">

            <h3 class="mb-1">{{ @Auth::user()->name }}</h3>

            <a>{{ @Auth::user()->email }}</a>

        </div>

        <p class="setting-left-sidetitle">{{ trans('labels.account') }}</p>

        <ul class="setting-left-sidebar">

            <li>

                <a href="{{ URL::to($storeinfo->slug . '/profile/') }}">

                    <i class="fa-regular fa-user"></i>

                    <span class="px-3">{{ trans('labels.profile') }}</span>

                </a>

            </li>

            <li>

                @if(@Auth::user()->google_id == "" && @Auth::user()->facebook_id == "")

                <a href="{{ URL::to($storeinfo->slug . '/change-password/') }}">

                    <i class="fa-solid fa-lock"></i>

                    <span class="px-3">{{ trans('labels.change_password') }}</span>

                </a>

                @endif

            </li>

        </ul>

        <p class="setting-left-sidetitle">{{ trans('labels.dashboard') }}</p>

        <ul class="setting-left-sidebar">

            <li>

                <a href="{{ URL::to($storeinfo->slug . '/orders/') }}">

                    <i class="fa-solid fa-cart-shopping"></i>

                    <span class="px-3">{{ trans('labels.orders') }}</span>

                </a>

            </li>

            <li>

                <a href="{{ URL::to($storeinfo->slug . '/favorites/') }}">

                    <i class="fa-regular fa-heart"></i>

                    <span class="px-3">{{ trans('labels.favourites') }}</span>

                </a>

            </li>

            <li>

                <a href="{{ URL::to($storeinfo->slug . '/logout/') }}">

                    <i class="fa-solid fa-right-from-bracket"></i>

                    <span class="px-3">{{ trans('labels.logout') }}</span>

                </a>

            </li>

        </ul>

    </div>

</div>
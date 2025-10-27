@extends('front.theme.default')
@section('content')
<!-- breadcrumb start -->
<div class="breadcrumb-sec">
    <div class="container">
        <nav class="mx-2">
            <h3 class="page-title text-white mb-2"> {{ trans('labels.checkout') }}</h3>
            <ol class="breadcrumb d-flex text-capitalize">
                <li class="breadcrumb-item"><a href="{{URL::to(@$storeinfo->slug)}}" class="text-white"> {{trans('labels.home')}}</a></li>
                <li class="breadcrumb-item active {{session()->get('direction') == 2 ? 'breadcrumb-rtl' : ''}}"> {{ trans('labels.checkout') }}</li>
            </ol>
        </nav>
    </div>
</div>
<!-- breadcrumb end -->
<section class="py-sm-5 py-4">
    <div class="container">
        <div class="row g-sm-3 g-0">
            <div class="col-md-12 col-lg-8">
                <div class="border shadow rounded-4 py-0 mb-4">
                    @php
                    $total_price = 0;

                    @endphp
                    @foreach ($cartdata as $cart)
                    <?php

                    $total_price += ($cart->price);

                    ?>
                    @endforeach
                    <div class="card border-0 select-delivery rounded-4">
                        <div class="card-body row">
                            <div class="radio-item-container px-sm-2 px-0">
                                <div class="d-flex align-items-center mb-3 px-0 border-bottom pb-2">
                                    <i class="fa-solid fa-truck"></i>
                                    <p class="title px-2">{{ trans('labels.delivery_option') }}</p>
                                </div>
                                <form class="px-3">
                                    @php
                                    $delivery_types = explode(',', helper::appdata(@$vdata)->delivery_type);
                                    if(Session::has('table_id'))
                                    {
                                    $delivery_types = array(3);
                                    }
                                    @endphp
                                    @foreach ($delivery_types as $key => $delivery_type)
                                    <div class="col-12 px-0 mb-2">
                                        <label class="form-check-label d-flex mx-0 justify-content-between align-items-center" for="cart-delivery-{{$delivery_type}}">
                                            <div class="d-flex align-items-center">
                                                <input class="form-check-input m-0" type="radio" name="cart-delivery" id="cart-delivery-{{$delivery_type}}" value="{{$delivery_type}}" {{ $key == 0 ? 'checked' : ''}}>
                                                <p class="px-2">
                                                    @if($delivery_type == 1)
                                                    {{ trans('labels.delivery') }}
                                                    @elseif($delivery_type == 2)
                                                    {{ trans('labels.pickup') }}
                                                    @elseif($delivery_type == 3)
                                                    {{ trans('labels.dine_in') }}
                                                    @endif
                                                </p>

                                            </div>
                                        </label>
                                    </div>
                                    @endforeach
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="border shadow rounded-4 py-0 mb-4" id="data_time">
                    <div class="card border-0 select-delivery rounded-4">
                        <div class="card-body">
                            <form action="#" method="get">
                                <div class="row gx-sm-3 gx-0">
                                    <div class="d-flex align-items-center mb-3 border-bottom pb-2">
                                        <i class="fa-regular fa-clock"></i>
                                        <p class="title px-2">{{ trans('labels.date_time') }}</p>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="Name" class="form-label" id="delivery_date">{{ trans('labels.delivery_date') }}<span class="text-danger"> * </span></label>
                                        <label for="Name" class="form-label" id="pickup_date">{{ trans('labels.pickup_date') }}<span class="text-danger"> * </span></label>
                                        <input type="date" class="form-control input-h" id="delivery_dt" value="" placeholder="Delivery date" required min="{{date('Y-m-d')}}">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="Name" class="form-label" id="delivery">{{ trans('labels.delivery_time') }}<span class="text-danger"> * </span></label>
                                        <label for="Name" class="form-label" id="pickup">{{ trans('labels.pickup_time') }}<span class="text-danger"> * </span></label>
                                        <label id="store_close" class="d-none text-danger">{{ trans('labels.today_store_closed') }}</label>
                                        <input type="hidden" name="store_id" id="store_id" value="{{ @$vdata }}">
                                        <input type="hidden" name="sloturl" id="sloturl" value="{{ URL::to(@$storeinfo->slug . '/timeslot') }}">
                                        <select name="delivery_time" id="delivery_time" class="form-select input-h" required>
                                            <option value="{{ old('delivery_time') }}">{{ trans('labels.select') }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="border shadow rounded-4 py-0 mb-4" id="table_show">
                    <div class="card border-0 select-delivery rounded-4">
                        <div class="card-body">
                            <form action="#" method="get">
                                <div class="row gx-sm-3 gx-0">
                                    <div class="d-flex align-items-center mb-3 border-bottom pb-2">
                                        <i class="fa-solid fa-utensils"></i>
                                        <p class="title px-2">{{ trans('labels.table') }}</p>
                                    </div>
                                    <div class="col-md-12 mb-4">
                                        <label for="Name" class="form-label" id="delivery">{{ trans('labels.table') }}<span class="text-danger"> * </span></label>
                                        <select name="table" id="table" class="form-select input-h" @if(Session::has('table_id')) disabled @endif required>
                                            <option value="">{{ trans('labels.select_table') }}
                                            </option>
                                            @foreach ($tableqrs as $tableqr)
                                            <option value="{{$tableqr->id}}" {{@Session::get('table_id') == $tableqr->id ? 'selected' : ''}}> {{ $tableqr->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="border shadow rounded-4 py-0 mb-4" id="open">
                    <div class="card border-0 select-delivery rounded-4">
                        <div class="card-body">
                            <form action="#" method="get">
                                <div class="row gx-sm-3 gx-0">
                                    <div class="d-flex align-items-center mb-3 border-bottom pb-2">
                                        <i class="fa-regular fa-circle-question"></i>
                                        <p class="title px-2">{{ trans('labels.delivery_info') }}</p>
                                    </div>
                                    <div class="col-md-12 mb-4">
                                        <label for="validationDefault" class="form-label">{{ trans('labels.delivery_area') }}<span class="text-danger"> * </span></label>
                                        <select name="delivery_area" id="delivery_area" class="form-control">
                                            <option value="" price="{{ 0 }}">
                                                {{ trans('labels.select') }}
                                            </option>
                                            @foreach ($deliveryarea as $area)
                                            <option value="{{ $area->name }}" price="{{ $area->price }}">
                                                {{ $area->name }} -
                                                {{ helper::currency_formate($area->price, @$vdata) }}
                                            </option>
                                            @endforeach

                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="validationDefault" class="form-label">{{ trans('labels.address') }}<span class="text-danger"> * </span></label>
                                        <input type="text" class="form-control input-h" name="address" id="address" placeholder="{{ trans('labels.address') }}">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="validationDefault" class="form-label">{{ trans('labels.landmark') }}<span class="text-danger"> </span></label>
                                        <input type="text" class="form-control input-h" name="landmark" id="landmark" placeholder="{{ trans('labels.landmark') }}">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="validationDefault" class="form-label">{{ trans('labels.building') }}</label>
                                        <input type="text" class="form-control input-h" name="building" id="building" placeholder="{{ trans('labels.building') }}">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="validationDefault" class="form-label">{{ trans('labels.pincode') }}</label>
                                        <input type="number" class="form-control input-h" placeholder="{{ trans('labels.pincode') }}" name="postal_code" id="postal_code">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="border shadow rounded-4 py-0 mb-4">
                    <div class="card border-0 select-delivery rounded-4">
                        <div class="card-body">
                            <form action="#" method="get">
                                <div class="row gx-sm-3 gx-0">
                                    <div class="d-flex align-items-center mb-3 border-bottom pb-2">
                                        <i class="fa-regular fa-address-card"></i>
                                        <p class="title px-2">{{ trans('labels.customer') }}</p>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="validationDefault" class="form-label">{{ trans('labels.name') }}<span class="text-danger">* </span></label>
                                        <input type="text" class="form-control input-h" placeholder="{{ trans('labels.name') }}" name="customer_name" id="customer_name" value="{{ @Auth::user() && @Auth::user()->type == 3 ? @Auth::user()->name : '' }}">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="validationDefault" class="form-label">{{ trans('labels.mobile') }}<span class="text-danger"> * </span></label>
                                        <input type="number" class="form-control input-h" placeholder="{{ trans('labels.mobile') }}" name="customer_mobile" id="customer_mobile" value="{{ @Auth::user() && @Auth::user()->type == 3 ? @Auth::user()->mobile : '' }}">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="validationDefault" class="form-label">{{ trans('labels.email') }}<span class="text-danger">* </span></label>
                                        <input type="email" class="form-control input-h" placeholder="{{ trans('labels.email') }}" name="customer_email" id="customer_email" value="{{ @Auth::user() && @Auth::user()->type == 3 ? @Auth::user()->email : '' }}">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">{{ trans('labels.note') }}<span class="text-danger"> </span></label>
                                        <textarea id="notes" name="notes" class="form-control input-h" rows="5" aria-label="With textarea" placeholder="{{ trans('labels.message') }}" value=""></textarea>
                                    </div>
                                    <input type="hidden" id="vendor" name="vendor" value="{{ $vdata }}" />
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-4">
                <input type="hidden" id="discount_amount" value="{{Session::get('offer_amount')}}" />
                <input type="hidden" id="offer_type" value="{{Session::get('offer_type')}}" />
                <input type="hidden" name="couponcode" id="couponcode" value="{{ Session::get('offer_code') }}">
                @if (App\Models\SystemAddons::where('unique_identifier', 'subscription')->first() != null &&
                App\Models\SystemAddons::where('unique_identifier', 'subscription')->first()->activated == 1)
                @if (App\Models\SystemAddons::where('unique_identifier', 'coupon')->first() != null &&
                App\Models\SystemAddons::where('unique_identifier', 'coupon')->first()->activated == 1)
                @php
                if(helper::vendordata(@$vdata)->allow_without_subscription == 1)
                {
                $promocode = 1;
                }
                else {
                $promocode = helper::get_plan(@$vdata)->coupons;
                }
                @endphp
                @if($promocode == 1)
                <div class="border shadow rounded-4 py-0 mb-4 @if(@$coupons->count() == 0 || Session::get('offer_type') == 'loyalty') d-none @endif" id="promocodesection">
                    <div class="card border-0 select-delivery rounded-4">
                        <div class="card-body row px-sm-3 px-2 justify-content-between align-items-center">
                            <div class="col-md-6 col-lg-12 col-xl-7 px-0">
                                <div>
                                    <p class="title border-bottom px-2 pb-2 mb-2"><i class="fa-solid fa-receipt"></i><span class="px-2" id="promocode_code">@if (Session::has('offer_amount')) {{ Session::get('offer_code') }} {{ trans('labels.applied') }} @else {{ trans('labels.apply_coupon') }} @endif </span></p>
                                    <p class="apply-coupon-subtitle col-10" id="promocode_desc">{{ trans('labels.coupon_message') }}</p>
                                </div>
                            </div>
                            <input type="hidden" id="removecouponurl" value="{{ URL::to('/cart/removepromocode') }}" />
                            <div class="col-md-6 col-lg-12 col-xl-5 d-md-flex d-lg-block d-xl-flex justify-content-end px-2" id="promocode_button">
                                @if (Session::has('offer_amount'))
                                <a class=" text-danger" href="javascript:void(0)" onclick="RemoveCopon()"> {{ trans('labels.remove') }}</a>
                                @else
                                <a class="btn-primary d-inline-bloc text-center fs-7 mt-3 mobile-viwe-btn mt-md-0 mt-lg-3 mt-xl-0 mt-xxl-0" href="#" type="button" @if(@$coupons->count() > 0) data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight" @endif>{{ trans('labels.offer') }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if(@Auth::user()->id)
                <div class="border shadow rounded-4 py-0 mb-4 @if(Session::get('offer_type') == 'promocode') d-none @endif" id="loyaltysection">
                    <div class="card border-0 select-delivery rounded-4">
                        <div class="card-body px-sm-3 px-2">
                            <div class="d-flex align-items-start border-bottom pb-2 px-0">
                                <div>
                                    <p class="title px-2"><i class="fa-solid fa-trophy"></i><span class="px-2" id="loyalty_program">{{ trans('labels.loyalty_program') }}</span></p>
                                </div>
                            </div>
                            <div class="row mt-2 align-items-center">
                                <p class="apply-coupon-subtitle col-10" id="loyalty_desc">{{ trans('labels.you_have_currently') }} <b>{{ @loyaltyhelper::availablepoints(@Auth::user()->id,@$vdata) }}</b> {{ trans('labels.use_it_on_order') }}</p>
                                <div class="px-2 py-2">
                                    <h5>1 {{ trans('labels.point') }} = {{ helper::currency_formate(@loyaltyhelper::getloyaltydata(@$vdata)->per_coin_amount, @$vdata) }}</h5>

                                    @if(loyaltyhelper::availablepoints(@Auth::user()->id,@$vdata) > 0)
                                    <div class="d-flex mt-2 gap-2">
                                        <input type="text" class="form-control input-h" name="points" id="points" placeholder="{{ trans('labels.enter_point') }}" value="{{Session::get('offer_code')}}" @if(Session::get('offer_type')=='loyalty' ) readonly @endif>
                                        <input type="hidden" id="applyredeempoints" value="{{ URL::to('/cart/applyredeempoints') }}" />
                                        <input type="hidden" id="removeredeempoints" value="{{ URL::to('/cart/removeredeempoints') }}" />
                                        <div id="points_button">
                                            @if (Session::has('offer_type') == "loyalty")
                                            <button class="copybtn btn-primary" href="javascript:void(0)" onclick="RemovePoints()">{{ trans('labels.remove') }}</button>
                                            @else
                                            <button class="btn-primary btn" onclick="RedeemPoints('{{@$vdata}}')">{{ trans('labels.redeem') }}</button>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @endif
                @else
                @if (App\Models\SystemAddons::where('unique_identifier', 'coupon')->first() != null &&
                App\Models\SystemAddons::where('unique_identifier', 'coupon')->first()->activated == 1)
                <div class="border shadow rounded-4 py-0 mb-4">
                    <div class="card border-0 select-delivery rounded-4">
                        <div class="card-body row justify-content-between align-items-center">
                            <div class="d-flex align-items-start col-md-6 col-lg-12 col-xl-7 px-0">
                                <div>
                                    <p class="title px-2 mb-2"><i class="fa-solid fa-receipt"></i><span class="px-2" id="promocode_code">@if (Session::has('offer_amount')) {{ Session::get('offer_code') }} applied @else {{ trans('labels.apply_coupon') }} @endif </span></p>
                                    <p class="apply-coupon-subtitle col-10" id="promocode_desc">{{ trans('labels.coupon_message') }}</p>
                                </div>
                            </div>
                            <input type="hidden" id="removecouponurl" value="{{ URL::to('/cart/removepromocode') }}" />
                            <div class="col-md-6 col-lg-12 col-xl-5 d-md-flex d-lg-block d-xl-flex justify-content-end px-2" id="promocode_button">
                                @if (Session::has('offer_amount'))
                                <a class=" text-danger" href="javascript:void(0)" onclick="RemoveCopon()"> {{ trans('labels.remove') }}</a>
                                @else
                                <a class="btn-primary d-inline-bloc text-center fs-7 mt-3 mobile-viwe-btn mt-md-0 mt-lg-3 mt-xl-0 mt-xxl-0" href="#" type="button" @if(@$coupons->count() > 0) data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight" @endif>{{ @$coupons->count() }}
                                    {{ trans('labels.offer') }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @endif

                <div class="border shadow rounded-4 py-0 mb-4">
                    <div class="card border-0 select-delivery rounded-4">
                        <div class="card-body row gx-sm-3 gx-0">
                            <div class="d-flex align-items-center border-bottom pb-2">
                                <i class="fa-solid fa-basket-shopping"></i>
                                <p class="title px-2">{{ trans('labels.order_summary') }}</p>
                            </div>
                            <div class="col">

                                <ul class="list-group list-group-flush order-summary-list" id="payment_summery_list">
                                    <li class="list-group-item">
                                        {{ trans('labels.sub_total') }}
                                        <span>
                                            {{ helper::currency_formate($total_price, @$vdata) }}
                                        </span>
                                    </li>

                                    @php
                                    $totalcarttax = 0;
                                    @endphp
                                    @foreach ($taxArr['tax'] as $k => $tax)
                                    @php
                                    $rate = $taxArr['rate'][$k];
                                    $totalcarttax += (float) $taxArr['rate'][$k];
                                    @endphp
                                    <li class="list-group-item" id="tax_list">
                                        {{ $tax }}
                                        <span>
                                            {{ helper::currency_formate($rate, $vdata) }}

                                        </span>
                                    </li>
                                    @endforeach
                                    <li class="list-group-item" id="shipping_charge_hide">
                                        {{ trans('labels.delivery_charge') }} (+)
                                        <span id="shipping_charge">

                                            {{ helper::currency_formate('0.0', @$vdata) }}
                                        </span>
                                    </li>
                                    @php
                                    $grand_total = 0;
                                    $total_coupons = $coupons->count();
                                    @endphp
                                    @if(Session::has('offer_amount'))
                                    @php
                                    $grand_total = ($total_price - Session::get('offer_amount')) + $totalcarttax ;
                                    @endphp
                                    @else
                                    @php
                                    $grand_total = $total_price + $totalcarttax ;
                                    @endphp
                                    @endif



                                    <input type="hidden" id="sub_total" value="{{$total_price}}" />

                                    <input type="hidden" id="tax" value="{{ implode('|', $taxArr['rate']) }}" />
                                    <input type="hidden" name="tax_name" id="tax_name" value="{{ implode('|', $taxArr['tax']) }}">
                                    <input type="hidden" name="totaltax" id="totaltax" value="{{ $totalcarttax }}">
                                    <input type="hidden" name="delivery_charge" id="delivery_charge" value="0">
                                    <input type="hidden" name="grand_total" id="grand_total" value="{{$grand_total}}">
                                       @if (Session::has('offer_amount'))

                                    <li class="list-group-item" id="discount_1">
                                        @if (Session::get('offer_type') == "loyalty") {{trans('labels.loyalty_discount')}}  (-) @else {{ trans('labels.discount') }}  (-) @endif
                                        <span>
                                            {{ helper::currency_formate(Session::get('offer_amount'), @$vdata) }}
                                        </span>
                                    </li>

                                    @endif
                                    <li class="list-group-item fw-700 text-success">
                                        {{ trans('labels.grand_total') }}
                                        <span class="fw-700 text-success" id="grand_total_view">
                                            {{ helper::currency_formate($grand_total, @$vdata) }}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="border shadow rounded-4 py-0 mb-4">
                    <div class="card border-0 select-delivery rounded-4">
                        <div class="card-body">
                            <div class="radio-item-container px-sm-2 px-0">
                                <div class="d-flex align-items-center border-bottom pb-2">
                                    <i class="fa-solid fa-money-check-dollar"></i>
                                    <p class="title px-2"> {{ trans('labels.payment_option') }}</p>
                                </div>
                                <form>
                                    @foreach ($paymentlist as $key => $payment)
                                    @php $transaction__type = $payment->payment_type; @endphp
                                    <div class="col-12 select-payment-list-items">
                                        <label class="form-check-label d-flex  justify-content-between align-items-center" for="{{ $payment->payment_name }}">
                                            <div class="d-flex align-items-center">
                                                <input class="form-check-input m-0" type="radio" id="{{ $payment->payment_type }}" name="payment" data-payment_type="{{ $payment->payment_type }}" data-currency="{{ $payment->currency }}" @if (!$key) {!! 'checked' !!} @endif value="{{ $payment->public_key }}">
                                                <p class="px-2">{{ $payment->payment_name }}</p>
                                            </div>
                                            <img src="{{ helper::image_path($payment->image) }}" alt="" class="select-paymentimages">


                                            @if ( $payment->payment_type  == '2')
                                            <input type="hidden" name="razorpay" id="razorpay" value="{{ $payment->public_key }}">
                                            @endif

                                            @if ( $payment->payment_type == '3')
                                            <input type="hidden" name="stripekey" id="stripekey" value="{{ $payment->public_key }}">
                                            <input type="hidden" name="stripecurrency" id="stripecurrency" value="{{ $payment->currency }}">

                                            @endif
                                            @if ( $payment->payment_type == '4')
                                            <input type="hidden" name="flutterwavekey" id="flutterwavekey" value="{{ $payment->public_key }}">
                                            @endif
                                            @if ( $payment->payment_type == '5')
                                            <input type="hidden" name="paystackkey" id="paystackkey" value="{{ $payment->public_key }}">
                                            @endif
                                            @if ($payment->payment_type == '6')
                                            <input type="hidden" value="{{ $payment->payment_description }}"
                                                id="bank_payment">
                                        @endif
                                            @if ($payment->payment_type == '16')
                                            <input type="hidden" name="cinetpaykey" id="cinetpaykey" value="{{ $payment->public_key }}">
                                            <input type="hidden" name="cinetpaysecret" id="cinetpaysecret" value="{{ $payment->secret_key }}">
                                        @endif

                                        </label>
                                    </div>

                                    @endforeach

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <button target="_blank" class="btn-primary text-center w-100" onclick="Order()">{{ trans('labels.place_order') }}</button>
            </div>
        </div>
    </div>
</section>
<input type="hidden" id="table_required" value="{{ trans('messages.table_required') }}">
<input type="hidden" id="delivery_time_required" value="{{ trans('messages.delivery_time_required') }}">
<input type="hidden" id="delivery_date_required" value="{{ trans('messages.delivery_date_required') }}">
<input type="hidden" id="address_required" value="{{ trans('messages.address_required') }}">
<input type="hidden" id="no_required" value="{{ trans('messages.no_required') }}">
<input type="hidden" id="landmark_required" value="{{ trans('messages.landmark_required') }}">
<input type="hidden" id="pincode_required" value="{{ trans('messages.pincode_required') }}">
<input type="hidden" id="delivery_area_required" value="{{ trans('messages.delivery_area') }}">
<input type="hidden" id="pickup_date_required" value="{{ trans('messages.pickup_date_required') }}">
<input type="hidden" id="pickup_time_required" value="{{ trans('messages.pickup_time_required') }}">
<input type="hidden" id="customer_mobile_required" value="{{ trans('messages.customer_mobile_required') }}">
<input type="hidden" id="customer_email_required" value="{{ trans('messages.customer_email_required') }}">
<input type="hidden" id="customer_name_required" value="{{ trans('messages.customer_name_required') }}">
<input type="hidden" id="currency" value="{{ helper::appdata(@$vdata)->currency }}">
<input type="hidden" id="checkplanurl" value="{{ URL::to('/orders/checkplan') }}">
<input type="hidden" id="paymenturl" value="{{ URL::to('/orders/paymentmethod') }}">
<input type="hidden" id="mecadourl" value="{{ URL::to('/orders/mercadoorderrequest') }}">
<input type="hidden" id="paypalurl" value="{{ URL::to('/orders/paypalrequest') }}">
<input type="hidden" id="myfatoorahurl" value="{{ URL::to('/orders/myfatoorahrequest') }}">
<input type="hidden" id="toyyibpayurl" value="{{ URL::to('/orders/toyyibpayrequest') }}">
<input type="hidden" id="phonepeurl" value="{{ URL::to('/orders/phoneperequest') }}">
<input type="hidden" id="paytaburl" value="{{ URL::to('/orders/paytabrequest') }}">
<input type="hidden" id="mollieurl" value="{{ URL::to('/orders/mollierequest') }}">
<input type="hidden" id="khaltiurl" value="{{ URL::to('/orders/khaltirequest') }}">
<input type="hidden" id="xenditurl" value="{{ URL::to('/orders/xenditrequest') }}">
<input type="hidden" id="payment_url" value="{{ URL::to(@$storeinfo->slug) }}/payment">
<input type="hidden" id="website_title" value="{{ helper::appdata(@$vdata)->website_title }}">
<input type="hidden" id="image" value="{{ helper::appdata(@$vdata)->image }}">
<input type="hidden" id="slug" value="{{ @$storeinfo->slug }}">
<input type="hidden" id="failure" value="{{ url()->current() }}">
<input type="hidden" name="buynow_key" id="buynow_key" value="{{ request()->get('buy_now') }}">
<form action="{{ url('/orders/paypalrequest') }}" method="post" class="d-none">
    {{ csrf_field() }}
    <input type="hidden" name="return" value="2">
    <input type="submit" class="callpaypal" name="submit">
</form>
<!-- Apply Coupon Modal Promocode -->
<div class="offcanvas  {{session()->get('direction') == 2 ? 'offcanvas-start' : 'offcanvas-end'}}" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header offcanvas-header-coupons">
        <h5 class="offcanvas-title" id="offcanvasRightLabel">{{ trans('labels.coupons_offers') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body offer-coupons">


        @foreach ($coupons as $coupon)
        @php
        $count = helper::getcouponcodecount($coupon->code,$coupon->vendor_id);

        @endphp
        <div class="row g-4">
            <div class="col px-0">
                <div class="card promo-card position-relative rounded-5 h-100">
                    @if ($count <= $coupon->limit)
                        <div class="card-body p-4">
                            <div class="row main-row">
                                <div class="coupons-imag col-12 col-md-4 col-lg-4 col-xl-4">
                                    <h1> {{$coupon->price}}%</h1>
                                    <h6 class="ms-3"> {{ trans('labels.coupons') }}</h6>

                                </div>
                                <div class="coupons-content col-12 col-md-8 col-lg-8 col-xl-8 d-md-flex justify-content-end">
                                    <div>
                                        <h2>{{ $coupon->name }}</h2>

                                        <p class="ps-7">{{ $coupon->name }}</p>
                                    </div>
                                </div>
                            </div>
                            <form class="form-group" data-copy=true>
                                <div class="copy-button rounded-5">
                                    <input type="hidden" id="applycoponurl" value="{{ URL::to('/cart/applypromocode') }}" />
                                    <input id="promocode" type="text" class="rounded-5 px-2 input-h" readonly value="{{ $coupon->code }}" />
                                    <button type="button" class="copybtn btn-primary" onclick="ApplyCopon('{{ $coupon->code }}','{{@$vdata}}')">{{ trans('labels.apply') }}</button>
                                </div>
                            </form>
                        </div>
                        @endif
                </div>
            </div>
        </div>
        @endforeach

    </div>
</div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalbankdetails" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalbankdetailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalbankdetailsLabel">{{ trans('labels.banktransfer') }}</h5>
                <button type="button" class="btn-close bg-white border-0" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form enctype="multipart/form-data" action="{{ URL::to('/orders/paymentmethod') }}" method="POST">
                <div class="modal-body">
                    @csrf

                    <input type="hidden" name="payment_type" id="payment_type" class="form-control"
                        value="">
                    <input type="hidden" name="modal_customer_name" id="modal_customer_name" class="form-control"
                        value="">
                    <input type="hidden" name="modal_customer_email" id="modal_customer_email" class="form-control"
                        value="">
                    <input type="hidden" name="modal_customer_mobile" id="modal_customer_mobile"
                        class="form-control" value="">
                    <input type="hidden" name="modal_delivery_date" id="modal_delivery_date" class="form-control"
                        value="">
                    <input type="hidden" name="modal_delivery_time" id="modal_delivery_time" class="form-control"
                        value="">
                    <input type="hidden" name="modal_delivery_area" id="modal_delivery_area" class="form-control"
                        value="">
                    <input type="hidden" name="modal_delivery_charge" id="modal_delivery_charge"
                        class="form-control" value="">
                    <input type="hidden" name="modal_address" id="modal_address" class="form-control"
                        value="">
                    <input type="hidden" name="modal_landmark" id="modal_landmark" class="form-control"
                        value="">
                    <input type="hidden" name="modal_postal_code" id="modal_postal_code" class="form-control"
                        value="">
                    <input type="hidden" name="modal_building" id="modal_building" class="form-control"
                        value="">
                    <input type="hidden" name="modal_message" id="modal_message" class="form-control"
                        value="">
                    <input type="hidden" name="modal_subtotal" id="modal_subtotal" class="form-control"
                        value="">
                    <input type="hidden" name="modal_discount_amount" id="modal_discount_amount"
                        class="form-control" value="">
                    <input type="hidden" name="modal_couponcode" id="modal_couponcode" class="form-control"
                        value="">
                    <input type="hidden" name="modal_ordertype" id="modal_ordertype" class="form-control"
                        value="">
                    <input type="hidden" name="modal_vendor_id" id="modal_vendor_id" class="form-control"
                        value="">
                    <input type="hidden" name="modal_grand_total" id="modal_grand_total" class="form-control"
                        value="">
                    <input type="hidden" name="modal_tax" id="modal_tax" class="form-control" value="">
                    <input type="hidden" name="modal_tax_name" id="modal_tax_name" class="form-control"
                        value="">
                    <input type="hidden" name="modal_order_type" id="modal_order_type" class="form-control"
                        value="">
                    <input type="hidden" name="modal_table" id="modal_table" class="form-control" value="">
                    <input type="hidden" name="modal_offer_type" id="modal_offer_type" class="form-control"
                        value="">
                    <input type="hidden" name="modal_buynow" id="modal_buynow" class="form-control"
                        value="">
                    <p>{{ trans('labels.payment_description') }}</p>
                    <hr>
                    <p class="payment_description" id="payment_description"></p>
                    <hr>
                    <div class="form-group col-md-12">
                        <label for="screenshot"> {{ trans('labels.screenshot') }} </label>
                        <div class="controls">
                            <input type="file" name="screenshot" id="screenshot"
                                class="form-control  @error('screenshot') is-invalid @enderror" required>
                            @error('screenshot')
                                <span class="text-danger"> {{ $message }} </span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger"
                        data-bs-dismiss="modal">{{ trans('labels.close') }}</button>
                    <button type="submit" class="btn btn-primary"> {{ trans('labels.save') }} </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    let storeid = "{{@$vdata}}";

    function RemoveCopon() {
        "use strict";
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success mx-1 yes-btn',
                cancelButton: 'btn btn-danger mx-1 no-btn'
            },
            buttonsStyling: false
        })
        swalWithBootstrapButtons.fire({
            title: are_you_sure,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: yes,
            cancelButtonText: no,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: $('#removecouponurl').val(),
                    method: 'post',
                    data: {
                        promocode: jQuery('#promocode').val()
                    },
                    success: function(response) {
                        $('#preloader').hide();
                        if (response.status == 1) {
                            var html;
                            let coupons = "{{ $coupons->count() }}";
                            html = ' <a class="btn-primary d-inline-bloc fs-7 mt-3 mobile-viwe-btn mt-md-0 mt-lg-3 mt-xl-0 mt-xxl-0" href="#" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">' + coupons + '  Offer(S)</a>';
                            var grand_total = (parseFloat($('#grand_total').val()) + parseFloat($('#discount_amount').val()));
                            $('#loyaltysection').show();
                            $('#discount_1').remove();
                            $('#promocode_button').html(html);
                            $('#promocode_code').html("Apply Coupon");
                            $('#discount_amount').val('');
                            $('#offer_type').val('');
                            $('#couponcode').val('');
                            $('#grand_total_view').html(currency_formate(grand_total));
                            $('#grand_total').val(grand_total);
                        } else {
                            $('#ermsg').text(response.message);
                            $('#error-msg').addClass('alert-danger');
                            $('#error-msg').css("display", "block");
                            setTimeout(function() {
                                $("#success-msg").hide();
                            }, 5000);
                        }
                    }
                });
            } else if (
                /* Read more about handling dismissals below */
                result.dismiss === Swal.DismissReason.cancel
            ) {
                Swal.DismissReason.cancel
            }
        })
    }
</script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="https://checkout.flutterwave.com/v3.js"></script>
<script src="https://js.paystack.co/v1/inline.js"></script>

<script>
// CinetPay Payment Handler
function processCinetPayPayment(paymentData) {
    $('#preloader').show();

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Process order with CinetPay
    $.ajax({
        type: 'POST',
        url: "{{ url('/orders/paymentmethod') }}",
        data: paymentData,
        success: function(response) {
            $('#preloader').hide();
            if (response.status == 1) {
                if (response.payment_url) {
                    // Redirect to CinetPay payment page
                    window.location.href = response.payment_url;
                } else {
                    // Regular success redirect
                    if (response.url) {
                        location.href = response.url;
                    }
                }
            } else {
                toastr.error(response.message);
            }
        },
        error: function(xhr, status, error) {
            $('#preloader').hide();
            toastr.error('Payment processing failed. Please try again.');
        }
    });
}

// Add CinetPay to existing payment handling
$(document).ready(function() {
    // Override existing form submission for CinetPay
    $('#checkout-form').on('submit', function(e) {
        var payment_type = $('#payment_type').val();

        if (payment_type == '16') {
            e.preventDefault();

            // Validate form first
            if (validateCheckoutForm()) {
                var formData = $(this).serialize();
                processCinetPayPayment(formData);
            }
            return false;
        }
        // Let other payment types proceed normally
    });

    // Payment method selection change
    $('input[name="payment"]').on('change', function() {
        var payment_type = $(this).data('payment_type');
        $('#payment_type').val(payment_type);

        if (payment_type == '16') {
            // Show CinetPay specific UI if needed
            console.log('CinetPay selected');
        }
    });
});

function validateCheckoutForm() {
    // Basic validation - extend as needed
    if (!$('#customer_name').val()) {
        toastr.error('Name is required');
        return false;
    }
    if (!$('#customer_email').val()) {
        toastr.error('Email is required');
        return false;
    }
    return true;
}
</script>
<script src="https://checkout.stripe.com/v2/checkout.js"></script>
<script src="https://js.stripe.com/v3/"></script>
<script src="{{ url(env('ASSETSPATHURL') . 'web-assets/js/custom/checkout.js') }}"></script>
@endsection

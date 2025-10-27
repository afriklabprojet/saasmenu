@extends('front.theme.default')
@section('content')
<!-- breadcrumb start -->
<div class="breadcrumb-sec">
    <div class="container">
        <nav class="px-2">
            <h3 class="page-title text-white mb-2">{{trans('labels.orders')}}</h3>
            <ol class="breadcrumb d-flex text-capitalize">
                <li class="breadcrumb-item"><a href="{{URL::to(@$storeinfo->slug)}}" class="text-white"> {{trans('labels.home')}}</a></li>
                <li class="breadcrumb-item active {{session()->get('direction') == 2 ? 'breadcrumb-rtl' : ''}}">{{trans('labels.orders')}}</li>
            </ol>
        </nav>
    </div>
</div>
<!-- breadcrumb end -->
<!-- Orders section end -->
<section class="bg-light mt-0 py-sm-5 py-4">
    <div class="container">
        <div class="row gx-sm-3 gx-2">
            @include('front.theme.user_sidebar')
            <div class="col-md-12 col-lg-9">
                <div class="card shadow border-0 rounded-5">
                    <div class="card-body py-4">
                        <div class=" px-sm-3">
                            <div class="">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h2 class="page-title mb-0">{{trans('labels.orders')}}</h2>
                                    <div class="dropdown">
                                        <a class="btn btn-primary not-hover-secondary dropdown-toggle px-3 py-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Status
                                        </a>
                                        <ul class="dropdown-menu custom">
                                            <li><a class="dropdown-item" href="{{ URL::to($storeinfo->slug.'/orders?type=preparing') }}">{{ trans('labels.preparing') }}</a></li>
                                            <li><a class="dropdown-item" href="{{ URL::to($storeinfo->slug.'/orders?type=cancelled') }}">{{ trans('labels.cancelled') }}</a></li>
                                            <li><a class="dropdown-item" href="{{ URL::to($storeinfo->slug.'/orders?type=delivered') }}">{{ trans('labels.delivered') }}</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <p class="page-subtitle mt-2 mb-0 line-limit-2">{{trans('labels.orders_desc')}}</p>
                            </div>
                        </div>
                        <!-- data table start -->
                        @if(count($getorders) > 0)
                        <div class="row gap-2 py-3 px-sm-3">
                            @php $i = 1; @endphp
                            @foreach ($getorders as $orderdata)
                            <div class="card border-1 rounded-3 px-sm-3 px-0">
                                <div class="card-body">
                                    <div class="d-flex flex-wrap justify-content-between mb-2">
                                        <div class="d-flex flex-wrap align-items-center">
                                            <p class="fs-6 fw-600">#</p>
                                            <a href="{{ URL::to($storeinfo->slug . '/track-order/' . $orderdata->order_number) }}" class="fs-6 fw-600">
                                                {{ $orderdata->order_number }}
                                            </a>
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center">
                                            <span class="fs-7 fw-400 text-muted">{{ helper::date_format($orderdata->created_at,$orderdata->vendor_id) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center mb-1">
                                            <p class=" text-muted fs-7 fw-400">{{ trans('labels.payment_type') }}&nbsp;:&nbsp;</p>
                                            <span class="fs-7 fw-500">
                                             @if ($orderdata->payment_type == 6)
                                               {{ @helper::getpayment($orderdata->payment_type, $orderdata->vendor_id)->payment_name }}
                                                : <small><a href="{{ helper::image_path($orderdata->screenshot) }}" target="_blank" class="text-danger">{{ trans('labels.click_here') }}</a></small>
                                            @else
                                                 {{ @helper::getpayment($orderdata->payment_type, $orderdata->vendor_id)->payment_name }}
                                             @endif
                                                @if (in_array($orderdata->payment_type, [2, 3, 4, 5, 7, 8, 9, 10, 16]))
                                                : {{ $orderdata->payment_id }}
                                                @endif
                                            </span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <p class=" text-muted fw-400">{{ trans('labels.grand_total') }}&nbsp;:&nbsp;</p>
                                            <span class="fs-7 fw-500">{{ helper::currency_formate($orderdata->grand_total, $orderdata->vendor_id) }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap justify-content-between mt-2">
                                        <div class="d-flex align-items-center">
                                            <span>
                                              {{ @helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type,$orderdata->vendor_id)->name == null ? '-' : @helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type,$orderdata->vendor_id)->name }}
                                            </span>
                                        </div>
                                        <div class="d-flex align-items-center" tooltip="{{ trans('labels.detail') }}">
                                            <a class="btn-border px-3 py-1  rounded-5" href="{{ URL::to($storeinfo->slug . '/track-order/' . $orderdata->order_number) }}">
                                            {{ trans('labels.detail') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        @include('front.nodata')
                        @endif
                        <!-- data table end -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Orders section end -->
<button class="btn account-menu btn-primary d-lg-none d-md-block" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
    <i class="fa-solid fa-bars-staggered text-white"></i>
    <span class="px-2">{{ trans('labels.account_menu') }}</span>
</button>
@endsection

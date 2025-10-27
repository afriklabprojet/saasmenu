@extends('admin.layout.default')
@php
if(Auth::user()->type == 4)
{
$vendor_id = Auth::user()->vendor_id;
}else{
$vendor_id = Auth::user()->id;
}
@endphp
@section('content')
<div class="row justify-content-between align-items-center mb-3">
    <div class="col-12 col-md-6">
        <!-- <h5 class="pages-title fs-2">{{ trans('labels.invoice') }}</h5> -->
        <h5 class="pages-title fs-2">{{ trans('labels.order_details') }}</h5>
        <div class="d-flex">

            @include('admin.layout.breadcrumb')
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="col-md-12 my-2 d-flex justify-content-end justify-content-md-end">
            <a href="{{ URL::to('admin/orders/print/' . $getorderdata->order_number) }}" tooltip="{{ trans('labels.print') }}" class="btn btn-success rounded-5 mx-2 px-3 py-2 {{ Auth::user()->type == 1 ? 'disabled' : '' }}">
                <i class="fa fa-pdf" aria-hidden="true"></i> <i class="fa-solid fa-print"></i>
            </a>

            @if ($getorderdata->status_type == 1 || $getorderdata->status_type == 2)
            <button type="button" class="btn btn-sm btn-primary px-4 dropdown-toggle" data-bs-toggle="dropdown">{{ @helper::gettype($getorderdata->status, $getorderdata->status_type, $getorderdata->order_type,$vendor_id)->name == null ? '-' : @helper::gettype($getorderdata->status, $getorderdata->status_type, $getorderdata->order_type,$vendor_id)->name }}</button>
            <div class="dropdown-menu dropdown-menu-right {{ Auth::user()->type == 1 ? 'disabled' : '' }}">
                @foreach (helper::customstauts($getorderdata->vendor_id, $getorderdata->order_type) as $status)
                <a class="dropdown-item w-auto @if ($getorderdata->status == '1') fw-600 @endif" onclick="statusupdate('{{ URL::to('admin/orders/update-' . $getorderdata->id . '-' . $status->id . '-' . $status->type) }}')">{{ $status->name }}</a>
                @endforeach
            </div>
            @endif

            <a href="{{ URL::to('/admin/orders/generatepdf/' . $getorderdata->order_number) }}" tooltip="{{ trans('labels.downloadpdf') }}" class="btn btn-secondary btn-size mx-2 px-3 py-2 "><i class="fa-solid fa-file-pdf"></i></a>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="row justify-content-between g-md-4 g-lg-4">
            <div class="{{ $getorderdata->order_type == 4 ? 'col-md-4' : 'col-md-3 ' }}">
                <div class="card border-0 mb-3 h-100 d-flex">
                    <div class="card-header d-flex align-items-center bg-transparent text-dark py-3">
                        <i class="fa-solid fa-circle-info fs-5"></i>
                        <h5 class="px-2 fw-500">{{ trans('labels.order_details') }}</h5>
                    </div>
                    <div class="card-body">

                        <div class="basic-list-group">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <p>{{ trans('labels.order_number') }}</p>
                                    <p class="text-dark fw-600">#{{ $getorderdata->order_number }}</p>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    {{ trans('labels.order_date') }}
                                    <p class="text-muted">
                                        {{ helper::date_format($getorderdata->created_at, $vendor_id) }}
                                    </p>
                                </li>
                                @if ($getorderdata->order_from != 'pos' && $getorderdata->order_type != 3)
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    {{ $getorderdata->order_type == 1 ? trans('labels.delivery_date') : trans('labels.pickup_date') }}
                                    <p class="text-muted">
                                        {{ helper::date_format($getorderdata->delivery_date, $vendor_id) }}
                                    </p>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    {{ $getorderdata->order_type == 1 ? trans('labels.delivery_time') : trans('labels.pickup_time') }}
                                    <p class="text-muted">{{ $getorderdata->delivery_time }}</p>
                                </li>
                                @endif

                                {{-- payment_type = COD : 1,RazorPay : 2, Stripe : 3, Flutterwave : 4, Paystack : 5, Mercado Pago : 7, PayPal : 8, MyFatoorah : 9, toyyibpay : 10, phonepe : 11 --}}
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    {{ trans('labels.payment_type') }}
                                    <span class="text-muted">
                                        @if ($getorderdata->payment_type == 6)
                                        {{ @helper::getpayment($getorderdata->payment_type, $getorderdata->vendor_id)->payment_name }}
                                        : <small><a href="{{ helper::image_path($getorderdata->screenshot) }}" target="_blank" class="text-danger">{{ trans('labels.click_here') }}</a></small>
                                        @else
                                        {{ @helper::getpayment($getorderdata->payment_type, $getorderdata->vendor_id)->payment_name }}
                                        @endif
                                    </span>
                                </li>
                                @if (in_array($getorderdata->payment_type, [2, 3, 4, 5, 7, 8, 9, 10]))
                                <li class="list-group-item px-0">{{ trans('labels.payment_id') }}
                                    <p class="text-muted">
                                        {{ $getorderdata->payment_id }}
                                    </p>
                                </li>
                                @endif
                            </ul>
                        </div>
                        @if ($getorderdata->notes != '')
                        <h6>{{ trans('labels.order_notes') }}</h6>
                        <small class="text-muted">{{ $getorderdata->notes }}</small>
                        @endif
                    </div>
                </div>
            </div>
            <div class="{{ $getorderdata->order_type == 4 ? 'col-md-4' : 'col-md-3' }}">
                <div class="card border-0 mb-3 h-100 d-flex">
                    <div class="card-header d-flex align-items-center bg-transparent text-dark py-3 justify-content-between">
                        <h5 class="px-2 fw-500"><i class="fa-solid fa-user fs-5"></i> {{ trans('labels.customer') }}
                        </h5>
                        <p class="text-muted cursor-pointer" onclick="editcustomerdata('{{ $getorderdata->order_number }}','{{ $getorderdata->customer_name }}','{{ $getorderdata->mobile }}','{{ $getorderdata->customer_email }}','{{ str_replace(',', '|', $getorderdata->address) }}','{{ str_replace(',', '|', $getorderdata->building) }}','{{ str_replace(',', '|', $getorderdata->landmark) }}','{{ $getorderdata->pincode }}','customer_info')">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </p>
                    </div>
                    <div class="card-body">
                        <div class="basic-list-group">
                            <div class="row">
                                <div class="basic-list-group">
                                    <ul class="list-group list-group-flush">

                                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                            <p>{{ trans('labels.name') }}</p>
                                            <p class="text-muted"> {{ $getorderdata->customer_name }}</p>
                                        </li>

                                        @if ($getorderdata->mobile != null)
                                        <li class="list-group-item px-0 d-flex justify-content-between">
                                            <p>{{ trans('labels.mobile') }}</p>
                                            <p class="text-muted">{{ $getorderdata->mobile }}</p>
                                        </li>
                                        @endif

                                        @if ($getorderdata->customer_email != null)
                                        <li class="list-group-item px-0 d-flex justify-content-between">
                                            <p>{{ trans('labels.email') }}</p>
                                            <p class="text-muted">{{ $getorderdata->customer_email }}</p>
                                        </li>
                                        @endif
                                    </ul>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if ($getorderdata->order_type != 4)
            <div class="col-md-3">
                <div class="card border-0 mb-3 h-100 d-flex">
                    <div class="card-header d-flex align-items-center bg-transparent text-dark py-3 justify-content-between">
                        <h5 class="px-2 fw-500"><i class="fa-solid fa-file-invoice fs-5"></i>
                            @if ($getorderdata->order_type == 3)
                            {{ trans('labels.other_info') }}
                            @else
                            {{ trans('labels.billing_info') }}
                            @endif
                        </h5>
                        @if ($getorderdata->order_type == 1 or $getorderdata->order_type == 2)
                        <p class="text-muted cursor-pointer" onclick="editcustomerdata('{{ $getorderdata->order_number }}','{{ $getorderdata->customer_name }}','{{ $getorderdata->mobile }}','{{ $getorderdata->customer_email }}','{{ str_replace(',', '|', $getorderdata->address) }}','{{ str_replace(',', '|', $getorderdata->building) }}','{{ str_replace(',', '|', $getorderdata->landmark) }}','{{ $getorderdata->pincode }}','delivery_info')">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </p>
                        @endif
                    </div>

                    <div class="card-body">
                        <div class="basic-list-group">
                            <div class="row">
                                @if ($getorderdata->order_type == 1)
                                <div class="col-md-12 mb-2">
                                    <div class="basic-list-group">
                                        <ul class="list-group list-group-flush">
                                            @if ($getorderdata->order_from == 'pos')
                                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                                <p>{{ trans('labels.pos') }}</p>
                                                <p class="text-muted"> {{ trans('labels.dine_in') }}</p>
                                            </li>
                                            @else
                                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                                <p>{{ trans('labels.address') }}</p>
                                                <p class="text-muted"> {{ $getorderdata->address }}</p>
                                            </li>
                                            <li class="list-group-item px-0 d-flex justify-content-between">
                                                <p>{{ trans('labels.landmark') }}</p>
                                                <p class="text-muted">{{ $getorderdata->building }},
                                                    {{ $getorderdata->landmark }}
                                                </p>
                                            </li>
                                            <li class="list-group-item px-0 d-flex justify-content-between">
                                                <p>{{ trans('labels.pincode') }}</p>
                                                <p class="text-muted"> {{ $getorderdata->pincode }}.</p>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                                @elseif ($getorderdata->order_type == 2)
                                <div class="col-md-12 mb-2">
                                    <div class="basic-list-group">
                                        <ul class="list-group list-group-flush">
                                            @if ($getorderdata->order_from == 'pos')
                                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                                <p>{{ trans('labels.order_type') }}</p>
                                                <p class="text-muted"> {{ trans('labels.takeaway') }}</p>
                                            </li>
                                            @else
                                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                                <p>{{ trans('labels.order_type') }}</p>
                                                <p class="text-muted"> {{ trans('labels.pickup') }}</p>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                                @elseif ($getorderdata->order_type == 3)
                                <div class="col-md-12 mb-2">
                                    <div class="basic-list-group">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                                <p>{{ trans('labels.table') }}</p>
                                                <p class="text-muted"> {{ $getorderdata['tableqr']->name }}</p>
                                            </li>
                                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                                <p>{{ trans('labels.size') }}</p>
                                                <p class="text-muted"> {{ $getorderdata['tableqr']->size }}
                                                    {{ trans('labels.seats') }}
                                                </p>
                                            </li>
                                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                                <p>{{ trans('labels.area') }}</p>
                                                <p class="text-muted">
                                                    {{ $getorderdata['tableqr']->area->name }}
                                                </p>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <div class="{{ $getorderdata->order_type == 4 ? 'col-md-4' : 'col-md-3' }}">
                <div class="card border-0 mb-3 h-100 d-flex">
                    <div class="card-header d-flex align-items-center bg-transparent text-dark py-3 justify-content-between">
                        <h5 class="px-2 fw-500"><i class="fa-solid fa-clipboard fs-5"></i>
                            {{ trans('labels.notes') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="basic-list-group">
                            <div class="row">
                                <div class="basic-list-group">
                                    @if ($getorderdata->vendor_note != '')
                                    <div class="alert alert-info" role="alert">
                                        {{ $getorderdata->vendor_note }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <form action="{{ URL::to('admin/orders/vendor_note') }}" method="POST">
                            @csrf
                            <div class="form-group col-md-12">
                                <label for="note"> {{ trans('labels.note') }} </label>
                                <div class="controls">
                                    <input type="hidden" name="order_id" class="form-control" value="{{ $getorderdata->order_number }}">
                                    <input type="text" name="vendor_note" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group text-end">
                                <button @if (env('Environment')=='sendbox' ) type="button" onclick="myFunction()" type="submit" @endif class="btn btn-primary"> {{ trans('labels.update') }} </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4">
    <div class="col-md-12">

        <div class="card border-0 mb-3">
            <div class="card-header d-flex align-items-center bg-transparent text-dark py-3">
                <i class="fa-solid fa-bag-shopping fs-5"></i>
                <h5 class="px-2 fw-500">Orders</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr class="fw-500">
                                <td>{{ trans('labels.image') }}</td>
                                <td>{{ trans('labels.products') }}</td>
                                <td class="text-end">{{ trans('labels.unit_cost') }}</td>
                                <td class="text-end">{{ trans('labels.qty') }}</td>
                                <td class="text-end">{{ trans('labels.sub_total') }}</td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ordersdetails as $orders)
                            @php

                            $itemprice = $orders->variants_price;

                            @endphp
                            <tr>
                                <td><img src="{{ helper::image_path($orders->item_image) }}" class="rounded-3 object-fit-cover hw-50" alt=""></td>
                                <td>{{ $orders->item_name }}
                                    @if ($orders->variants_id != '' || $orders->extras_id != '' )
                                    <br>
                                    <a href="javascript:void(0)" onclick="showaddons('{{$orders->variants_name}}','{{$orders->variants_price}}','{{$orders->extras_name}}','{{$orders->extras_price}}','{{$orders->item_name}}')"> <small class="text-dark"> {{ trans('labels.customize') }}</small></a>
                                    @endif
                                    @if ($orders->extras_id != '')
                                    <?php
                                    $extras_id = explode('|', $orders->extras_id);
                                    $extras_name = explode('|', $orders->extras_name);
                                    $extras_price = explode('|', $orders->extras_price);
                                    $extras_total_price = 0;
                                    
                                    ?>
                                    <br>
                                    @foreach ($extras_id as $key => $addons)
                                                  
                                                    @php
                                                        $extras_total_price += $extras_price[$key];
                                                    @endphp
                                                @endforeach
                                    @else
                                    @php
                                    $extras_total_price = 0;
                                    @endphp
                                    @endif
                                </td>
                                <td class="text-end">
                                    {{ helper::currency_formate($itemprice, $getorderdata->vendor_id) }}
                                    @if ($extras_total_price > 0)
                                    <br> <small class="text-muted"> +
                                        {{ helper::currency_formate($extras_total_price, $getorderdata->vendor_id) }}</small>
                                    @endif
                                </td>
                                <td class="text-end">{{ $orders->qty }}</td>
                                <td class="text-end">
                                    {{ helper::currency_formate(($itemprice + $extras_total_price) * $orders->qty, $getorderdata->vendor_id) }}
                                </td>
                            </tr>
                            @endforeach
                            <tr>
                                <td class="text-end border-0" colspan="4">
                                    <strong class="fw-600">{{ trans('labels.sub_total') }}</strong>
                                </td>
                                <td class="text-end border-0">
                                    <strong class="fw-600">{{ helper::currency_formate($getorderdata->sub_total, $getorderdata->vendor_id) }}</strong>
                                </td>
                            </tr>
                         
                            @php
                            $tax = explode('|', $getorderdata->tax);
                            $tax_name = explode('|', $getorderdata->tax_name);
                            @endphp
                            @if ($getorderdata->tax != null && $getorderdata->tax != '')
                            @foreach ($tax as $key => $tax_value)
                            <tr>
                                <td class="text-end border-0" colspan="4">
                                    <strong class="fw-600">{{ $tax_name[$key] }}</strong>
                                </td>
                                <td class="text-end border-0">
                                    <strong class="fw-600">{{ helper::currency_formate(@(float) $tax[$key], $getorderdata->vendor_id) }}</strong>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                            @if ($getorderdata->order_type == 1)
                            <tr>
                                <td class="text-end border-0" colspan="4">
                                    <strong class="fw-600">{{ trans('labels.delivery_charge') }}
                                        ({{ $getorderdata->delivery_area }}) (+)</strong>
                                </td>
                                <td class="text-end border-0">
                                    <strong class="fw-600">{{ helper::currency_formate($getorderdata->delivery_charge, $getorderdata->vendor_id) }}</strong>
                                </td>
                            </tr>
                            @endif
                               @if ($getorderdata->discount_amount > 0)
                            <tr>
                          
                                <td class="text-end border-0" colspan="4">
                                    <strong class="fw-600">
                                        @if ($getorderdata->offer_type == 'loyalty')
                                        {{ trans('labels.loyalty_discount') }} (-)
                                        @endif

                                        @if ($getorderdata->order_type == 4)
                                        {{ trans('labels.discount') }} (-)
                                        @else
                                        @if ($getorderdata->offer_type == 'promocode')
                                        {{ trans('labels.discount') }} (-)
                                        {{ $getorderdata->couponcode != '' ? '(' . $getorderdata->couponcode . ')' : '' }}
                                        @endif
                                        @endif
                                    </strong>
                                </td>
                                <td class="text-end border-0">
                                    <strong class="fw-600">{{ helper::currency_formate($getorderdata->discount_amount, $getorderdata->vendor_id) }}</strong>
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-end text-success border-0" colspan="4">
                                    <strong class="fs-5">{{ trans('labels.grand_total') }}</strong>
                                </td>
                                <td class="text-end text-success border-0">
                                    <strong class="fs-5">{{ helper::currency_formate($getorderdata->grand_total, $getorderdata->vendor_id) }}</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="customerinfo" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content rounded-5">
            <div class="modal-header">
                <h5 class="modal-title" id="modalbankdetailsLabel">{{ trans('labels.edit') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form enctype="multipart/form-data" action="{{ URL::to('admin/orders/customerinfo') }}" method="POST">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="order_id" id="modal_order_id" class="form-control" value="">
                    <input type="hidden" name="edit_type" id="edit_type" class="form-control" value="">
                    <div id="customer_info">
                        <div class="form-group col-md-12">
                            <label for="customer_name"> {{ trans('labels.customer_name') }} </label>
                            <div class="controls">
                                <input type="text" name="customer_name" id="customer_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="customer_mobile"> {{ trans('labels.mobile') }} </label>
                            <div class="controls">
                                <input type="text" name="customer_mobile" id="customer_mobile" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="customer_email"> {{ trans('labels.email') }} </label>
                            <div class="controls">
                                <input type="text" name="customer_email" id="customer_email" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div id="delivery_info">
                        <div class="form-group col-md-12">
                            <label for="customer_address"> {{ trans('labels.address') }} </label>
                            <div class="controls">
                                <input type="text" name="customer_address" id="customer_address" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="customer_building"> {{ trans('labels.building') }} </label>
                            <div class="controls">
                                <input type="text" name="customer_building" id="customer_building" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="customer_landmark"> {{ trans('labels.landmark') }} </label>
                            <div class="controls">
                                <input type="text" name="customer_landmark" id="customer_landmark" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="customer_pincode"> {{ trans('labels.pincode') }} </label>
                            <div class="controls">
                                <input type="text" name="customer_pincode" id="customer_pincode" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">{{ trans('labels.close') }}</button>
                    <button @if (env('Environment')=='sendbox' ) type="button" onclick="myFunction()" type="submit" @endif class="btn btn-primary"> {{ trans('labels.save') }} </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="customisation" tabindex="-1" aria-labelledby="customisationLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content rounded-5">
            <div class="modal-header px-4">
                <p class="title pb-1" id="cart_item_name"></p>
                <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>




            <div class="modal-body px-4 pb-4">
                <div class="p-12px">
                    <div id="item-variations" class="mt-2">

                    </div>
                    <!-- Extras -->
                    <div id="item-extras" class="mt-3">
                        <h5 class="fw-normal m-0 d-none" id="extras_title">{{ trans('labels.extras') }} </h5>
                        <ul class="m-0 ps-2">
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    var variation_title = "{{ trans('labels.variation')}}";
    var extra_title = "{{trans('labels.extras')}}";

    function showaddons(variants_name, variants_price, extras_name, extras_price, item_name) {
        "use strict";
        $('#cart_item_name').html(item_name);

        var i = 0;
        var extras = extras_name.split("|");
        var variations = variants_name.split(',');
        var extra_price = extras_price.split('|');
        var html = "";
        if (variations != '') {
            html += '<p class="fw-bolder m-0" id="variation_title">' + variation_title + '</p><ul class="m-0 ps-2">';
            html += '<li class="px-0">' + variations + ' : <span class="text-muted">' + currency_formate(parseFloat(variants_price)) + '</span></li>'
            html += '</ul>';
        }
        $('#item-variations').html(html);
        var html1 = '';
        if (extras != '') {
            $('#extras_title').removeClass('d-none');
            html1 += '<p class="fw-bolder m-0" id="extras_title">' + extra_title + '</p><ul class="m-0 ps-2">';
            for (i in extras) {
                html1 += '<li class="px-0">' + extras[i] + ' : <span class="text-muted">' + currency_formate(parseFloat(extra_price[i])) + '</span></li>'
            }
            html1 += '</ul>';
        }
        $('#item-extras').html(html1);
        $('#customisation').modal('show');
    }

    function currency_formate(price) {

        if ("{{ @helper::appdata($vendor_id)->currency_position }}" == "left") {

            if ("{{helper::appdata($vendor_id)->decimal_separator}}" == 1) {
                if ("{{helper::appdata($vendor_id)->currency_space}}" == 1) {
                    return "{{ @helper::appdata($vendor_id)->currency }}" + " " + parseFloat(price).toFixed("{{helper::appdata($vendor_id)->currency_formate}}");
                } else {
                    return "{{ @helper::appdata($vendor_id)->currency }}" + parseFloat(price).toFixed("{{helper::appdata($vendor_id)->currency_formate}}");
                }

            } else {
                if ("{{helper::appdata($vendor_id)->currency_space}}" == 1) {
                    var newprice = "{{ @helper::appdata($vendor_id)->currency }}" + " " + (parseFloat(price).toFixed("{{helper::appdata($vendor_id)->currency_formate}}"));
                } else {
                    var newprice = "{{ @helper::appdata($vendor_id)->currency }}" + (parseFloat(price).toFixed("{{helper::appdata($vendor_id)->currency_formate}}"));
                }
                newprice = newprice.replace('.', ',');
                return newprice;
            }
        } else {
            if ("{{helper::appdata($vendor_id)->decimal_separator}}" == 1) {
                if ("{{helper::appdata($vendor_id)->currency_space}}" == 1) {
                    return parseFloat(price).toFixed("{{helper::appdata($vendor_id)->currency_formate}}") + " " + "{{ @helper::appdata($vendor_id)->currency }}";
                } else {
                    return parseFloat(price).toFixed("{{helper::appdata($vendor_id)->currency_formate}}") + "{{ @helper::appdata($vendor_id)->currency }}";
                }
            } else {
                if ("{{helper::appdata($vendor_id)->currency_space}}" == 1) {
                    var newprice = (parseFloat(price).toFixed("{{helper::appdata($vendor_id)->currency_formate}}")) + " " + "{{ @helper::appdata($vendor_id)->currency }}";
                } else {
                    var newprice = (parseFloat(price).toFixed("{{helper::appdata($vendor_id)->currency_formate}}")) + "{{ @helper::appdata($vendor_id)->currency }}";
                }
                newprice = newprice.replace('.', ',');
                return newprice;
            }
        }
    }
</script>
@endsection
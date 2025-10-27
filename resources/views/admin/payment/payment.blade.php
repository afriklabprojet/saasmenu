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
    <div class="row align-items-center mb-3">
        <div class="col-12">
            <h5 class="pages-title fs-2">{{ trans('labels.payment_settings') }}</h5>
            @include('admin.layout.breadcrumb')
        </div>

    </div>
    <div class="row g-3 mb-7 sort_menu" id="carddetails" data-url="{{ url('admin/payment/reorder_payment') }}">

        @php
            $payment_array = ['myfatoorah,mercado_pago', 'paypal', 'toyyibpay'];
        @endphp

        @foreach ($getpayment as $key => $pmdata)
            @php

                $transaction_type = $pmdata->payment_type;

                $image_tag_name = $transaction_type . '_image';

            @endphp

            <div class="col-md-12 col-lg-12 col-xl-6"
                data-id="{{ $pmdata->id }}">
                <form action="{{ URL::to('admin/payment/update') }}" method="POST" class=""
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="transaction_type" value="{{ $pmdata->id }}">
                    <input type="hidden" name="payment_type" value="{{ $transaction_type }}">
                    <div class="card h-100 handle">
                        <div class="card-header bg-transparent">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>

                                    <img src="{{ helper::image_path($pmdata->image) }}" alt=""
                                        class="img-fluid rounded me-2" height="30" width="30">
                                    <b>
                                        {{ $pmdata->payment_name }}
                                        @if ($transaction_type == '2' || $transaction_type == '3' || $transaction_type == '4' || $transaction_type == '5' || $transaction_type == '6' || $transaction_type == '7' || $transaction_type == '8' || $transaction_type == '9' || $transaction_type == '10' || $transaction_type == '11' || $transaction_type == '12' || $transaction_type == '13' || $transaction_type == '14' || $transaction_type == '15' || $transaction_type == '16')
                                            @if (env('Environment') == 'sendbox')
                                                <span class="badge badge bg-danger ms-2">{{ trans('labels.addon') }}</span>
                                            @endif
                                        @endif
                                    </b>
                                </div>
                                <div>
                                    <input id="checkbox-switch-{{ $transaction_type }}" type="checkbox"
                                        class="checkbox-switch" name="is_available" value="1"
                                        {{ $pmdata->is_available == 1 ? 'checked' : '' }}>
                                    <label for="checkbox-switch-{{ $transaction_type }}" class="switch">
                                        <span
                                            class="{{ session()->get('direction') == 2 ? 'switch__circle-rtl' : 'switch__circle' }}"><span
                                                class="switch__circle-inner"></span></span>
                                        <span
                                            class="switch__left {{ session()->get('direction') == 2 ? 'pe-2' : 'ps-2' }}">{{ trans('labels.off') }}</span>
                                        <span
                                            class="switch__right {{ session()->get('direction') == 2 ? 'ps-2' : 'pe-2' }}">{{ trans('labels.on') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="form-label">
                                        {{ trans('labels.payment_name') }} <span class="text-danger">
                                            *</span> </label>
                                    <input type="text" class="form-control" name="name"
                                        placeholder="{{ trans('labels.payment_name') }}"
                                        value="{{ $pmdata->payment_name }}" required>
                                </div>
                                @if (!in_array($transaction_type, ['1']))
                                    @if ($transaction_type == '6')




                                        {{-- <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="bank_name" class="form-label"> {{ trans('labels.bank_name') }}
                                                    <span class="text-danger"> *</span>
                                                </label>
                                                <input type="text" required="" id="bank_name" class="form-control"
                                                    name="bank_name" placeholder="{{ trans('labels.bank_name') }}"
                                                    value="{{ $pmdata->bank_name }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="account_holder_name" class="form-label">
                                                    {{ trans('labels.account_holder_name') }} <span class="text-danger">
                                                        *</span>
                                                </label>
                                                <input type="text" required="" id="account_holder_name"
                                                    class="form-control" name="account_holder_name"
                                                    placeholder="{{ trans('labels.account_holder_name') }}"
                                                    value="{{ $pmdata->account_holder_name }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="account_number" class="form-label">
                                                    {{ trans('labels.account_number') }} <span class="text-danger">
                                                        *</span>
                                                </label>
                                                <input type="text" required="" id="account_number"
                                                    class="form-control" name="account_number"
                                                    placeholder="{{ trans('labels.account_number') }}"
                                                    value="{{ $pmdata->account_number }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="bank_ifsc_code" class="form-label">
                                                    {{ trans('labels.bank_ifsc_code') }} <span class="text-danger">
                                                        *</span>
                                                </label>
                                                <input type="text" required="" id="bank_ifsc_code"
                                                    class="form-control" name="bank_ifsc_code"
                                                    placeholder="{{ trans('labels.bank_ifsc_code') }}"
                                                    value="{{ $pmdata->bank_ifsc_code }}">
                                            </div>
                                        </div> --}}
                                    @else
                                        <div class="col-md-6">
                                            <label for="razorpaycurrency" class="form-label">
                                                {{ trans('labels.environment') }}
                                                <span class="text-danger"> *</span>
                                            </label>
                                            <div class="d-flex align-items-center">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="environment"
                                                        id="sandbox-{{ $transaction_type }}" value="1"
                                                        {{ $pmdata->environment == 1 ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="sandbox-{{ $transaction_type }}">
                                                        {{ trans('labels.sandbox') }}
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline mx-3">
                                                    <input class="form-check-input" type="radio" name="environment"
                                                        id="production-{{ $transaction_type }}" value="2"
                                                        {{ $pmdata->environment == 2 ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="production-{{ $transaction_type }}">
                                                        {{ trans('labels.production') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="currency" class="form-label"> {{ trans('labels.currency') }}
                                                    <span class="text-danger"> *</span>
                                                </label>
                                                <input type="text" required="" id="currency" class="form-control"
                                                    name="currency" placeholder="Currency"
                                                    value="{{ $pmdata->currency }}">
                                            </div>
                                        </div>


                                        @if ($transaction_type == '4')
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="form-label">
                                                        @if ($transaction_type == 'flutterwave')
                                                            {{ trans('labels.encryption_key') }}
                                                        @else
                                                            {{ trans('labels.merchant_user_id') }}
                                                        @endif
                                                        <span class="text-danger"> *</span>
                                                    </label>
                                                    <input type="text" id="encryption_key" class="form-control"
                                                        name="encryption_key" placeholder="Encryption Key"
                                                        value="{{ $pmdata->encryption_key }}"
                                                        {{ $transaction_type == 'flutterwave' ? 'required' : '' }}>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($transaction_type == '12')
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="base_url_by_region" class="form-label">
                                                        {{ trans('labels.base_url_by_region') }}
                                                        <span class="text-danger"> *</span>
                                                    </label>
                                                    <input type="text" id="base_url_by_region" class="form-control"
                                                        name="base_url_by_region"
                                                        placeholder="{{ trans('labels.base_url_by_region') }}"
                                                        value="{{ $pmdata->base_url_by_region }}"
                                                        {{ $transaction_type == 'paytab' ? 'required' : '' }}>
                                                </div>
                                            </div>
                                        @endif



                                        <div
                                            class=" {{ $transaction_type == '7' || $transaction_type == '9' || $transaction_type == '13' || $transaction_type == '14' || $transaction_type == '16' ? 'col-md-12' : 'col-md-6' }}">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    @if ($transaction_type == 'phonepe')
                                                        {{ trans('labels.salt_key') }}
                                                    @elseif ($transaction_type == '16')
                                                        {{ trans('labels.cinetpay_site_id') }}
                                                    @else
                                                        {{ trans('labels.secret_key') }}
                                                    @endif
                                                    <span class="text-danger"> *</span>
                                                </label>
                                                <input type="text" required="" id="secretkey" class="form-control"
                                                    name="secret_key" placeholder="Secret Key"
                                                    value="{{ $pmdata->secret_key }}">
                                            </div>
                                        </div>
                                        <div
                                            class="col-md-6 {{ $transaction_type == '7' || $transaction_type == '9' || $transaction_type == '13' || $transaction_type == '14' ? 'd-none' : '' }}">
                                            <div class="form-group">
                                                <label for="publickey" class="form-label">
                                                    @if ($transaction_type == 'paytab')
                                                        {{ trans('labels.profile_key') }}
                                                    @elseif($transaction_type == 'phonepe')
                                                        {{ trans('labels.merchant_id') }}
                                                    @elseif($transaction_type == '16')
                                                        {{ trans('labels.cinetpay_api_key') }}
                                                    @else
                                                        {{ trans('labels.public_key') }}
                                                    @endif
                                                    <span class="text-danger"> *</span>
                                                </label>
                                                <input type="text" id="publickey" class="form-control"
                                                    name="public_key" placeholder="Public Key"
                                                    value="{{ $pmdata->public_key }}"
                                                    {{ $transaction_type != '7' || $transaction_type != '9' || $transaction_type != '13' || $transaction_type != '14' ? '' : 'required' }}>
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="image" class="form-label">
                                            {{ trans('labels.image') }}
                                        </label>
                                        <input type="file" class="form-control" name="image">
                                        <img src="{{ helper::image_path($pmdata->image) }}" alt=""
                                            class="img-fluid rounded hw-50">
                                    </div>
                                </div>
                                @if ($transaction_type == '6')


                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">
                                                {{ trans('labels.payment_description') }} <span
                                                    class="text-danger">
                                                    *</span></label>
                                            <textarea class="form-control" id="ckeditor" name="payment_description">{{ $pmdata->payment_description }}</textarea>

                                        </div>
                                    </div>
                                    @endif
                                <div class="col-md-12 d-flex justify-content-end align-items-end">
                                    <div class="form-group text-end">
                                        <button class="btn btn-success btn-succes m-1 {{ Auth::user()->type == 4 ? (helper::check_access('role_payment_methods', Auth::user()->role_id, $vendor_id, 'add') == 1 ? '' : 'd-none') : '' }}"
                                            @if (env('Environment') == 'sendbox') type="button" onclick="myFunction()" @else type="submit" @endif>{{ trans('labels.save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        @endforeach

    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.sort_menu').sortable({
                handle: '.handle',
                cursor: 'move',
                placeholder: 'highlight',
                axis: "x,y",

                update: function(e, ui) {
                    var sortData = $('.sort_menu').sortable('toArray', {
                        attribute: 'data-id'
                    })
                    updateToDatabase(sortData.join('|'))
                }
            })

            function updateToDatabase(idString) {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "POST",
                    dataType: "json",
                    url: $('#carddetails').attr('data-url'),
                    data: {
                        ids: idString,
                    },
                    success: function(response) {
                        if (response.status == 1) {
                            toastr.success(response.msg);
                        } else {
                            toastr.success(wrong);
                        }
                    }
                });
            }

        })
    </script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.12.1/ckeditor.js"></script>
     <script>
         CKEDITOR.replace('ckeditor');
     </script>
    <script src="{{ url(env('ASSETSPATHURL') . 'admin-assets/js/payment.js') }}"></script>
@endsection

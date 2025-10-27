<html>
<head>
    <title>{{ helper::appdata($getorderdata->vendor_id)->web_title }}</title>
</head>
<style type="text/css">
    body {
        font-family: 'Roboto Condensed', sans-serif;
    }
    .m-0 {
        margin: 0px;
    }
    .p-0 {
        padding: 0px;
    }
    .pt-5 {
        padding-top: 5px;
    }
    .mt-10 {
        margin-top: 10px;
    }
    .text-center {
        text-align: center !important;
    }
    .w-100 {
        width: 100%;
    }
    .w-50 {
        width: 50%;
    }
    .w-85 {
        width: 85%;
    }
    .w-15 {
        width: 15%;
    }
    .logo img {
        width: 200px;
        height: 60px;
    }
    .gray-color {
        color: #5D5D5D;
    }
    .text-bold {
        font-weight: bold;
    }
    .border {
        border: 1px solid black;
    }
    table tr,
    th,
    td {
        border: 1px solid #d2d2d2;
        border-collapse: collapse;
        padding: 7px 8px;
    }
    table tr th {
        background: #F4F4F4;
        font-size: 15px;
    }
    table tr td {
        font-size: 13px;
    }
    table {
        border-collapse: collapse;
    }
    .box-text p {
        line-height: 10px;
    }
    .float-left {
        float: left;
    }
    .total-part {
        font-size: 16px;
        line-height: 12px;
    }
    .total-right p {
        padding-right: 20px;
    }
</style>
<body>
    <div class="head-title">
        <h1 class="text-center m-0 p-0">Invoice</h1>
    </div>
    <div class="add-detail mt-10">
        <div class="w-50 float-left mt-10">
            <p class="m-0 pt-5 text-bold w-100">Invoice ID - <span
                    class="gray-color">#{{ $getorderdata->id }}</span></p>
            <p class="m-0 pt-5 text-bold w-100">Order ID - <span
                    class="gray-color">#{{ $getorderdata->order_number }}</span></p>
            <p class="m-0 pt-5 text-bold w-100">Order date - <span
                    class="gray-color">{{ helper::date_format($getorderdata->created_at, $getorderdata->vendor_id) }}</span></p>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div class="table-section bill-tbl w-100 mt-10">
        <table class="table w-100 mt-10">
            <tr>
                <th colspan="2" class="w-50">Customer info.</th>
            </tr>
            <tr>
                <td>
                    <div class="box-text">
                        <p><i class="fa-regular fa-user"></i> {{ $getorderdata->customer_name }}</p>
                        <p><i class="fa-regular fa-phone"></i> {{ $getorderdata->mobile }} </p>
                        <p><i class="fa-regular fa-envelope"></i> {{ $getorderdata->customer_email }}</p>
                    </div>
                </td>
                @if ($getorderdata->order_type == 1)
                    <td>
                        <div class="box-text">
                            <p> {{ $getorderdata->address }},</p>
                            <p>{{ $getorderdata->building }},</p>
                            <p>{{ $getorderdata->landmark }}</p>
                            <p> {{ $getorderdata->pincode }}.</p>
                        </div>
                    </td>
                @endif
            </tr>
        </table>
    </div>
    <div class="table-section bill-tbl w-100 mt-10">
        <table class="table w-100 mt-10">
            <tr>
                <th class="w-50">Payment option</th>
                <th class="w-50">Shipping method</th>
            </tr>
            <tr>
                <td>
                    @if ($getorderdata->payment_type == 6)
                        {{ @helper::getpayment($getorderdata->payment_type, $getorderdata->vendor_id)->payment_name }}
                        : <small><a href="{{ helper::image_path($transaction->screenshot) }}" target="_blank"
                                class="text-danger">Click here</a></small>
                    @else
                        {{ @helper::getpayment($getorderdata->payment_type, $getorderdata->vendor_id)->payment_name }}
                    @endif
                </td>
                <td>
                    @if ($getorderdata->order_type == 1)
                        Delivery
                    @elseif ($getorderdata->order_type == 2)
                        Pickup
                    @elseif ($getorderdata->order_type == 3)
                        Table
                    @elseif ($getorderdata->order_type == 4)
                        POS
                    @endif
                </td>
            </tr>
        </table>
    </div>
    <div class="table-section bill-tbl w-100 mt-10">
        <table class="table w-100 mt-10">
            <tr>
                <th class="w-50">Product name</th>
                <th class="w-50">Unit cost</th>
                <th class="w-50">QTY</th>
                <th class="w-50">Subtotal</th>
            </tr>
            @foreach ($ordersdetails as $orders)
                @php
                    $itemprice = $orders->price;
                    if ($orders->variants_id != '') {
                        $itemprice = $orders->variants_price;
                    }
                @endphp
                <tr align="center">
                    <td>{{ $orders->item_name }}
                        @if ($orders->variants_id != '')
                            - <small>{{ $orders->variants_name }} ({{helper::currency_formate($orders->variants_price, $getorderdata->vendor_id) }})</small>
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
                                <small>
                                    <b class="text-muted">{{ $extras_name[$key] }}</b> :
                                    {{ helper::currency_formate($extras_price[$key], $getorderdata->vendor_id) }}<br>
                                </small>
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
                    @php
                        $price = (float) $extras_total_price + (float) $orders->variants_price;
                        $total = (float) $price * (float) $orders->qty;
                    @endphp
                    <td> {{ helper::currency_formate($price, $getorderdata->vendor_id) }}
                    </td>
                    <td>{{ $orders->qty }}</td>
                    <td> {{ helper::currency_formate($total, $getorderdata->vendor_id) }}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="7">
                    <div class="total-part">
                        @php
                            $tax = explode('|', $getorderdata->tax);
                            $tax_name = explode('|', $getorderdata->tax_name);
                        @endphp
                        <div class="total-left w-85 float-left" align="right">
                            <p>Subtotal</p>
                           
                            @if ($getorderdata->tax != null && $getorderdata->tax != '')
                                @foreach ($tax as $key => $tax_value)
                                    <p>{{ $tax_name[$key] }}</p>
                                @endforeach
                            @endif
                          
                            @if ($getorderdata->order_type == 1)
                                <p> <strong>{{ trans('labels.delivery_charge') }} (+)
                                        ({{ $getorderdata->delivery_area }}) </strong></p>
                            @endif
                             @if ($getorderdata->discount_amount > 0)
                            <p><strong> @if ($getorderdata->offer_type == 'loyalty')
                                        {{ trans('labels.loyalty_discount') }} (-)
                                        @else
                                         {{ trans('labels.discount') }} {{ $getorderdata->couponcode != '' ? '(' . $getorderdata->couponcode . ')' : '' }} (-)
                                        @endif
                                       </strong>
                            </p>
                        @endif
                            <p>{{trans('labels.grand_total')}}</p>
                        </div>
                        <div class="total-right w-15 float-left text-bold" align="right">
                            <p>{{ helper::currency_formate($getorderdata->sub_total, $getorderdata->vendor_id) }}</p>
                           
                            @if ($getorderdata->tax != null && $getorderdata->tax != '')
                                @foreach ($tax as $key => $tax_value)
                                    <p><strong>{{ helper::currency_formate((float) $tax[$key], $getorderdata->vendor_id) }}</strong>
                                @endforeach
                            @endif
                            </p>
                            @if ($getorderdata->order_type == 1)
                                <p> <strong>{{ helper::currency_formate($getorderdata->delivery_charge, $getorderdata->vendor_id) }}</strong>
                                </p>
                            @endif
                             @if ($getorderdata->discount_amount > 0)
                                <p> <strong> {{ helper::currency_formate($getorderdata->discount_amount, $getorderdata->vendor_id) }}</strong>
                                </p>
                            @endif
                            <p> <strong>{{ helper::currency_formate($getorderdata->grand_total, $getorderdata->vendor_id) }}</strong>
                            </p>
                        </div>
                        <div style="clear: both;"></div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</html>

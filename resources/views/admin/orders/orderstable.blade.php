<table class="table table-striped table-bordered py-3 zero-configuration w-100">
    <thead>
        <tr class="fw-500 py-3">
          
            <td>{{ trans('labels.srno') }}</td>
            @if(request()->is('admin/customers*') && (Auth::user()->type == 1))
            <td>{{ trans('labels.vendor_title') }}</td>
            @endif
            <td>{{ trans('labels.order_number') }}</td>
            <td>{{ trans('labels.date_time') }}</td>
            <td>{{ trans('labels.grand_total') }}</td>
            <td>{{ trans('labels.payment_type') }}</td>
            <td>{{ trans('labels.order_type') }}</td>
            <td>{{ trans('labels.status') }}</td>
            <td>{{ trans('labels.created_date') }}</td>
            <td>{{ trans('labels.updated_date') }}</td>
            @if (Auth::user()->type == 2)
            <td>{{ trans('labels.action') }}</td>

            @endif
          
        </tr>
    </thead>
    <tbody>
        @php $i = 1; @endphp
        @foreach ($getorders as $orderdata)
        <tr id="dataid{{ $orderdata->id }}" class="fs-7">
            <td>@php echo $i++; @endphp</td>
            @if(request()->is('admin/customers*') && (Auth::user()->type == 1))
            <td>{{ $orderdata['vendorinfo']->name }}</td>
            @endif
            <td> 
            <div class="d-flex justify-content-between">
                        <a class="text-dark fw-700" href="{{ URL::to('admin/orders/invoice/' . $orderdata->order_number) }}"> {{$orderdata->order_number}} </a>
                        @if($orderdata->vendor_note != "")
                        <a href="javascript:void(0)"
                            class="btn btn-outline-primary btn-sm" tooltip="{{ $orderdata->vendor_note }}">
                            <i class="fa-solid fa-clipboard"></i>
                        </a>
                        @endif
                    </div>  
                
            </td>
            <td>
                @if($orderdata->order_type == 3)
                    {{  helper::date_format($orderdata->created_at,$vendor_id) }}
                @else
                    {{ helper::date_format($orderdata->delivery_date,$vendor_id) }} <br>
                    {{ $orderdata->delivery_time }}
                @endif

            </td>
            <td>{{ helper::currency_formate($orderdata->grand_total, $vendor_id) }}</td>
            <td>
                @if ($orderdata->payment_type == 0)
                    {{ trans('labels.online') }} </br>
                @else
                    {{ @helper::getpayment($orderdata->payment_type, $orderdata->vendor_id)->payment_name }} 
                    @if (in_array($orderdata->payment_type, [2, 3, 4, 5, 7, 8, 9, 10, 11, 12, 13, 14, 15]))
                    : {{ $orderdata->payment_id }}
                    @endif</br>
                @endif
                @if ($orderdata->payment_status == 1)
                    <small class="text-danger"><i class="far fa-clock"></i>
                        {{ trans('labels.unpaid') }}</small>
                @else
                    <small class="text-success"><i class="far fa-clock"></i>
                        {{ trans('labels.paid') }}</small>
                @endif
            </td>
          
            <td>
                    @if ($orderdata->order_type == 1)
                        {{ trans('labels.delivery') }}
                    @elseif ($orderdata->order_type == 2)
                        {{ trans('labels.pickup') }}
                    @elseif ($orderdata->order_type == 3)
                        {{ trans('labels.table') }}
                        ({{ $orderdata->dinein_tablename != '' ? $orderdata->dinein_tablename : '-' }})
                    @elseif ($orderdata->order_type == 4)
                        {{ trans('labels.pos') }}
                  
                    @endif
                </td>
          
            <td>
                        @if ($orderdata->status_type == '1')
                            <span
                                class="badge bg-warning">{{ @helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type,$vendor_id)->name == null ? '-' : @helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type,$vendor_id)->name }}</span>
                        @elseif($orderdata->status_type == '2')
                            <span
                                class="badge bg-info">{{ @helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type,$vendor_id)->name == null ? '-' : @helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type,$vendor_id)->name }}</span>
                        @elseif($orderdata->status_type == '3')
                            <span
                                class="badge bg-success">{{ @helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type,$vendor_id)->name == null ? '-' : @helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type,$vendor_id)->name }}</span>
                        @elseif($orderdata->status_type == '4')
                            <span
                                class="badge bg-danger">{{ @helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type,$vendor_id)->name == null ? '-' : @helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type,$vendor_id)->name }}</span>
                        @else
                            --
                        @endif
                    </td>
         
         
            <td>{{ helper::date_format($orderdata->created_at,$vendor_id) }}<br>
                {{ helper::time_format(@$orderdata->created_at,$vendor_id) }}
                </td>
                <td>
                   
                    {{ helper::date_format(@$orderdata->updated_at,$vendor_id) }}<br>
                    {{ helper::time_format(@$orderdata->updated_at,$vendor_id) }}
                </td>
            @if (Auth::user()->type == 2)
            <td>
                @if(Auth::user()->type == 2)
                <a href="{{ URL::to('admin/orders/print/' . $orderdata->order_number) }}" class="btn btn-sm btn-success btn-success btn-size" tooltip="{{ trans('labels.print') }}">
 
                    <i class="fa-solid fa-print"></i>
                </a>
                @endif
                <a class="btn btn-sm btn-dark btn-size" tooltip="{{ trans('labels.view') }}" href="{{ URL::to('admin/orders/invoice/' . $orderdata->order_number) }}">    <i class="fa-regular fa-eye"></i> 
                </a>
                @if (Auth::user()->type == 2 )
                        @if (($orderdata->payment_type == 1 || $orderdata->payment_type == 6) && $orderdata->payment_status == 1 && $orderdata->status_type == 3 &&  $orderdata->status_type !=4)
                            <a class="btn btn-sm btn-secondary btn-size"
                                onclick="codpayment('{{ $orderdata->order_number }}','{{ $orderdata->grand_total }}')"
                                tooltip="{{ trans('labels.payment') }}"><i class="fa-solid fa-file-invoice-dollar"></i>
                            </a>
                        @endif
                    @endif
            </td>
            @endif
          
        </tr>
        @endforeach
    </tbody>
</table>
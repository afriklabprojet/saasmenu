

<table class="table table-striped table-bordered py-3 zero-configuration w-100">
    <thead>
        <tr class="fw-500">
            <td>{{ trans('labels.srno') }}</td>
            @if (Auth::user()->type == '1')
                <td>{{ trans('labels.name') }}</td>
            @endif
            <td>{{ trans('labels.plan_name') }}</td>
            <td>{{ trans('labels.amount') }}</td>
            <td>{{ trans('labels.payment_type') }}</td>
            <td>{{ trans('labels.purchase_date') }}</td>
            <td>{{ trans('labels.expire_date') }}</td>
            <td>{{ trans('labels.status') }}</td>
            <td>{{ trans('labels.created_date') }}</td>
            <td>{{ trans('labels.updated_date') }}</td>
            @if (Auth::user()->type == '1')
                <td>{{ trans('labels.action') }}</td>
            @endif

        </tr>
    </thead>
    <tbody>
        @php
            $i = 1;
        @endphp
        @foreach ($transaction as $transaction)
            <tr class="fs-7">
                <td>@php echo $i++; @endphp</td>
                @if (Auth::user()->type == '1')
                    <td>{{ @$transaction['vendor_info']->name }}</td>
                @endif
                <td>{{ @$transaction['plan_info']->name }}</td>
                <td>{{ helper::currency_formate($transaction->amount, '') }}</td>
                <td>
                    @if ($transaction->payment_type == '6')
                        {{ trans('labels.' . $transaction->payment_type) }} : <small><a
                                href="{{ helper::image_path($transaction->screenshot) }}" target="_blank"
                                class="text-danger">{{ trans('labels.click_here') }}</a></small>
                    @elseif($transaction->amount == 0)
                        -
                    @else
                        {{ @helper::getpayment($transaction->payment_type, 1)->payment_name }} :
                        {{ $transaction->payment_id }}
                    @endif

                </td>
                <td>
                    @if ($transaction->payment_type == '6')
                        @if ($transaction->status == 2)
                            <span
                                class="badge bg-success">{{ helper::date_format($transaction->purchase_date, $transaction->vendor_id) }}</span>
                        @else
                            -
                        @endif
                    @else
                        <span
                            class="badge bg-success">{{ helper::date_format($transaction->purchase_date, $transaction->vendor_id) }}</span>
                    @endif
                </td>
                <td>
                    @if ($transaction->payment_type == '6')
                        @if ($transaction->status == 2)
                            <span
                                class="badge bg-danger">{{ $transaction->expire_date != '' ? helper::date_format($transaction->expire_date, $transaction->vendor_id) : '-' }}</span>
                        @else
                            -
                        @endif
                    @else
                        <span
                            class="badge bg-danger">{{ $transaction->expire_date != '' ? helper::date_format($transaction->expire_date, $transaction->vendor_id) : '-' }}</span>
                    @endif
                </td>
                <td>
                    @if ($transaction->payment_type == '6')
                        @if ($transaction->status == 1)
                            <span class="badge bg-warning">{{ trans('labels.pending') }}</span>
                        @elseif ($transaction->status == 2)
                            <span class="badge bg-success">{{ trans('labels.accepted') }}</span>
                        @elseif ($transaction->status == 3)
                            <span class="badge bg-danger">{{ trans('labels.rejected') }}</span>
                        @else
                            -
                        @endif
                    @else
                        -
                    @endif
                </td>
                <td>{{ helper::date_format($transaction->created_at,$vendor_id) }}<br>
                    {{ helper::time_format($transaction->created_at,$vendor_id) }}
                </td>
                <td>{{ helper::date_format(@$transaction->updated_at,$vendor_id) }}<br>
                    {{ helper::time_format(@$transaction->updated_at,$vendor_id) }}
                </td>
                @if (Auth::user()->type == '1')
                    <td>
                        @if ($transaction->payment_type == '6')
                            @if ($transaction->status == 1)
                                <a class="btn btn-sm btn-outline-success"
                                    onclick="statusupdate('{{ URL::to('admin/transaction-' . $transaction->id . '-2') }}')"><i
                                        class="fas fa-check"></i></a>
                                <a class="btn btn-sm btn-outline-danger"
                                    onclick="statusupdate('{{ URL::to('admin/transaction-' . $transaction->id . '-3') }}')"><i
                                        class="fas fa-close"></i></a>
                            @else
                                -
                            @endif
                        @else
                            -
                        @endif
                    </td>
                @endif

            </tr>
        @endforeach
    </tbody>
</table>

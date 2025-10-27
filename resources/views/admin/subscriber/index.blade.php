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
<div class="row justify-content-between align-items-center">
    <div class="col-12">
        <h5 class="pages-title fs-2">{{ trans('labels.subscribers') }}</h5>
        @include('admin.layout.breadcrumb')
    </div>
</div>
<div class="row mb-7">
    <div class="col-12">
        <div class="card border-0 my-3">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered py-3 zero-configuration w-100">
                        <thead>
                            <tr class="fw-500">
                                <td>{{trans('labels.srno')}}</td>
                                <td>{{trans('labels.email')}}</td>
                                <td>{{ trans('labels.created_date') }}</td>
                                <td>{{ trans('labels.updated_date') }}</td>
                                <td>{{trans('labels.action')}}</td>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i=1; @endphp
                            @foreach ($getsubscribers as $subscriber)
                            <tr class="fs-7">
                                <td>@php echo $i++ @endphp</td>
                                <td>{{$subscriber->email}}</td>
                                <td>{{ helper::date_format($subscriber->created_at,$vendor_id) }}<br>
                                    {{ helper::time_format($subscriber->created_at,$vendor_id) }}

                                </td>
                                <td>{{ helper::date_format($subscriber->updated_at,$vendor_id) }}<br>
                                    {{ helper::time_format($subscriber->updated_at,$vendor_id) }}

                                </td>
                                <td>
                                    <a @if (env('Environment')=='sendbox' ) onclick="myFunction()" @else onclick="deletedata('{{URL::to('admin/subscribers/delete-'.$subscriber->id)}}')" @endif class="btn btn-danger btn-sm  btn-size {{ Auth::user()->type == 4 ? (helper::check_access('role_subscribers', Auth::user()->role_id, $vendor_id, 'delete') == 1 ? '' : 'd-none') : '' }}"> <i class="fa-regular fa-trash"></i></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
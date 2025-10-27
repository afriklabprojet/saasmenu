@php

    if (Auth::user()->type == 4) {
        $vendor_id = Auth::user()->vendor_id;
    } else {
        $vendor_id = Auth::user()->id;
    }

@endphp

@extends('admin.layout.default')

@section('content')
<div class="row justify-content-between align-items-center mb-3">
    <div class="col-12 col-md-4">
        <h5 class="pages-title fs-2">{{ trans('labels.global_extras') }}</h5>
        <div class="d-flex">
            @include('admin.layout.breadcrumb')
        </div>
    </div>
    <div class="col-12 col-md-8">
        <div class="d-flex justify-content-end">
            <a href="{{ URL::to('admin/extras/add') }}" class="btn-add {{ Auth::user()->type == 4 ? (helper::check_access('role_global_extras', Auth::user()->role_id, $vendor_id, 'add') == 1 ? '' : 'd-none') : '' }}"><i class="fa-regular fa-plus mx-1"></i>{{ trans('labels.add') }}</a>
        </div>
    </div>
</div>


    <div class="row">

        <div class="col-12">

            <div class="card border-0 my-3">

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-striped table-bordered py-3 zero-configuration w-100">

                            <thead>

                                <tr class="text-uppercase fw-500">

                                    <td></td>
                                    <td>{{ trans('labels.srno') }}</td>

                                    <td>{{ trans('labels.name') }}</td>

                                    <td>{{ trans('labels.price') }}</td>

                                    <td>{{ trans('labels.status') }}</td>

                                    <td>{{ trans('labels.created_date') }}</td>

                                    <td>{{ trans('labels.updated_date') }}</td>

                                    <td>{{ trans('labels.action') }}</td>

                                </tr>

                            </thead>

                            <tbody id="tabledetails" data-url="{{ url('admin/extras/reorder_extras') }}">
                                @php $i=1; @endphp
                                @foreach ($allextras as $extras)
                                    <tr class="fs-7 row1" id="dataid{{ $extras->id }}" data-id="{{ $extras->id }}">
                                        <td><a tooltip="{{ trans('labels.move') }}"><i
                                                    class="fa-light fa-up-down-left-right mx-2"></i></a></td>
                                      
                                        <td>@php echo $i++ @endphp</td>
                                        <td>{{ $extras->name }}</td>
                                        <td>{{ helper::currency_formate($extras->price, $vendor_id) }}</td>
                                        <td>
                                            @if($extras->is_available == '1')
                                                <a tooltip="{{ trans('labels.active') }}"
                                                    @if (env('Environment') == 'sendbox') onclick="myFunction()" @else onclick="statusupdate('{{ URL::to('admin/extras/change_status-' . $extras->id . '/2') }}')" @endif
                                                    class="btn btn-sm btn-success btn-size  {{ Auth::user()->type == 4 ? (helper::check_access('role_global_extras', Auth::user()->role_id, $vendor_id, 'edit') == 1 ? '' : 'd-none') : '' }}"><i
                                                        class="fas fa-check"></i></a>
                                            @else
                                                <a tooltip="{{ trans('labels.inactive') }}"
                                                    @if (env('Environment') == 'sendbox') onclick="myFunction()" @else onclick="statusupdate('{{ URL::to('admin/extras/change_status-' . $extras->id . '/1') }}')" @endif
                                                    class="btn btn-sm btn-danger  btn-size {{ Auth::user()->type == 4 ? (helper::check_access('role_global_extras', Auth::user()->role_id, $vendor_id, 'edit') == 1 ? '' : 'd-none') : '' }}"><i
                                                        class="fas fa-close"></i></a>
                                            @endif
                                        </td>
                                        <td>{{ helper::date_format($extras->created_at,$vendor_id) }}<br>
                                            {{ helper::time_format($extras->created_at, $vendor_id) }}
                                        </td>
                                        <td>{{ helper::date_format($extras->updated_at,$vendor_id) }}<br>
                                            {{ helper::time_format($extras->updated_at, $vendor_id) }}
                                        </td>
                                        <td>
                                            <a href="{{ URL::to('admin/extras/edit-' . $extras->id) }}"
                                                tooltip="{{ trans('labels.edit') }}"
                                                class="btn btn-info bt btn-size n-sm {{ Auth::user()->type == 4 ? (helper::check_access('role_global_extras', Auth::user()->role_id, $vendor_id, 'edit') == 1 ? '' : 'd-none') : '' }}">
                                                <i class="fa-regular fa-pen-to-square"></i></a>
                                            <a tooltip="{{ trans('labels.delete') }}"
                                                @if (env('Environment') == 'sendbox') onclick="myFunction()" @else onclick="statusupdate('{{ URL::to('admin/extras/delete-' . $extras->id) }}')" @endif
                                                class="btn btn-danger  btn-size btn-sm {{ Auth::user()->type == 4 ? (helper::check_access('role_global_extras', Auth::user()->role_id, $vendor_id, 'delete') == 1 ? '' : 'd-none') : '' }}">
                                                <i class="fa-regular fa-trash"></i></a>
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

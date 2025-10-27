@extends('admin.layout.default')
@section('content')
@php
if(Auth::user()->type == 4)
{
    $vendor_id = Auth::user()->vendor_id;
}else{
    $vendor_id = Auth::user()->id;
}
@endphp
<div class="row justify-content-between align-items-center mb-3">
    <div class="col-12 col-md-4">
        <h5 class="pages-title fs-2">{{ trans('labels.area') }}</h5>
        <div class="d-flex">
            @include('admin.layout.breadcrumb')
        </div>
    </div>
    <div class="col-12 col-md-8">
        <div class="d-flex justify-content-end">
            <a href="{{ URL::to('admin/area/add') }}" class="btn-add {{ Auth::user()->type == 4 ? (helper::check_access('role_area', Auth::user()->role_id, $vendor_id, 'add') == 1  ? '' : 'd-none') : '' }}"><i class="fa-regular fa-plus mx-1"></i>{{ trans('labels.add') }}</a>
        </div>
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
                                <td>{{ trans('labels.srno') }}</td>
                                <td>{{ trans('labels.area') }}</td>
                                <td>{{ trans('labels.created_date') }}</td>
                                <td>{{ trans('labels.updated_date') }}</td>
                                <td>{{ trans('labels.action') }}</td>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i=1; @endphp
                            @foreach ($areas as $area)
                            <tr class="fs-7">
                                <td>@php echo $i++ @endphp</td>
                                <td>{{ $area->name }}</td>
                                <td>{{ helper::date_format($area->created_at,$vendor_id) }}<br>
                                    {{ helper::time_format($area->created_at,$vendor_id) }}

                                </td>
                                <td>{{ helper::date_format($area->updated_at,$vendor_id) }}<br>
                                    {{ helper::time_format($area->updated_at,$vendor_id) }}

                                </td>
                                <td>
                                    <a href="{{ URL::to('admin/area/edit-' . $area->id) }}" class="btn btn-sm btn-info btn-size {{ Auth::user()->type == 4 ? (helper::check_access('role_area', Auth::user()->role_id, $vendor_id, 'add') == 1  ? '' : 'd-none') : '' }}" tooltip="{{trans('labels.edit')}}"> <i class="fa-regular fa-pen-to-square"></i></a>
                                    <a @if (env('Environment')=='sendbox' ) onclick="myFunction()" @else onclick="statusupdate('{{ URL::to('admin/area/delete-' . $area->id) }}')" @endif class="btn btn-sm btn-danger btn-size {{ Auth::user()->type == 4 ? (helper::check_access('role_area', Auth::user()->role_id, $vendor_id, 'deletes') == 1  ? '' : 'd-none') : '' }}" tooltip="{{trans('labels.delete')}}"> <i class="fa-regular fa-trash"></i></a>
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
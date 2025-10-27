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
    <div class="col-12 col-md-4">
        <h5 class="pages-title fs-2">{{ trans('labels.areas') }}</h5>
        @include('admin.layout.breadcrumb')
    </div>
    <div class="col-12 col-md-8">
        <div class="d-flex justify-content-end">
            <a href="{{ URL::to('admin/areas/add') }}" class="btn-add {{ Auth::user()->type == 4 ? (helper::check_access('role_areas', Auth::user()->role_id, $vendor_id, 'add') == 1 ? '' : 'd-none') : '' }}">
                <i class="fa-regular fa-plus mx-1"></i>{{ trans('labels.add') }}
            </a>
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
                                <td></td>
                                <td>{{trans('labels.srno')}}</td>
                                <td>{{trans('labels.city')}}</td>
                                <td>{{trans('labels.area')}}</td>
                                <td>{{ trans('labels.status') }}</td>
                                <td>{{ trans('labels.created_date') }}</td>
                                <td>{{ trans('labels.updated_date') }}</td>
                                <td>{{trans('labels.action')}}</td>
                            </tr>
                        </thead>
                        <tbody id="tabledetails" data-url="{{url('admin/areas/reorder_area')}}">
                            @php
                            $i=1;
                            @endphp
                            @foreach ($allareas as $area)
                            <tr class="fs-7 row1" id="dataid{{$area->id}}" data-id="{{$area->id}}">
                            <td><a tooltip="{{trans('labels.move')}}"><i class="fa-light fa-up-down-left-right mx-2"></i></a></td>
                                <td>@php
                                    echo $i++
                                    @endphp</td>
                                <td>{{$area['city_info']->name}}</td>
                                <td>{{$area->area}}</td>
                                <td>
                                    @if ($area->is_available == '1')
                                    <a @if (env('Environment')=='sendbox' ) onclick="myFunction()" @else onclick="statusupdate('{{ URL::to('admin/areas/change_status-' . $area->id . '/2') }}')" @endif class="btn btn-sm btn-success btn-size" tooltip="{{trans('labels.active')}}"><i class="fas fa-check"></i></a>
                                    @else
                                    <a @if (env('Environment')=='sendbox' ) onclick="myFunction()" @else onclick="statusupdate('{{ URL::to('admin/areas/change_status-' . $area->id . '/1') }}')" @endif class="btn btn-sm btn-danger btn-xmark" tooltip="{{trans('labels.in_active')}}"><i class="fas fa-close"></i></a>
                                    @endif
                                </td>
                                <td>{{ helper::date_format($area->created_at, $vendor_id) }}<br>
                                    {{ helper::time_format($area->created_at, $vendor_id) }}

                                </td>
                                <td>{{ helper::date_format($area->updated_at, $vendor_id) }}<br>
                                    {{ helper::time_format($area->updated_at, $vendor_id) }}

                                </td>
                                <td>
                                    <a href="{{URL::to('admin/areas/edit-'.$area->id)}}" class="btn btn-sm btn-info btn-size" tooltip="{{trans('labels.edit')}}"> <i class="fa-regular fa-pen-to-square"></i></a>
                                    <a @if(env('Environment')=='sendbox' ) onclick="myFunction()" @else onclick="statusupdate('{{URL::to('admin/areas/delete-'.$area->id)}}')" @endif class="btn btn-sm btn-danger btn-size" tooltip="Delete"> <i class="fa-regular fa-trash"></i></a>
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
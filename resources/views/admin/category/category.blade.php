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
    @php
        // Calculer les limites catégories
        $planInfo = helper::getPlanInfo($vendor_id);
        $current_categories = App\Models\Category::where('vendor_id', $vendor_id)->count();
        $categories_limit = $planInfo['categories_limit'];
        $is_unlimited = $categories_limit == -1;
        $categories_percentage = $is_unlimited ? 0 : (($current_categories / $categories_limit) * 100);
        $limit_color = $categories_percentage >= 100 ? 'danger' : ($categories_percentage >= 80 ? 'warning' : 'success');
        $can_add_category = $is_unlimited || $current_categories < $categories_limit;
    @endphp

    {{-- Indicateur de limite catégories --}}
    @if (Auth::user()->type != 1)
        <div class="alert alert-{{ $limit_color }} alert-dismissible fade show mb-3" role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fa-solid fa-layer-group me-2"></i>
                    <strong>{{ trans('labels.categories') }}:</strong>
                    @if ($is_unlimited)
                        {{ $current_categories }} / {{ trans('labels.unlimited') }}
                    @else
                        {{ $current_categories }} / {{ $categories_limit }}
                        <span class="ms-2">({{ number_format($categories_percentage, 0) }}%)</span>
                    @endif
                    <span class="badge bg-{{ $limit_color }} ms-2">{{ $planInfo['plan_name'] }}</span>
                </div>
                @if (!$is_unlimited && $categories_percentage >= 80)
                    <a href="{{ URL::to('admin/plan') }}" class="btn btn-sm btn-{{ $limit_color }}">
                        <i class="fa-solid fa-arrow-up me-1"></i>{{ trans('labels.upgrade_plan') }}
                    </a>
                @endif
            </div>
            @if (!$is_unlimited)
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-{{ $limit_color }}" role="progressbar"
                         style="width: {{ min($categories_percentage, 100) }}%"
                         aria-valuenow="{{ $current_categories }}"
                         aria-valuemin="0"
                         aria-valuemax="{{ $categories_limit }}">
                    </div>
                </div>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

        <div class="row justify-content-between align-items-center mb-3">
            <div class="col-12 col-md-4">
                <h5 class="pages-title fs-2">{{ trans('labels.category') }}</h5>
                <div class="d-flex">
                @include('admin.layout.breadcrumb')
                </div>
            </div>
            <div class="col-12 col-md-8">
                <div class="d-flex justify-content-end">
                    <a href="{{ URL::to('admin/categories/add') }}"
                       class="btn-add {{ Auth::user()->type == 4 ? (helper::check_access('role_categories', Auth::user()->role_id, $vendor_id, 'add') == 1 ? '' : 'd-none') : '' }} {{ !$can_add_category ? 'disabled' : '' }}"
                       @if (!$can_add_category)
                           data-bs-toggle="tooltip"
                           data-bs-placement="left"
                           title="{{ trans('labels.category_limit_reached') }}. {{ trans('labels.upgrade_to_add_more') }}"
                           onclick="return false;"
                       @endif>
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
                                        <td>{{ trans('labels.srno') }}</td>
                                        <td>{{ trans('labels.image') }}</td>
                                        <td>{{ trans('labels.category') }}</td>
                                        <td>{{ trans('labels.status') }}</td>
                                        <td>{{ trans('labels.created_date') }}</td>
                                        <td>{{ trans('labels.updated_date') }}</td>
                                        <td>{{ trans('labels.action') }}</td>
                                    </tr>
                                </thead>
                                <tbody id="tabledetails" data-url="{{url('admin/categories/reorder_category')}}">
                                    @php $i=1; @endphp
                                    @foreach ($allcategories as $category)
                                        <tr class="fs-7 row1" id="dataid{{$category->id}}" data-id="{{$category->id}}">
                                        <td><a tooltip="{{trans('labels.move')}}"><i class="fa-light fa-up-down-left-right mx-2"></i></a></td>
                                            <td>@php echo $i++ @endphp</td>
                                            <td><img src="{{url(env('ASSETSPATHURL').'admin-assets/images/category/'.$category->image)}}" alt="" width="50" class="img-fluid rounded hw-50 object-fit-cover"></td>
                                            <td>{{ $category->name }}</td>

                                            <td>
                                                @if ($category->is_available == '1')
                                                    <a @if (env('Environment') == 'sendbox') onclick="myFunction()" @else onclick="statusupdate('{{ URL::to('admin/categories/change_status-' . $category->slug . '/2') }}')" @endif
                                                        class="btn btn-sm btn-success btn-size" tooltip="{{trans('labels.active')}}"><i class="fas fa-check"></i></a>
                                                @else
                                                    <a @if (env('Environment') == 'sendbox') onclick="myFunction()" @else onclick="statusupdate('{{ URL::to('admin/categories/change_status-' . $category->slug . '/1') }}')" @endif
                                                        class="btn btn-sm btn-danger btn-xmark" tooltip="{{trans('labels.in_active')}}"><i class="fas fa-close"></i></a>
                                                @endif
                                            </td>
                                            <td>{{ helper::date_format($category->created_at,$vendor_id) }}<br>
                                                {{ helper::time_format($category->created_at,$vendor_id) }}

                                            </td>
                                            <td>{{ helper::date_format($category->updated_at,$vendor_id) }}<br>
                                                {{ helper::time_format($category->updated_at,$vendor_id) }}

                                            </td>
                                            <td>
                                                <a href="{{ URL::to('admin/categories/edit-' . $category->slug) }}"
                                                    class="btn btn-sm btn-info btn-size" tooltip="{{trans('labels.edit')}}"> <i
                                                        class="fa-regular fa-pen-to-square"></i></a>
                                                <a @if (env('Environment') == 'sendbox') onclick="myFunction()" @else onclick="statusupdate('{{ URL::to('admin/categories/delete-' . $category->slug) }}')" @endif
                                                    class="btn btn-sm btn-danger btn-size" tooltip="{{trans('labels.delete')}}"> <i
                                                        class="fa-regular fa-trash"></i></a>
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

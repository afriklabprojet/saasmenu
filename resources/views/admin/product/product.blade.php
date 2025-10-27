@extends('admin.layout.default')
@php
    if (Auth::user()->type == 4) {
        $vendor_id = Auth::user()->vendor_id;
    } else {
        $vendor_id = Auth::user()->id;
    }
@endphp
@section('content')
    @php
        // Calculer les limites produits
        $planInfo = helper::getPlanInfo($vendor_id);
        $current_products = App\Models\Item::where('vendor_id', $vendor_id)->count();
        $products_limit = $planInfo['products_limit'];
        $is_unlimited = $products_limit == -1;
        $products_percentage = $is_unlimited ? 0 : (($current_products / $products_limit) * 100);
        $limit_color = $products_percentage >= 100 ? 'danger' : ($products_percentage >= 80 ? 'warning' : 'success');
        $can_add_product = $is_unlimited || $current_products < $products_limit;
    @endphp

    {{-- Indicateur de limite produits --}}
    @if (Auth::user()->type != 1)
        <div class="alert alert-{{ $limit_color }} alert-dismissible fade show mb-3" role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fa-solid fa-box me-2"></i>
                    <strong>{{ trans('labels.products') }}:</strong>
                    @if ($is_unlimited)
                        {{ $current_products }} / {{ trans('labels.unlimited') }}
                    @else
                        {{ $current_products }} / {{ $products_limit }}
                        <span class="ms-2">({{ number_format($products_percentage, 0) }}%)</span>
                    @endif
                    <span class="badge bg-{{ $limit_color }} ms-2">{{ $planInfo['plan_name'] }}</span>
                </div>
                @if (!$is_unlimited && $products_percentage >= 80)
                    <a href="{{ URL::to('admin/plan') }}" class="btn btn-sm btn-{{ $limit_color }}">
                        <i class="fa-solid fa-arrow-up me-1"></i>{{ trans('labels.upgrade_plan') }}
                    </a>
                @endif
            </div>
            @if (!$is_unlimited)
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-{{ $limit_color }}" role="progressbar"
                         style="width: {{ min($products_percentage, 100) }}%"
                         aria-valuenow="{{ $current_products }}"
                         aria-valuemin="0"
                         aria-valuemax="{{ $products_limit }}">
                    </div>
                </div>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-between align-items-center mb-3">
        <div class="col-12 col-md-4">
            <h5 class="pages-title fs-2">{{ trans('labels.products') }}</h5>
            @include('admin.layout.breadcrumb')
        </div>
        <div class="col-12 col-md-8">
            <div class="d-flex justify-content-end">
                <a href="{{ URL::to('admin/products/add') }}"
                   class="btn-add {{ Auth::user()->type == 4 ? (helper::check_access('role_products', Auth::user()->role_id, $vendor_id, 'add') == 1 ? '' : 'd-none') : '' }} {{ !$can_add_product ? 'disabled' : '' }}"
                   @if (!$can_add_product)
                       data-bs-toggle="tooltip"
                       data-bs-placement="left"
                       title="{{ trans('labels.product_limit_reached') }}. {{ trans('labels.upgrade_to_add_more') }}"
                       onclick="return false;"
                   @endif>
                    <i class="fa-regular fa-plus mx-1"></i>{{ trans('labels.add') }}
                </a>
                @if ($getproductslist->count() > 0)
            <a href="{{ URL::to('/admin/exportproduct') }}" class="btn-add mx-2 {{ Auth::user()->type == 4 ? (helper::check_access('role_products', Auth::user()->role_id, $vendor_id, 'add') == 1 ? '' : 'd-none') : '' }} mx-2">{{ trans('labels.export') }}</a>
            @endif
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
                                    <td>{{ trans('labels.name') }}</td>
                                    <td>{{ trans('labels.price') }}</td>
                                    <td>{{ trans('labels.stock') }}</td>
                                    <td>{{ trans('labels.status') }}</td>
                                    <td>{{ trans('labels.created_date') }}</td>
                                    <td>{{ trans('labels.updated_date') }}</td>
                                    <td>{{ trans('labels.action') }}</td>
                                </tr>
                            </thead>
                            <tbody id="tabledetails" data-url="{{url('admin/products/reorder_product')}}">
                                @php $i = 1; @endphp
                                @foreach ($getproductslist as $product)
                                    <tr class="fs-7 row1" id="dataid{{$product->id}}" data-id="{{$product->id}}">
                                        <td><a tooltip="{{trans('labels.move')}}"><i class="fa-light fa-up-down-left-right mx-2"></i></a></td>
                                        <td>@php echo $i++; @endphp</td>
                                        <td><img src="@if( @$product['item_image']->image_url != null ) {{ @$product['item_image']->image_url }} @else {{ helper::image_path($product->image) }} @endif"
                                                class="img-fluid rounded hw-50 object-fit-cover" alt=""> </td>
                                        <td>{{ @$product['category_info']->name }}</td>
                                        <td>{{ $product->item_name }}
                                        </td>
                                         <td>
                                            @if ($product->has_variants == 1)
                                                <span class="badge bg-info">{{ trans('labels.in_variants') }}</span><br>
                                            @else
                                                {{ helper::currency_formate($product->item_price, $vendor_id) }}
                                            @endif
                                        </td>
                                         <td>
                                            @if ($product->has_variants == 1)

                                                    <span
                                                        class="badge bg-info">{{ trans('labels.in_variants') }}</span><br>
                                                    @if (helper::checklowqty($product->id, $product->vendor_id) == 1)
                                                        <span class="badge bg-warning">{{ trans('labels.low_qty') }}</span>
                                                    @endif

                                            @else
                                                @if ($product->stock_management == 1)
                                                    @if (helper::checklowqty($product->id, $product->vendor_id) == 1)
                                                        <span
                                                            class="badge bg-success">{{ trans('labels.in_stock') }}</span><br>
                                                        <span class="badge bg-warning">{{ trans('labels.low_qty') }}</span>
                                                    @elseif(helper::checklowqty($product->id, $product->vendor_id) == 2)
                                                        <span
                                                            class="badge bg-danger">{{ trans('labels.out_of_stock') }}</span>
                                                    @elseif(helper::checklowqty($product->id, $product->vendor_id) == 3)
                                                        -
                                                    @else
                                                        <span
                                                            class="badge bg-success">{{ trans('labels.in_stock') }}</span>
                                                    @endif
                                                    @else
                                                    -
                                                @endif
                                            @endif

                                        </td>

                                        <td>
                                            @if ($product->is_available == '1')
                                                <a @if (env('Environment') == 'sendbox') onclick="myFunction()" @else onclick="statusupdate('{{ URL::to('admin/products/status-' . $product->slug . '/2') }}')" @endif
                                                    class="btn btn-sm btn-success btn-size {{ Auth::user()->type == 4 ? (helper::check_access('role_shipping_area', Auth::user()->role_id, $vendor_id, 'edit') == 1 ? '' : 'd-none') : '' }}" tooltip="{{trans('labels.active')}}"><i class="fas fa-check"></i></a>
                                            @else
                                                <a @if (env('Environment') == 'sendbox') onclick="myFunction()" @else onclick="statusupdate('{{ URL::to('admin/products/status-' . $product->slug . '/1') }}')" @endif
                                                    class="btn btn-sm btn-danger btn-xmark {{ Auth::user()->type == 4 ? (helper::check_access('role_shipping_area', Auth::user()->role_id, $vendor_id, 'edit') == 1 ? '' : 'd-none') : '' }}" tooltip="{{trans('labels.in_active')}}"><i class="fas fa-close"></i></a>
                                            @endif
                                        </td>
                                        <td>{{ helper::date_format($product->created_at, $vendor_id) }}<br>
                                            {{ helper::time_format($product->created_at, $vendor_id) }}

                                        </td>
                                        <td>{{ helper::date_format($product->updated_at, $vendor_id) }}<br>
                                            {{ helper::time_format($product->updated_at, $vendor_id) }}

                                        </td>
                                        <td>
                                            <a class="btn btn-sm btn-info btn-size {{ Auth::user()->type == 4 ? (helper::check_access('role_shipping_area', Auth::user()->role_id, $vendor_id, 'edit') == 1 ? '' : 'd-none') : '' }}" tooltip="{{trans('labels.edit')}}"
                                                href="{{ URL::to('admin/products/edit-' . $product->slug) }}"> <i
                                                    class="fa-regular fa-pen-to-square"></i></a>
                                            <a class="btn btn-sm btn-danger btn-size {{ Auth::user()->type == 4 ? (helper::check_access('role_shipping_area', Auth::user()->role_id, $vendor_id, 'delete') == 1 ? '' : 'd-none') : '' }}" tooltip="{{trans('labels.delete')}}"
                                                @if (env('Environment') == 'sendbox') onclick="myFunction()" @else onclick="statusupdate('{{ URL::to('admin/products/delete-' . $product->slug) }}')" @endif><i
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

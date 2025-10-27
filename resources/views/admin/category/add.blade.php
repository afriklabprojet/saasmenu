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
    // Vérifier les limites catégories
    $planInfo = helper::getPlanInfo($vendor_id);
    $current_categories = App\Models\Category::where('vendor_id', $vendor_id)->count();
    $categories_limit = $planInfo['categories_limit'];
    $is_unlimited = $categories_limit == -1;
    $categories_percentage = $is_unlimited ? 0 : (($current_categories / $categories_limit) * 100);
@endphp

    <div class="row justify-content-between align-items-center mb-3">
        <div class="col-12">
            <h5 class="pages-title fs-2">{{ trans('labels.add_new') }}</h5>
            <div class="d-flex">
                @include('admin.layout.breadcrumb')
            </div>
        </div>

    </div>

    {{-- Message d'avertissement limite proche --}}
    @if (!$is_unlimited && $categories_percentage >= 80 && $categories_percentage < 100)
        <div class="alert alert-warning mb-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <strong>{{ trans('labels.warning') }}!</strong>
            {{ trans('labels.you_are_using') }} {{ $current_categories }}/{{ $categories_limit }} {{ trans('labels.categories') }}
            ({{ number_format($categories_percentage, 0) }}%).
            <a href="{{ URL::to('admin/plan') }}" class="alert-link">{{ trans('labels.upgrade_now') }}</a>
            {{ trans('labels.to_add_more') }}.
        </div>
    @endif

    <div class="row mb-7">
        <div class="col-12">
            <div class="card border-0 box-shadow">
                <div class="card-body">
                    <form action="{{ URL::to('admin/categories/save') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="form-group row">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">{{ trans('labels.name') }}<span class="text-danger"> *
                                        </span></label>
                                    <input type="text" class="form-control" name="category_name"
                                        value="{{ old('category_name') }}" placeholder="{{ trans('labels.name') }}" required>
                                </div>
                                @error('category_name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror

                                <div class="col-12 col-md-6">
                                    <label class="form-label">{{ trans('labels.image') }}<span class="text-danger"> *
                                        </span></label>
                                    <input type="file" class="form-control" name="category_image"
                                        value="{{ old('category_image') }}" placeholder="{{ trans('labels.image') }}" required>
                                </div>
                                @error('category_image')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror


                            </div>
                            <div class="form-group text-end">
                                <a href="{{ URL::to('admin/categories') }}" class="btn btn-danger btn-cancel m-1">{{
                                    trans('labels.cancel') }}</a>
                                <button class="btn btn-success btn-succes m-1 {{ Auth::user()->type == 4 ? (helper::check_access('role_categories', Auth::user()->role_id, $vendor_id, 'add') == 1  ? '' : 'd-none') : '' }}" @if (env('Environment') == 'sendbox') type="button"
                                    onclick="myFunction()" @else type="submit" @endif>{{ trans('labels.save')
                                    }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

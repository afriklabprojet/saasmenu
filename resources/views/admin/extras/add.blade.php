@extends('admin.layout.default')
@section('content')    <div class="row justify-content-between align-items-center mb-3">
        <div class="col-12">
            <h5 class="pages-title fs-2">{{ trans('labels.add_new') }}</h5>
            <div class="d-flex">
                @include('admin.layout.breadcrumb')
            </div>
        </div>

    </div>
   
    <div class="row">
        <div class="col-12">
            <div class="card border-0 box-shadow">
                <div class="card-body">
                    <form action="{{ URL::to('admin/extras/save') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="form-label">{{ trans('labels.name') }}<span class="text-danger"> *
                                    </span></label>
                                <input type="text" class="form-control" name="name"
                                    value="{{ old('name') }}" placeholder="{{ trans('labels.name') }}" required>
                              
                            </div>
                            <div class="form-group col-md-6">
                                <label class="form-label">{{ trans('labels.price') }} <span
                                        class="text-danger"> * </span></label>
                                <input type="text" class="form-control numbers_only" name="price"
                                    value="{{old('price')}}"
                                    placeholder="{{ trans('labels.price') }}" id="price" required>
                            </div>
                            <div class="form-group text-end">
                                <a href="{{ URL::to('admin/extras') }}" class="btn btn-danger btn-cancel m-1">{{
                                    trans('labels.cancel') }}</a>
                                <button class="btn btn-success btn-succes m-1  {{ Auth::user()->type == 4 ? (helper::check_access('role_global_extras', Auth::user()->role_id, Auth::user()->vendor_id, 'add') == 1 ? '' : 'd-none') : '' }}" @if (env('Environment') == 'sendbox') type="button"
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
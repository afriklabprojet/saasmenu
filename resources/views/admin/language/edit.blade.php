@extends('admin.layout.default')
@section('content')
<div class="d-flex justify-content-between align-items-center">
    <h5 class="text-uppercase">{{ trans('labels.edit') }}</h5>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="{{ URL::to('admin/language-settings') }}">{{ trans('labels.languages') }}</a></li>
            <li class="breadcrumb-item active {{session()->get('direction') == 2 ? 'breadcrumb-rtl' : ''}}" aria-current="page">{{ trans('labels.edit') }}</li>
        </ol>
    </nav>
</div>
<div class="row">
    <div class="col-12">
        <div class="card border-0 my-3">
            <div class="card-body">
                <form action="{{ URL::to('/admin/language-settings/update-' . $getlanguage->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-sm-3 col-md-12">
                            <div class="form-group mb-3">
                                <label for="layout" class="col-form-label">{{trans('labels.layout')}} <span class="text-danger"> *
                                </span></label>
                                <select name="layout" class="form-control layout-dropdown" id="layout" required>
                                    <option value="" selected>{{trans('labels.select')}}</option>
                                    <option value="1"{{ $getlanguage->layout == "1" ? 'selected' : '' }} >{{ trans('labels.ltr') }}</option>
                                    <option value="2"{{ $getlanguage->layout == "2" ? 'selected' : '' }} >{{ trans('labels.rtl') }}</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="layout" class="col-form-label">{{trans('labels.image')}}</label>
                                <input type="file" class="form-control" name="image">
                                <img src="{{ helper::image_path($getlanguage->image) }}" class="img-fluid rounded hw-50 mt-1" alt="">
                            </div>
                           
                        </div>
                    </div>
                    <div class="text-end">
                        <a href="{{URL::to('admin/language-settings')}}" class="btn btn-outline-danger">{{ trans('labels.cancel') }}</a>
                        <button
                        @if (env('Environment') == 'sendbox') type="button" onclick="myFunction()" @else type="submit" @endif
                        class="btn btn-secondary">{{ trans('labels.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
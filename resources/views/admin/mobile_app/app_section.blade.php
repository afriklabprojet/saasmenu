<div id="app_section" class="hidechild">
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 box-shadow">
                <div class="card-body pb-0">
                    <form action="{{ URL::to('admin/app_section/update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="text-uppercase">{{ trans('labels.mobile_app_section') }}</h5>
                          
                                <div>
                                    <input id="mobile_app-switch" type="checkbox" class="checkbox-switch"
                                        name="mobile_app_on_off" value="1"
                                        {{ @$app->mobile_app_on_off == 1 ? 'checked' : '' }}>
                                    <label for="mobile_app-switch" class="switch">
                                        <span
                                            class="{{ session()->get('direction') == 2 ? 'switch__circle-rtl' : 'switch__circle' }}"><span
                                                class="switch__circle-inner"></span></span>
                                        <span
                                            class="switch__left {{ session()->get('direction') == 2 ? 'pe-2' : 'ps-2' }}">{{ trans('labels.off') }}</span>
                                        <span
                                            class="switch__right {{ session()->get('direction') == 2 ? 'ps-2' : 'pe-2' }}">{{ trans('labels.on') }}</span>
                                    </label>
                                </div>
                           
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="form-label">{{ trans('labels.android_link') }}</label>
                                <input type="text" class="form-control" name="android_link"
                                    value="{{ @$app->android_link }}"
                                    placeholder="{{ trans('labels.android_link') }}">
                                @error('android_link')
                                    <span class="text-danger">{{ $message }}</span> <br>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="form-label">{{ trans('labels.ios_link') }} </label>
                                <input type="text" class="form-control" name="ios_link"
                                    value="{{ @$app->ios_link }}" placeholder="{{ trans('labels.ios_link') }}">
                                @error('ios_link')
                                    <span class="text-danger">{{ $message }}</span> <br>
                                @enderror
                            </div>
                            @if (Auth::user()->type == 1)
                                <div class="form-group col-md-6">
                                    <label class="form-label">{{ trans('labels.image') }} </label>
                                    <input type="file" class="form-control" name="image">
                                    @error('image')
                                        <span class="text-danger">{{ $message }}</span> <br>
                                    @enderror
                                    <img class="img-fluid rounded hw-70 mt-1 object-fit-cover"
                                        src="{{ helper::image_Path(@$app->image) }}" alt="">
                                </div>
                            @endif
                            <div class="form-group text-end">
                                <button class="btn btn-secondary {{ Auth::user()->type == 4 ? (helper::check_access('role_settings', Auth::user()->role_id, $vendor_id, 'edit') == 1 ? '' : 'd-none') : '' }}"
                                    @if (env('Environment') == 'sendbox') type="button" onclick="myFunction()" @else type="submit" @endif>{{ trans('labels.save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

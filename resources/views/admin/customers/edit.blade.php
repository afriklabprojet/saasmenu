@extends('admin.layout.default')
@section('content')
    <div class="row align-items-center mb-3">
        <div class="col-12">
            <h5 class="pages-title fs-2">{{ trans('labels.edit_customer') }}</h5>
            @include('admin.layout.breadcrumb')
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">{{ trans('labels.edit_customer') }}</h5>
                    <div>
                        <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-secondary btn-sm">
                            <i class="fa-solid fa-arrow-left"></i> {{ trans('labels.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ trans('labels.name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $customer->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ trans('labels.email') }} <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email', $customer->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ trans('labels.phone') }}</label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                           value="{{ old('phone', $customer->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ trans('labels.status') }} <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="1" {{ old('status', $customer->status) == 1 ? 'selected' : '' }}>
                                            {{ trans('labels.active') }}
                                        </option>
                                        <option value="2" {{ old('status', $customer->status) == 2 ? 'selected' : '' }}>
                                            {{ trans('labels.inactive') }}
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ trans('labels.address') }}</label>
                                    <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                              rows="3">{{ old('address', $customer->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ trans('labels.city') }}</label>
                                    <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                                           value="{{ old('city', $customer->city) }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ trans('labels.postal_code') }}</label>
                                    <input type="text" name="postal_code" class="form-control @error('postal_code') is-invalid @enderror"
                                           value="{{ old('postal_code', $customer->postal_code) }}">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa-solid fa-save"></i> {{ trans('labels.save') }}
                                    </button>
                                    <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-secondary">
                                        {{ trans('labels.cancel') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

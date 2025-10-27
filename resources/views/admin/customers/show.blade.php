@extends('admin.layout.default')
@section('content')
    <div class="row align-items-center mb-3">
        <div class="col-12">
            <h5 class="pages-title fs-2">{{ trans('labels.customer_details') }}</h5>
            @include('admin.layout.breadcrumb')
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">{{ $customer->name }}</h5>
                    <div>
                        <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-primary btn-sm">
                            <i class="fa-regular fa-edit"></i> {{ trans('labels.edit') }}
                        </a>
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fa-solid fa-arrow-left"></i> {{ trans('labels.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ trans('labels.name') }}</label>
                                <p class="text-muted">{{ $customer->name }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ trans('labels.email') }}</label>
                                <p class="text-muted">{{ $customer->email }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ trans('labels.phone') }}</label>
                                <p class="text-muted">{{ $customer->phone ?? '-' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ trans('labels.status') }}</label>
                                <div>
                                    @if($customer->status == 1)
                                        <span class="badge bg-success">{{ trans('labels.active') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ trans('labels.inactive') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ trans('labels.address') }}</label>
                                <p class="text-muted">{{ $customer->address ?? '-' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ trans('labels.city') }}</label>
                                <p class="text-muted">{{ $customer->city ?? '-' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ trans('labels.postal_code') }}</label>
                                <p class="text-muted">{{ $customer->postal_code ?? '-' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ trans('labels.created_date') }}</label>
                                <p class="text-muted">{{ $customer->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

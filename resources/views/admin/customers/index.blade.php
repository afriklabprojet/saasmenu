@extends('admin.layout.default')
@section('content')
    <div class="row align-items-center mb-3">
        <div class="col-12">
            <h5 class="pages-title fs-2">{{ trans('labels.customers') }}</h5>
            @include('admin.layout.breadcrumb')
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 my-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">{{ trans('labels.customers_list') }}</h5>
                    <div>
                        <span class="badge bg-primary">{{ $customers->total() }} {{ trans('labels.total') }}</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($customers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ trans('labels.name') }}</th>
                                        <th>{{ trans('labels.email') }}</th>
                                        <th>{{ trans('labels.phone') }}</th>
                                        <th>{{ trans('labels.city') }}</th>
                                        <th>{{ trans('labels.status') }}</th>
                                        <th>{{ trans('labels.created_date') }}</th>
                                        <th>{{ trans('labels.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customers as $key => $customer)
                                        <tr>
                                            <td>{{ $customers->firstItem() + $key }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm rounded-circle bg-primary text-white me-2">
                                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                                    </div>
                                                    {{ $customer->name }}
                                                </div>
                                            </td>
                                            <td>{{ $customer->email }}</td>
                                            <td>{{ $customer->phone ?? '-' }}</td>
                                            <td>{{ $customer->city ?? '-' }}</td>
                                            <td>
                                                @if($customer->status == 1)
                                                    <span class="badge bg-success">{{ trans('labels.active') }}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ trans('labels.inactive') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $customer->created_at->format('d M Y') }}</td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        {{ trans('labels.action') }}
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('admin.customers.show', $customer->id) }}">
                                                                <i class="fa-regular fa-eye"></i> {{ trans('labels.view') }}
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('admin.customers.edit', $customer->id) }}">
                                                                <i class="fa-regular fa-edit"></i> {{ trans('labels.edit') }}
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger" 
                                                                        onclick="return confirm('{{ trans('messages.are_you_sure') }}')">
                                                                    <i class="fa-regular fa-trash"></i> {{ trans('labels.delete') }}
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $customers->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa-solid fa-users fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">{{ trans('messages.no_data_found') }}</h6>
                            <p class="text-muted">{{ trans('messages.no_customers_found') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
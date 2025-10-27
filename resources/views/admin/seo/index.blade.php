@extends('admin.layout.app')

@section('page-title')
    {{ __('Gestion SEO') }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">{{ __('Optimisation SEO') }}</h3>
                        <div>
                            <a href="{{ route('admin.seo.sitemap') }}" class="btn btn-info">
                                <i class="fas fa-sitemap"></i> {{ __('Générer Sitemap') }}
                            </a>
                            <a href="{{ route('admin.seo.robots') }}" class="btn btn-secondary">
                                <i class="fas fa-robot"></i> {{ __('Générer Robots.txt') }}
                            </a>
                            <a href="{{ route('admin.seo.create', 'home') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> {{ __('Nouveau Meta Tag') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    @endif

                    <!-- Pages rapides -->
                    <div class="mb-4">
                        <h5>{{ __('Pages principales') }}</h5>
                        <div class="btn-group" role="group">
                            <a href="{{ route('admin.seo.create', 'home') }}" class="btn btn-outline-primary">
                                <i class="fas fa-home"></i> Homepage
                            </a>
                            <a href="{{ route('admin.seo.create', 'menu') }}" class="btn btn-outline-primary">
                                <i class="fas fa-utensils"></i> Menu
                            </a>
                            <a href="{{ route('admin.seo.create', 'blog') }}" class="btn btn-outline-primary">
                                <i class="fas fa-blog"></i> Blog
                            </a>
                            <a href="{{ route('admin.seo.create', 'contact') }}" class="btn btn-outline-primary">
                                <i class="fas fa-envelope"></i> Contact
                            </a>
                        </div>
                    </div>

                    @if($seoMetas->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Page') }}</th>
                                    <th>{{ __('Meta Title') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Indexation') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($seoMetas as $meta)
                                <tr>
                                    <td>
                                        <span class="badge badge-info">{{ strtoupper($meta->page_type) }}</span>
                                        @if($meta->page_id)
                                        <small class="text-muted">#{{ $meta->page_id }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 250px;">
                                            {{ $meta->meta_title ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 300px;">
                                            {{ $meta->meta_description ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $meta->index ? 'success' : 'danger' }}">
                                            {{ $meta->robots }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.seo.create', [$meta->page_type, $meta->page_id]) }}"
                                               class="btn btn-sm btn-warning"
                                               title="{{ __('Modifier') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.seo.destroy', $meta->id) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('{{ __('Supprimer ce meta tag ?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="{{ __('Supprimer') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $seoMetas->links() }}
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        {{ __('Aucun meta tag configuré. Commencez par créer les meta tags pour vos pages principales.') }}
                    </div>
                    @endif

                    <!-- Guide SEO -->
                    <div class="mt-4">
                        <h5>{{ __('Guide d\'optimisation SEO') }}</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6><i class="fas fa-check-circle text-success"></i> {{ __('Meta Title') }}</h6>
                                        <small>50-60 caractères maximum pour un affichage optimal</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6><i class="fas fa-check-circle text-success"></i> {{ __('Meta Description') }}</h6>
                                        <small>150-160 caractères pour encourager les clics</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6><i class="fas fa-check-circle text-success"></i> {{ __('Open Graph') }}</h6>
                                        <small>Image 1200x630px pour les partages sociaux</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

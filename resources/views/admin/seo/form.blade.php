@extends('admin.layout.app')

@section('page-title')
    {{ __('Meta Tags SEO') }} - {{ strtoupper($pageType) }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">{{ __('Configurer les Meta Tags') }}</h3>
                        <a href="{{ route('admin.seo.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('Retour') }}
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.seo.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="page_type" value="{{ $pageType }}">
                    <input type="hidden" name="page_id" value="{{ $pageId }}">

                    <div class="card-body">
                        @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <!-- Meta Tags Basiques -->
                        <h5 class="mb-3">{{ __('Meta Tags Basiques') }}</h5>

                        <div class="form-group">
                            <label for="meta_title">{{ __('Meta Title') }} <span class="text-muted">(50-60 caractères)</span></label>
                            <input type="text"
                                   name="meta_title"
                                   id="meta_title"
                                   class="form-control @error('meta_title') is-invalid @enderror"
                                   value="{{ old('meta_title', $seoMeta->meta_title ?? '') }}"
                                   maxlength="60">
                            <small class="form-text text-muted">
                                <span id="title-length">0</span>/60 caractères
                            </small>
                            @error('meta_title')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="meta_description">{{ __('Meta Description') }} <span class="text-muted">(150-160 caractères)</span></label>
                            <textarea name="meta_description"
                                      id="meta_description"
                                      rows="3"
                                      class="form-control @error('meta_description') is-invalid @enderror"
                                      maxlength="160">{{ old('meta_description', $seoMeta->meta_description ?? '') }}</textarea>
                            <small class="form-text text-muted">
                                <span id="desc-length">0</span>/160 caractères
                            </small>
                            @error('meta_description')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="meta_keywords">{{ __('Mots-clés') }} <span class="text-muted">(séparés par virgules)</span></label>
                            <input type="text"
                                   name="meta_keywords"
                                   id="meta_keywords"
                                   class="form-control @error('meta_keywords') is-invalid @enderror"
                                   value="{{ old('meta_keywords', $seoMeta->meta_keywords ?? '') }}"
                                   placeholder="restaurant, livraison, paris, cuisine française">
                            @error('meta_keywords')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <!-- Open Graph (Facebook/WhatsApp) -->
                        <h5 class="mb-3">{{ __('Open Graph (Facebook, WhatsApp)') }}</h5>

                        <div class="form-group">
                            <label for="og_title">{{ __('OG Title') }}</label>
                            <input type="text"
                                   name="og_title"
                                   id="og_title"
                                   class="form-control @error('og_title') is-invalid @enderror"
                                   value="{{ old('og_title', $seoMeta->og_title ?? '') }}">
                            @error('og_title')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="og_description">{{ __('OG Description') }}</label>
                            <textarea name="og_description"
                                      id="og_description"
                                      rows="2"
                                      class="form-control @error('og_description') is-invalid @enderror">{{ old('og_description', $seoMeta->og_description ?? '') }}</textarea>
                            @error('og_description')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="og_image">{{ __('OG Image') }} <span class="text-muted">(1200x630px recommandé)</span></label>
                            @if($seoMeta && $seoMeta->og_image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $seoMeta->og_image) }}" alt="OG Image" class="img-thumbnail" style="max-width: 300px;">
                            </div>
                            @endif
                            <input type="file"
                                   name="og_image"
                                   id="og_image"
                                   class="form-control-file @error('og_image') is-invalid @enderror"
                                   accept="image/jpeg,image/png,image/jpg">
                            @error('og_image')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <!-- Twitter Card -->
                        <h5 class="mb-3">{{ __('Twitter Card') }}</h5>

                        <div class="form-group">
                            <label for="twitter_card">{{ __('Type de carte') }}</label>
                            <select name="twitter_card" id="twitter_card" class="form-control">
                                <option value="summary" {{ old('twitter_card', $seoMeta->twitter_card ?? 'summary_large_image') == 'summary' ? 'selected' : '' }}>
                                    Summary
                                </option>
                                <option value="summary_large_image" {{ old('twitter_card', $seoMeta->twitter_card ?? 'summary_large_image') == 'summary_large_image' ? 'selected' : '' }}>
                                    Summary Large Image
                                </option>
                            </select>
                        </div>

                        <hr class="my-4">

                        <!-- Paramètres avancés -->
                        <h5 class="mb-3">{{ __('Paramètres Avancés') }}</h5>

                        <div class="form-group">
                            <label for="canonical_url">{{ __('URL Canonique') }}</label>
                            <input type="url"
                                   name="canonical_url"
                                   id="canonical_url"
                                   class="form-control @error('canonical_url') is-invalid @enderror"
                                   value="{{ old('canonical_url', $seoMeta->canonical_url ?? '') }}"
                                   placeholder="https://www.exemple.com/page">
                            <small class="form-text text-muted">URL préférée pour éviter le contenu dupliqué</small>
                            @error('canonical_url')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>{{ __('Indexation') }}</label>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       id="index"
                                       name="index"
                                       value="1"
                                       {{ old('index', $seoMeta->index ?? true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="index">
                                    {{ __('Autoriser l\'indexation par les moteurs de recherche') }}
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>{{ __('Suivi des liens') }}</label>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       id="follow"
                                       name="follow"
                                       value="1"
                                       {{ old('follow', $seoMeta->follow ?? true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="follow">
                                    {{ __('Permettre aux robots de suivre les liens de cette page') }}
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="schema_markup">{{ __('Schema.org JSON-LD') }} <span class="text-muted">(optionnel)</span></label>
                            <textarea name="schema_markup"
                                      id="schema_markup"
                                      rows="6"
                                      class="form-control @error('schema_markup') is-invalid @enderror"
                                      placeholder='{"@context": "https://schema.org", "@type": "Restaurant", ...}'>{{ old('schema_markup', $seoMeta->schema_markup ?? '') }}</textarea>
                            <small class="form-text text-muted">Données structurées pour les rich snippets Google</small>
                            @error('schema_markup')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ __('Enregistrer') }}
                        </button>
                        <a href="{{ route('admin.seo.index') }}" class="btn btn-secondary">
                            {{ __('Annuler') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Compteur de caractères pour title
    const titleInput = document.getElementById('meta_title');
    const titleLength = document.getElementById('title-length');

    titleInput.addEventListener('input', function() {
        titleLength.textContent = this.value.length;
    });
    titleLength.textContent = titleInput.value.length;

    // Compteur de caractères pour description
    const descInput = document.getElementById('meta_description');
    const descLength = document.getElementById('desc-length');

    descInput.addEventListener('input', function() {
        descLength.textContent = this.value.length;
    });
    descLength.textContent = descInput.value.length;
});
</script>
@endsection

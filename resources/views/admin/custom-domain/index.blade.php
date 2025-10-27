@extends('admin.layout.default')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 box-shadow">
            <div class="card-header bg-white border-0">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0">🌐 Domaine Personnalisé</h5>
                    @if(Auth::user()->custom_domain && Auth::user()->domain_verified)
                        <span class="badge bg-success">
                            <i class="fa-solid fa-check-circle"></i> Vérifié
                        </span>
                    @elseif(Auth::user()->custom_domain)
                        <span class="badge bg-warning">
                            <i class="fa-solid fa-clock"></i> En attente de vérification
                        </span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-exclamation-circle"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Information sur les abonnements -->
                <div class="alert alert-info mb-4">
                    <h6 class="fw-bold">
                        <i class="fa-solid fa-info-circle"></i> Fonctionnalité disponible dès le plan Starter
                    </h6>
                    <p class="mb-0">
                        Utilisez votre propre domaine (ex: monrestaurant.com) pour renforcer votre marque et offrir une meilleure expérience à vos clients.
                    </p>
                </div>

                @if(!Auth::user()->custom_domain)
                    <!-- Formulaire d'ajout -->
                    <div class="row">
                        <div class="col-lg-8 mx-auto">
                            <form action="{{ route('admin.custom-domain.store') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="custom_domain" class="form-label fw-bold">
                                        Votre Domaine Personnalisé
                                    </label>
                                    <input type="text"
                                           class="form-control @error('custom_domain') is-invalid @enderror"
                                           id="custom_domain"
                                           name="custom_domain"
                                           placeholder="monrestaurant.com"
                                           value="{{ old('custom_domain') }}">
                                    @error('custom_domain')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        Entrez uniquement le domaine sans http:// ou www
                                    </small>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-save"></i> Enregistrer le Domaine
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- Domaine configuré -->
                    <div class="row">
                        <div class="col-lg-8 mx-auto">
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">Votre Domaine</h6>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div>
                                            <p class="mb-1 fw-bold fs-5">{{ Auth::user()->custom_domain }}</p>
                                            @if(Auth::user()->domain_verified)
                                                <small class="text-success">
                                                    <i class="fa-solid fa-check-circle"></i>
                                                    Vérifié le {{ Auth::user()->domain_verified_at->format('d/m/Y à H:i') }}
                                                </small>
                                            @else
                                                <small class="text-warning">
                                                    <i class="fa-solid fa-clock"></i>
                                                    En attente de vérification
                                                </small>
                                            @endif
                                        </div>
                                        <div>
                                            <form action="{{ route('admin.custom-domain.destroy') }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce domaine ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fa-solid fa-trash"></i> Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    @if(!Auth::user()->domain_verified)
                                        <form action="{{ route('admin.custom-domain.verify') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success">
                                                <i class="fa-solid fa-check"></i> Vérifier le Domaine
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            <!-- Instructions de configuration DNS -->
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0 fw-bold">
                                        <i class="fa-solid fa-book"></i> Instructions de Configuration DNS
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="fw-bold">Étape 1 : Accédez à votre registrar de domaine</p>
                                    <p class="text-muted">
                                        Connectez-vous au site où vous avez acheté votre domaine (GoDaddy, Namecheap, OVH, etc.)
                                    </p>

                                    <p class="fw-bold mt-3">Étape 2 : Configurez un enregistrement CNAME</p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Nom</th>
                                                    <th>Valeur</th>
                                                    <th>TTL</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><code>CNAME</code></td>
                                                    <td><code>www</code></td>
                                                    <td><code>{{ parse_url(config('app.url'), PHP_URL_HOST) }}</code></td>
                                                    <td><code>3600</code></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <p class="fw-bold mt-3">Étape 3 : Configurez un enregistrement A (optionnel)</p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Nom</th>
                                                    <th>Valeur</th>
                                                    <th>TTL</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><code>A</code></td>
                                                    <td><code>@</code></td>
                                                    <td><code>{{ gethostbyname(parse_url(config('app.url'), PHP_URL_HOST)) }}</code></td>
                                                    <td><code>3600</code></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="alert alert-warning mt-3">
                                        <i class="fa-solid fa-exclamation-triangle"></i>
                                        <strong>Important :</strong> La propagation DNS peut prendre de 1 à 48 heures.
                                        Attendez quelques heures avant de cliquer sur "Vérifier le Domaine".
                                    </div>

                                    <p class="fw-bold mt-3">Étape 4 : Vérifiez votre domaine</p>
                                    <p class="text-muted">
                                        Une fois la configuration DNS effectuée et après avoir attendu la propagation,
                                        cliquez sur le bouton "Vérifier le Domaine" ci-dessus.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Avantages -->
                <div class="row mt-5">
                    <div class="col-12">
                        <h6 class="fw-bold mb-3">✨ Avantages d'un Domaine Personnalisé</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="fw-bold">🎯 Image Professionnelle</h6>
                                        <p class="text-muted small mb-0">
                                            Renforcez la crédibilité de votre restaurant avec votre propre nom de domaine
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="fw-bold">🚀 Meilleur Référencement</h6>
                                        <p class="text-muted small mb-0">
                                            Améliorez votre SEO et votre visibilité sur les moteurs de recherche
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="fw-bold">🔗 URL Mémorable</h6>
                                        <p class="text-muted small mb-0">
                                            Facilitez la mémorisation et le partage de votre adresse web
                                        </p>
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

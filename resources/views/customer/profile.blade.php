@extends('customer.layout')

@section('customer-content')
<div class="mb-4">
    <h2 class="mb-0">Mon Profil</h2>
    <p class="text-muted">Gérez vos informations personnelles</p>
</div>

<div class="row">
    <!-- Informations du Profil -->
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations Personnelles</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('customer.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Nom complet <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Téléphone</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                               id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="avatar" class="form-label">Photo de profil</label>
                        <input type="file" class="form-control @error('avatar') is-invalid @enderror"
                               id="avatar" name="avatar" accept="image/*" onchange="previewImage(this)">
                        @error('avatar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        @if($user->avatar)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $user->avatar) }}"
                                     alt="Avatar actuel" class="img-thumbnail" style="max-width: 150px;" id="current-avatar">
                            </div>
                        @endif
                        <div class="mt-2" id="preview-container" style="display: none;">
                            <img id="avatar-preview" class="img-thumbnail" style="max-width: 150px;">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Photo de Profil -->
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center">
                @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}"
                         class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                @else
                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3"
                         style="width: 150px; height: 150px; font-size: 3rem;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <h5 class="mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-0">{{ $user->email }}</p>
                <hr>
                <small class="text-muted">Membre depuis {{ $user->created_at->format('M Y') }}</small>
            </div>
        </div>
    </div>
</div>

<!-- Changement de Mot de Passe -->
<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Changer le Mot de Passe</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('customer.password.change') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mot de passe actuel <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                               id="current_password" name="current_password" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nouveau mot de passe <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('new_password') is-invalid @enderror"
                               id="new_password" name="new_password" required>
                        @error('new_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Minimum 8 caractères</small>
                    </div>

                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                        <input type="password" class="form-control"
                               id="new_password_confirmation" name="new_password_confirmation" required>
                    </div>

                    <button type="submit" class="btn btn-warning text-white">
                        <i class="fas fa-key me-2"></i>Changer le mot de passe
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').src = e.target.result;
            document.getElementById('preview-container').style.display = 'block';
            var currentAvatar = document.getElementById('current-avatar');
            if (currentAvatar) {
                currentAvatar.style.display = 'none';
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection

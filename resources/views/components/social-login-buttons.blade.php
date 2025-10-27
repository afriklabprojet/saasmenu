<!-- Boutons de connexion sociale -->
<div class="social-login-buttons">
    <div class="text-center mb-3">
        <p class="text-muted">{{ __('Ou connectez-vous avec') }}</p>
    </div>

    <div class="d-grid gap-2">
        <!-- Google Login -->
        <a href="{{ route('social.login', 'google') }}" class="btn btn-outline-danger btn-block">
            <i class="fab fa-google"></i> {{ __('Continuer avec Google') }}
        </a>

        <!-- Facebook Login -->
        <a href="{{ route('social.login', 'facebook') }}" class="btn btn-outline-primary btn-block">
            <i class="fab fa-facebook-f"></i> {{ __('Continuer avec Facebook') }}
        </a>

        <!-- Apple Login (optionnel) -->
        @if(config('services.apple.client_id'))
        <a href="{{ route('social.login', 'apple') }}" class="btn btn-outline-dark btn-block">
            <i class="fab fa-apple"></i> {{ __('Continuer avec Apple') }}
        </a>
        @endif
    </div>
</div>

<style>
.social-login-buttons .btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 12px 20px;
    font-weight: 500;
    margin-bottom: 10px;
}

.social-login-buttons .btn i {
    font-size: 1.2em;
}

.social-login-buttons .btn-outline-danger:hover {
    background-color: #db4437;
    color: white;
    border-color: #db4437;
}

.social-login-buttons .btn-outline-primary:hover {
    background-color: #1877f2;
    color: white;
    border-color: #1877f2;
}

.social-login-buttons .btn-outline-dark:hover {
    background-color: #000;
    color: white;
    border-color: #000;
}
</style>

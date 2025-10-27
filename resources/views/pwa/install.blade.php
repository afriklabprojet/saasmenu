@extends('front.theme.default')

@section('content')
<div class="container-fluid p-0">
    <div class="pwa-install-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10 text-center">
                    <div class="pwa-install-content">
                        <div class="app-icon mb-4">
                            <img src="{{ helper::image_path(helper::appdata($vendor_id ?? 1)->favicon ?? 'logo.png') }}"
                                 alt="E-menu App" class="pwa-icon">
                        </div>

                        <h1 class="pwa-title">Installez E-menu</h1>
                        <p class="pwa-description">
                            Accédez rapidement à vos restaurants favoris directement depuis votre écran d'accueil.
                            Installation gratuite et rapide !
                        </p>

                        <div class="pwa-features mb-5">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="feature-item">
                                        <div class="feature-icon">📱</div>
                                        <h5>Accès rapide</h5>
                                        <p>Lancez l'app d'un simple clic</p>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="feature-item">
                                        <div class="feature-icon">⚡</div>
                                        <h5>Ultra rapide</h5>
                                        <p>Chargement instantané</p>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="feature-item">
                                        <div class="feature-icon">🔔</div>
                                        <h5>Notifications</h5>
                                        <p>Restez informé de vos commandes</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="install-buttons">
                            <button id="installPWA" class="btn btn-primary btn-lg me-3 d-none">
                                <i class="fas fa-download me-2"></i>
                                Installer maintenant
                            </button>

                            <div id="manualInstall" class="manual-install">
                                <h5>Installation manuelle :</h5>
                                <div class="install-steps">
                                    <div class="step" id="iosInstructions" style="display: none;">
                                        <p><strong>Sur iPhone/iPad :</strong></p>
                                        <ol>
                                            <li>Appuyez sur <i class="fas fa-share"></i> en bas de Safari</li>
                                            <li>Sélectionnez "Ajouter à l'écran d'accueil"</li>
                                            <li>Appuyez sur "Ajouter"</li>
                                        </ol>
                                    </div>

                                    <div class="step" id="androidInstructions" style="display: none;">
                                        <p><strong>Sur Android :</strong></p>
                                        <ol>
                                            <li>Appuyez sur le menu ⋮ de Chrome</li>
                                            <li>Sélectionnez "Ajouter à l'écran d'accueil"</li>
                                            <li>Appuyez sur "Ajouter"</li>
                                        </ol>
                                    </div>

                                    <div class="step" id="desktopInstructions" style="display: none;">
                                        <p><strong>Sur ordinateur :</strong></p>
                                        <ol>
                                            <li>Cliquez sur l'icône d'installation dans la barre d'adresse</li>
                                            <li>Ou utilisez le menu du navigateur</li>
                                            <li>Sélectionnez "Installer E-menu"</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>
                                Retour au site
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.pwa-install-hero {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    padding: 2rem 0;
}

.pwa-install-content {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 3rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.pwa-icon {
    width: 120px;
    height: 120px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.pwa-title {
    font-size: 3rem;
    font-weight: 300;
    margin-bottom: 1rem;
}

.pwa-description {
    font-size: 1.2rem;
    opacity: 0.9;
    margin-bottom: 3rem;
    line-height: 1.6;
}

.feature-item {
    padding: 1rem;
    text-align: center;
}

.feature-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.feature-item h5 {
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.feature-item p {
    opacity: 0.8;
    margin: 0;
}

.manual-install {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    padding: 2rem;
    margin: 2rem 0;
}

.install-steps ol {
    text-align: left;
    max-width: 400px;
    margin: 0 auto;
}

.install-steps li {
    margin-bottom: 0.5rem;
    padding: 0.25rem 0;
}

.btn-lg {
    padding: 12px 30px;
    font-size: 1.1rem;
    border-radius: 25px;
}

@media (max-width: 768px) {
    .pwa-install-content {
        padding: 2rem 1.5rem;
        margin: 1rem;
    }

    .pwa-title {
        font-size: 2.5rem;
    }

    .pwa-description {
        font-size: 1rem;
    }
}
</style>
@endsection

@section('scripts')
<script>
let deferredPrompt;
let installButton = document.getElementById('installPWA');
let manualInstall = document.getElementById('manualInstall');

// Détecter si l'installation PWA est supportée
window.addEventListener('beforeinstallprompt', (e) => {
    console.log('PWA installation disponible');
    e.preventDefault();
    deferredPrompt = e;

    // Afficher le bouton d'installation
    installButton.classList.remove('d-none');
    manualInstall.style.display = 'none';
});

// Gérer l'installation PWA
installButton.addEventListener('click', async () => {
    if (!deferredPrompt) {
        return;
    }

    // Afficher le prompt d'installation
    deferredPrompt.prompt();

    // Attendre la réponse de l'utilisateur
    const { outcome } = await deferredPrompt.userChoice;

    if (outcome === 'accepted') {
        console.log('PWA installée avec succès');
        // Redirection ou message de succès
        setTimeout(() => {
            window.location.href = '{{ url("/") }}';
        }, 1000);
    } else {
        console.log('Installation PWA refusée');
    }

    // Réinitialiser la variable
    deferredPrompt = null;
    installButton.classList.add('d-none');
});

// Détecter le type d'appareil et afficher les instructions appropriées
function detectDevice() {
    const userAgent = navigator.userAgent.toLowerCase();

    if (/iphone|ipad|ipod/.test(userAgent)) {
        document.getElementById('iosInstructions').style.display = 'block';
    } else if (/android/.test(userAgent)) {
        document.getElementById('androidInstructions').style.display = 'block';
    } else {
        document.getElementById('desktopInstructions').style.display = 'block';
    }
}

// Vérifier si l'app est déjà installée
window.addEventListener('appinstalled', () => {
    console.log('PWA installée avec succès');
    installButton.classList.add('d-none');

    // Afficher un message de succès
    const successMessage = document.createElement('div');
    successMessage.className = 'alert alert-success';
    successMessage.innerHTML = '✅ E-menu a été installé avec succès !';
    document.querySelector('.install-buttons').prepend(successMessage);

    setTimeout(() => {
        window.location.href = '{{ url("/") }}';
    }, 2000);
});

// Initialiser
document.addEventListener('DOMContentLoaded', () => {
    detectDevice();

    // Si le prompt n'est pas supporté, afficher les instructions manuelles
    setTimeout(() => {
        if (installButton.classList.contains('d-none')) {
            manualInstall.style.display = 'block';
        }
    }, 1000);
});

// Enregistrer le Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then((registration) => {
                console.log('Service Worker enregistré:', registration.scope);
            })
            .catch((error) => {
                console.log('Erreur Service Worker:', error);
            });
    });
}
</script>
@endsection

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installer E-menu PWA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="manifest" href="/manifest.json">
</head>
<body>
    <div class="container-fluid p-0">
        <div class="pwa-install-hero" style="min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; padding: 2rem 0;">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-md-10 text-center">
                        <div class="pwa-install-content" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border-radius: 20px; padding: 3rem; border: 1px solid rgba(255, 255, 255, 0.2);">
                            <div class="app-icon mb-4">
                                <img src="{{ helper::image_path(helper::appdata($vendor_id ?? 1)->favicon ?? 'logo.png') }}"
                                     alt="E-menu App" style="width: 120px; height: 120px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);">
                            </div>

                            <h1 style="font-size: 3rem; font-weight: 300; margin-bottom: 1rem;">Installer E-menu</h1>
                            <p style="font-size: 1.2rem; opacity: 0.9; margin-bottom: 3rem; line-height: 1.6;">
                                Accédez rapidement à vos restaurants favoris directement depuis votre écran d'accueil.
                                Installation gratuite et rapide !
                            </p>

                            <div class="install-buttons">
                                <button id="installPWA" class="btn btn-primary btn-lg me-3 d-none">
                                    Installer maintenant
                                </button>

                                <div id="manualInstall" class="manual-install" style="background: rgba(255, 255, 255, 0.1); border-radius: 15px; padding: 2rem; margin: 2rem 0;">
                                    <h5>Installation manuelle :</h5>
                                    <div class="install-steps">
                                        <div class="step" id="desktopInstructions">
                                            <p><strong>Sur ordinateur :</strong></p>
                                            <ol style="text-align: left; max-width: 400px; margin: 0 auto;">
                                                <li>Cliquez sur l'icône d'installation dans la barre d'adresse</li>
                                                <li>Ou utilisez le menu du navigateur</li>
                                                <li>Sélectionnez "Installer E-menu"</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>

                                <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-lg">
                                    Retour au site
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
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

    // PWA Installation
    let deferredPrompt;
    let installButton = document.getElementById('installPWA');
    let manualInstall = document.getElementById('manualInstall');

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        installButton.classList.remove('d-none');
        manualInstall.style.display = 'none';
    });

    installButton.addEventListener('click', async () => {
        if (!deferredPrompt) return;

        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;

        if (outcome === 'accepted') {
            console.log('PWA installée avec succès');
            setTimeout(() => {
                window.location.href = '/';
            }, 1000);
        }

        deferredPrompt = null;
        installButton.classList.add('d-none');
    });
    </script>
</body>
</html>

{{-- Bouton d'installation PWA E-menu --}}
<div id="pwa-install-container" class="pwa-install-container" style="display: none;">
    <div class="pwa-install-banner">
        <div class="d-flex align-items-center">
            <div class="pwa-app-info me-3">
                <img src="{{ @helper::image_path(@helper::appdata($vdata)->favicon) }}"
                     alt="E-menu" class="pwa-app-icon">
                <div class="pwa-app-details">
                    <h6 class="mb-1">{{ @helper::appdata($vdata)->website_title ?? 'E-menu' }}</h6>
                    <small class="text-muted">Installer l'application</small>
                </div>
            </div>
            <div class="pwa-install-actions ms-auto">
                <button id="pwa-install-btn" class="btn btn-primary btn-sm me-2">
                    <i class="fas fa-download me-1"></i>
                    Installer
                </button>
                <button id="pwa-install-close" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Bouton de notifications PWA --}}
<div id="pwa-notification-container" class="pwa-notification-container" style="display: none;">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="fas fa-bell fa-2x text-primary"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">Restez informé</h6>
                    <small class="text-muted">Recevez des notifications pour vos commandes</small>
                </div>
                <div>
                    <button id="pwa-notify-btn" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-bell me-1"></i>
                        Activer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Badge PWA Status --}}
<div id="pwa-status-badge" class="pwa-status-badge position-fixed" style="bottom: 20px; left: 20px; z-index: 1000; display: none;">
    <div class="badge bg-success p-2 rounded-pill shadow">
        <i class="fas fa-mobile-alt me-1"></i>
        <span class="pwa-status-text">PWA Active</span>
    </div>
</div>

<style>
.pwa-install-container {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 9999;
    background: linear-gradient(135deg, var(--bs-primary, #007bff), var(--bs-secondary, #6c757d));
    color: white;
    animation: slideDown 0.3s ease-out;
}

.pwa-install-banner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 12px 20px;
}

.pwa-app-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    object-fit: cover;
}

.pwa-app-details h6 {
    color: white;
    font-weight: 600;
    margin: 0;
}

.pwa-app-details small {
    color: rgba(255, 255, 255, 0.8);
}

.pwa-notification-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    max-width: 350px;
    animation: slideUp 0.3s ease-out;
}

.pwa-status-badge {
    transition: all 0.3s ease;
}

.pwa-status-badge:hover {
    transform: scale(1.05);
}

@keyframes slideDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideUp {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Mode sombre */
@media (prefers-color-scheme: dark) {
    .pwa-notification-container .card {
        background-color: #2d3748;
        color: white;
    }

    .pwa-notification-container .text-muted {
        color: #a0aec0 !important;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .pwa-install-banner {
        padding: 10px 15px;
    }

    .pwa-app-icon {
        width: 40px;
        height: 40px;
    }

    .pwa-notification-container {
        right: 10px;
        left: 10px;
        max-width: none;
    }

    .pwa-status-badge {
        bottom: 15px;
        left: 15px;
    }
}

/* Animation pour les boutons */
#pwa-install-btn {
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    transition: all 0.3s ease;
}

#pwa-install-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-2px);
}

#pwa-install-close {
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
}

#pwa-install-close:hover {
    background: rgba(255, 0, 0, 0.3);
    border-color: rgba(255, 0, 0, 0.5);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du bouton de fermeture de l'installation
    const closeBtn = document.getElementById('pwa-install-close');
    const installContainer = document.getElementById('pwa-install-container');

    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            installContainer.style.display = 'none';
            // Mémoriser que l'utilisateur a fermé la bannière
            localStorage.setItem('pwa-install-dismissed', 'true');
        });
    }

    // Vérifier si la bannière a été fermée précédemment
    if (localStorage.getItem('pwa-install-dismissed') === 'true') {
        if (installContainer) {
            installContainer.style.display = 'none';
        }
    }

    // Afficher le statut PWA si l'app est installée
    if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) {
        const statusBadge = document.getElementById('pwa-status-badge');
        if (statusBadge) {
            statusBadge.style.display = 'block';
        }
    }

    // Extension du gestionnaire PWA existant
    if (window.pwaManager) {
        // Override des méthodes d'affichage
        const originalShowInstallButton = window.pwaManager.showInstallButton;
        const originalHideInstallButton = window.pwaManager.hideInstallButton;

        window.pwaManager.showInstallButton = function() {
            const container = document.getElementById('pwa-install-container');
            if (container && localStorage.getItem('pwa-install-dismissed') !== 'true') {
                container.style.display = 'block';
            }
        };

        window.pwaManager.hideInstallButton = function() {
            const container = document.getElementById('pwa-install-container');
            if (container) {
                container.style.display = 'none';
            }
        };

        // Afficher les notifications après l'installation
        window.addEventListener('appinstalled', function() {
            const notifyContainer = document.getElementById('pwa-notification-container');
            if (notifyContainer) {
                setTimeout(() => {
                    notifyContainer.style.display = 'block';
                }, 2000);
            }
        });
    }
});
</script>

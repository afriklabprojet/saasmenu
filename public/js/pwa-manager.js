/**
 * Gestionnaire PWA pour E-menu
 * Gère l'installation, les notifications et les fonctionnalités hors ligne
 */

class PWAManager {
    constructor() {
        this.deferredPrompt = null;
        this.isInstalled = false;
        this.serviceWorker = null;
        this.pushManager = null;

        this.init();
    }

    /**
     * Initialisation du gestionnaire PWA
     */
    async init() {
        console.log('🚀 Initialisation du gestionnaire PWA E-menu');

        // Vérifier le support PWA
        if (!this.isPWASupported()) {
            console.warn('PWA non supporté sur ce navigateur');
            return;
        }

        // Enregistrer le Service Worker
        await this.registerServiceWorker();

        // Configurer l'installation
        this.setupInstallPrompt();

        // Configurer les notifications
        this.setupNotifications();

        // Écouter les événements
        this.setupEventListeners();

        // Vérifier si déjà installé
        this.checkInstallStatus();

        console.log('✅ Gestionnaire PWA initialisé');
    }

    /**
     * Vérifier le support PWA
     */
    isPWASupported() {
        return 'serviceWorker' in navigator && 'PushManager' in window;
    }

    /**
     * Enregistrer le Service Worker
     */
    async registerServiceWorker() {
        try {
            const registration = await navigator.serviceWorker.register('/sw.js', {
                scope: '/'
            });

            this.serviceWorker = registration;
            console.log('✅ Service Worker enregistré:', registration.scope);

            // Écouter les mises à jour
            registration.addEventListener('updatefound', () => {
                console.log('🔄 Mise à jour du Service Worker détectée');
                this.handleServiceWorkerUpdate(registration);
            });

        } catch (error) {
            console.error('❌ Erreur enregistrement Service Worker:', error);
        }
    }

    /**
     * Configurer l'invite d'installation
     */
    setupInstallPrompt() {
        // Capturer l'événement beforeinstallprompt
        window.addEventListener('beforeinstallprompt', (event) => {
            console.log('📱 PWA installable détectée');
            event.preventDefault();
            this.deferredPrompt = event;
            this.showInstallButton();
        });

        // Détecter l'installation
        window.addEventListener('appinstalled', (event) => {
            console.log('✅ PWA installée');
            this.isInstalled = true;
            this.hideInstallButton();
            this.trackInstall();
        });
    }

    /**
     * Configurer les notifications push
     */
    async setupNotifications() {
        if (!('Notification' in window) || !('PushManager' in window)) {
            console.warn('Notifications non supportées');
            return;
        }

        try {
            const registration = await navigator.serviceWorker.ready;
            this.pushManager = registration.pushManager;

            // Vérifier l'état des notifications
            const permission = await Notification.requestPermission();
            console.log('🔔 Permission notifications:', permission);

        } catch (error) {
            console.error('❌ Erreur configuration notifications:', error);
        }
    }

    /**
     * Configurer les écouteurs d'événements
     */
    setupEventListeners() {
        // Bouton d'installation
        const installBtn = document.getElementById('pwa-install-btn');
        if (installBtn) {
            installBtn.addEventListener('click', () => this.promptInstall());
        }

        // Bouton de notifications
        const notifyBtn = document.getElementById('pwa-notify-btn');
        if (notifyBtn) {
            notifyBtn.addEventListener('click', () => this.enableNotifications());
        }

        // Statut de connexion
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());

        // Visibilité de l'application
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && this.serviceWorker) {
                this.checkForUpdates();
            }
        });
    }

    /**
     * Vérifier l'état d'installation
     */
    checkInstallStatus() {
        // Vérifier si l'application est en mode standalone
        if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) {
            this.isInstalled = true;
            console.log('✅ Application en mode standalone');
        }

        // Pour iOS Safari
        if (window.navigator.standalone === true) {
            this.isInstalled = true;
            console.log('✅ Application installée sur iOS');
        }

        if (this.isInstalled) {
            this.hideInstallButton();
        }
    }

    /**
     * Déclencher l'installation
     */
    async promptInstall() {
        if (!this.deferredPrompt) {
            console.warn('Installation PWA non disponible');
            return false;
        }

        try {
            // Afficher l'invite d'installation
            this.deferredPrompt.prompt();

            // Attendre la réponse de l'utilisateur
            const result = await this.deferredPrompt.userChoice;
            console.log('🎯 Réponse installation:', result.outcome);

            if (result.outcome === 'accepted') {
                console.log('✅ Installation acceptée');
                this.trackInstallAttempt(true);
            } else {
                console.log('❌ Installation refusée');
                this.trackInstallAttempt(false);
            }

            this.deferredPrompt = null;
            return result.outcome === 'accepted';

        } catch (error) {
            console.error('❌ Erreur installation:', error);
            return false;
        }
    }

    /**
     * Activer les notifications push
     */
    async enableNotifications() {
        try {
            // Demander la permission
            const permission = await Notification.requestPermission();

            if (permission !== 'granted') {
                console.warn('Permission notifications refusée');
                this.showNotificationError('Permission refusée');
                return false;
            }

            // S'abonner aux notifications push
            const subscription = await this.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(this.getVapidPublicKey())
            });

            // Envoyer l'abonnement au serveur
            const response = await fetch('/pwa/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(subscription.toJSON())
            });

            if (response.ok) {
                console.log('✅ Notifications activées');
                this.showNotificationSuccess('Notifications activées avec succès');
                this.updateNotificationButton(true);
                return true;
            } else {
                throw new Error('Erreur serveur lors de l\'abonnement');
            }

        } catch (error) {
            console.error('❌ Erreur activation notifications:', error);
            this.showNotificationError('Erreur lors de l\'activation');
            return false;
        }
    }

    /**
     * Désactiver les notifications
     */
    async disableNotifications() {
        try {
            const subscription = await this.pushManager.getSubscription();

            if (subscription) {
                await subscription.unsubscribe();

                // Informer le serveur
                await fetch('/pwa/unsubscribe', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });
            }

            console.log('✅ Notifications désactivées');
            this.showNotificationSuccess('Notifications désactivées');
            this.updateNotificationButton(false);

        } catch (error) {
            console.error('❌ Erreur désactivation notifications:', error);
        }
    }

    /**
     * Gérer les mises à jour du Service Worker
     */
    handleServiceWorkerUpdate(registration) {
        const newWorker = registration.installing;

        if (newWorker) {
            newWorker.addEventListener('statechange', () => {
                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                    // Nouvelle version disponible
                    this.showUpdatePrompt();
                }
            });
        }
    }

    /**
     * Gérer le retour en ligne
     */
    handleOnline() {
        console.log('🌐 Connexion rétablie');
        this.hideOfflineIndicator();

        // Synchroniser les données en attente
        if (this.serviceWorker && this.serviceWorker.sync) {
            this.serviceWorker.sync.register('background-sync');
        }
    }

    /**
     * Gérer le mode hors ligne
     */
    handleOffline() {
        console.log('📵 Mode hors ligne activé');
        this.showOfflineIndicator();
    }

    /**
     * Vérifier les mises à jour
     */
    async checkForUpdates() {
        try {
            const registration = await navigator.serviceWorker.getRegistration();
            if (registration) {
                await registration.update();
            }
        } catch (error) {
            console.error('❌ Erreur vérification mises à jour:', error);
        }
    }

    /**
     * Interface utilisateur - Bouton d'installation
     */
    showInstallButton() {
        const btn = document.getElementById('pwa-install-btn');
        if (btn) {
            btn.style.display = 'block';
            btn.classList.add('animate__fadeIn');
        }
    }

    hideInstallButton() {
        const btn = document.getElementById('pwa-install-btn');
        if (btn) {
            btn.style.display = 'none';
        }
    }

    /**
     * Interface utilisateur - Indicateur hors ligne
     */
    showOfflineIndicator() {
        let indicator = document.getElementById('offline-indicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'offline-indicator';
            indicator.className = 'alert alert-warning fixed-top text-center';
            indicator.innerHTML = '📵 Mode hors ligne - Certaines fonctionnalités peuvent être limitées';
            document.body.appendChild(indicator);
        }
        indicator.style.display = 'block';
    }

    hideOfflineIndicator() {
        const indicator = document.getElementById('offline-indicator');
        if (indicator) {
            indicator.style.display = 'none';
        }
    }

    /**
     * Interface utilisateur - Notifications
     */
    showNotificationSuccess(message) {
        this.showToast(message, 'success');
    }

    showNotificationError(message) {
        this.showToast(message, 'error');
    }

    showToast(message, type = 'info') {
        // Utiliser le système de toast existant ou créer un simple
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} toast-notification`;
        toast.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            <strong>${type === 'success' ? '✅' : '❌'}</strong> ${message}
            <button type="button" class="btn-close float-end" onclick="this.parentElement.remove()"></button>
        `;
        document.body.appendChild(toast);

        // Auto-suppression après 5 secondes
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 5000);
    }

    /**
     * Mettre à jour le bouton de notifications
     */
    updateNotificationButton(enabled) {
        const btn = document.getElementById('pwa-notify-btn');
        if (btn) {
            btn.textContent = enabled ? '🔔 Notifications activées' : '🔕 Activer les notifications';
            btn.onclick = enabled ? () => this.disableNotifications() : () => this.enableNotifications();
        }
    }

    /**
     * Afficher l'invite de mise à jour
     */
    showUpdatePrompt() {
        const updatePrompt = document.createElement('div');
        updatePrompt.className = 'alert alert-info fixed-top text-center';
        updatePrompt.innerHTML = `
            <strong>🔄 Mise à jour disponible</strong>
            <button class="btn btn-primary btn-sm ms-2" onclick="window.pwaManager.applyUpdate()">
                Mettre à jour
            </button>
            <button class="btn btn-secondary btn-sm ms-1" onclick="this.parentElement.remove()">
                Plus tard
            </button>
        `;
        document.body.appendChild(updatePrompt);
    }

    /**
     * Appliquer la mise à jour
     */
    async applyUpdate() {
        if (this.serviceWorker && this.serviceWorker.waiting) {
            this.serviceWorker.waiting.postMessage({ type: 'SKIP_WAITING' });
            window.location.reload();
        }
    }

    /**
     * Utilitaires
     */
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    getVapidPublicKey() {
        // Remplacer par votre clé VAPID publique
        return 'YOUR_VAPID_PUBLIC_KEY_HERE';
    }

    /**
     * Analytics
     */
    trackInstall() {
        // Implémenter le suivi d'installation
        console.log('📊 PWA installée - événement suivi');
    }

    trackInstallAttempt(accepted) {
        // Implémenter le suivi des tentatives d'installation
        console.log('📊 Tentative installation:', accepted ? 'acceptée' : 'refusée');
    }
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', () => {
    window.pwaManager = new PWAManager();
});

// Export pour usage externe
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PWAManager;
}

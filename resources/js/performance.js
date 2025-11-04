/**
 * RestroSaaS Performance Optimizations
 * Client-side performance enhancements and PWA utilities
 */

class RestroPerformance {
    constructor() {
        this.init();
        this.setupPWA();
        this.initLazyLoading();
        this.setupPerformanceMonitoring();
    }

    /**
     * Initialize performance optimizations
     */
    init() {
        // Critical rendering path optimization
        this.optimizeCriticalPath();

        // Intersection Observer for lazy loading
        this.setupIntersectionObservers();

        // Preload critical resources
        this.preloadCriticalResources();

        // Setup service worker
        this.registerServiceWorker();
    }

    /**
     * Optimize critical rendering path
     */
    optimizeCriticalPath() {
        // Load non-critical CSS asynchronously
        const loadCSS = (href) => {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            link.media = 'print';
            link.onload = function() {
                this.media = 'all';
            };
            document.head.appendChild(link);
        };

        // Load non-critical stylesheets
        const nonCriticalCSS = [
            '/admin-assets/css/dashboard.css',
            '/admin-assets/css/charts.css'
        ];

        nonCriticalCSS.forEach(css => {
            if (window.location.pathname.includes(css.split('/').pop().split('.')[0])) {
                loadCSS(css);
            }
        });
    }

    /**
     * Setup Intersection Observers for performance
     */
    setupIntersectionObservers() {
        // Lazy load images
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.01
        });

        // Observe lazy images
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });

        // Lazy load components
        const componentObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadComponent(entry.target);
                    componentObserver.unobserve(entry.target);
                }
            });
        });

        document.querySelectorAll('[data-lazy-component]').forEach(el => {
            componentObserver.observe(el);
        });
    }

    /**
     * Load component dynamically
     */
    async loadComponent(element) {
        const componentName = element.dataset.lazyComponent;

        try {
            const module = await import(`./components/${componentName}.js`);
            if (module.default) {
                new module.default(element);
            }
        } catch (error) {
            console.warn(`Failed to load component: ${componentName}`, error);
        }
    }

    /**
     * Preload critical resources
     */
    preloadCriticalResources() {
        const criticalResources = [
            { href: '/admin-assets/js/jquery.min.js', as: 'script' },
            { href: '/admin-assets/css/bootstrap.min.css', as: 'style' },
            { href: '/admin-assets/fonts/primary-font.woff2', as: 'font', type: 'font/woff2', crossorigin: 'anonymous' }
        ];

        criticalResources.forEach(resource => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.href = resource.href;
            link.as = resource.as;
            if (resource.type) link.type = resource.type;
            if (resource.crossorigin) link.crossOrigin = resource.crossorigin;
            document.head.appendChild(link);
        });
    }

    /**
     * Register service worker
     */
    async registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('/sw-advanced.js');
                console.log('SW registered:', registration);

                // Listen for updates
                registration.addEventListener('updatefound', () => {
                    this.showUpdateNotification();
                });
            } catch (error) {
                console.error('SW registration failed:', error);
            }
        }
    }

    /**
     * Setup PWA features
     */
    setupPWA() {
        // Install prompt
        let deferredPrompt;

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            this.showInstallButton();
        });

        // Handle install button click
        document.addEventListener('click', async (e) => {
            if (e.target.matches('[data-pwa-install]')) {
                e.preventDefault();

                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    console.log(`User ${outcome} the install prompt`);
                    deferredPrompt = null;
                }
            }
        });

        // PWA lifecycle events
        window.addEventListener('appinstalled', () => {
            console.log('PWA was installed');
            this.hideInstallButton();
        });
    }

    /**
     * Show PWA install button
     */
    showInstallButton() {
        const installButton = document.querySelector('[data-pwa-install]');
        if (installButton) {
            installButton.style.display = 'block';
        }
    }

    /**
     * Hide PWA install button
     */
    hideInstallButton() {
        const installButton = document.querySelector('[data-pwa-install]');
        if (installButton) {
            installButton.style.display = 'none';
        }
    }

    /**
     * Show update notification
     */
    showUpdateNotification() {
        const notification = document.createElement('div');
        notification.className = 'update-notification';
        notification.innerHTML = `
            <div class="update-content">
                <span>Nouvelle version disponible!</span>
                <button onclick="window.location.reload()">Mettre à jour</button>
            </div>
        `;
        document.body.appendChild(notification);

        // Auto-hide after 10 seconds
        setTimeout(() => {
            notification.remove();
        }, 10000);
    }

    /**
     * Initialize lazy loading
     */
    initLazyLoading() {
        // Lazy load non-critical JavaScript
        const lazyLoadScript = (src, callback) => {
            const script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.onload = callback;
            document.body.appendChild(script);
        };

        // Load scripts based on page type
        const currentPage = this.getCurrentPageType();

        switch (currentPage) {
            case 'dashboard':
                this.loadDashboardAssets();
                break;
            case 'orders':
                this.loadOrdersAssets();
                break;
            case 'products':
                this.loadProductsAssets();
                break;
        }
    }

    /**
     * Get current page type
     */
    getCurrentPageType() {
        const path = window.location.pathname;

        if (path.includes('/dashboard')) return 'dashboard';
        if (path.includes('/orders')) return 'orders';
        if (path.includes('/products')) return 'products';
        if (path.includes('/analytics')) return 'analytics';

        return 'default';
    }

    /**
     * Load dashboard-specific assets
     */
    loadDashboardAssets() {
        // Chart.js for dashboard
        if (document.querySelector('[data-chart]')) {
            import('./charts.js').then(module => {
                module.initCharts();
            });
        }
    }

    /**
     * Load orders-specific assets
     */
    loadOrdersAssets() {
        // DataTables for orders list
        if (document.querySelector('.orders-table')) {
            import('./datatables.js').then(module => {
                module.initDataTables();
            });
        }
    }

    /**
     * Load products-specific assets
     */
    loadProductsAssets() {
        // Image gallery for products
        if (document.querySelector('.product-gallery')) {
            import('./gallery.js').then(module => {
                module.initGallery();
            });
        }
    }

    /**
     * Setup performance monitoring
     */
    setupPerformanceMonitoring() {
        // Web Vitals measurement
        this.measureWebVitals();

        // Long task monitoring
        this.monitorLongTasks();

        // Memory usage monitoring
        this.monitorMemoryUsage();
    }

    /**
     * Measure Web Vitals
     */
    measureWebVitals() {
        if ('PerformanceObserver' in window) {
            // LCP - Largest Contentful Paint
            const lcpObserver = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                const lastEntry = entries[entries.length - 1];
                this.reportMetric('LCP', lastEntry.startTime);
            });
            lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });

            // FID - First Input Delay
            const fidObserver = new PerformanceObserver((list) => {
                list.getEntries().forEach((entry) => {
                    const fid = entry.processingStart - entry.startTime;
                    this.reportMetric('FID', fid);
                });
            });
            fidObserver.observe({ entryTypes: ['first-input'] });

            // CLS - Cumulative Layout Shift
            let clsValue = 0;
            const clsObserver = new PerformanceObserver((list) => {
                list.getEntries().forEach((entry) => {
                    if (!entry.hadRecentInput) {
                        clsValue += entry.value;
                    }
                });
                this.reportMetric('CLS', clsValue);
            });
            clsObserver.observe({ entryTypes: ['layout-shift'] });
        }
    }

    /**
     * Monitor long tasks
     */
    monitorLongTasks() {
        if ('PerformanceObserver' in window) {
            const observer = new PerformanceObserver((list) => {
                list.getEntries().forEach((entry) => {
                    if (entry.duration > 50) {
                        console.warn('Long task detected:', entry.duration + 'ms');
                        this.reportMetric('LongTask', entry.duration);
                    }
                });
            });
            observer.observe({ entryTypes: ['longtask'] });
        }
    }

    /**
     * Monitor memory usage
     */
    monitorMemoryUsage() {
        if ('memory' in performance) {
            setInterval(() => {
                const memory = performance.memory;
                if (memory.usedJSHeapSize > memory.jsHeapSizeLimit * 0.9) {
                    console.warn('High memory usage detected');
                    this.reportMetric('MemoryUsage', memory.usedJSHeapSize);
                }
            }, 30000); // Check every 30 seconds
        }
    }

    /**
     * Report performance metric
     */
    reportMetric(name, value) {
        // Send to analytics endpoint
        if (navigator.sendBeacon) {
            const data = JSON.stringify({
                metric: name,
                value: value,
                url: window.location.href,
                userAgent: navigator.userAgent,
                timestamp: Date.now()
            });

            navigator.sendBeacon('/api/analytics/performance', data);
        }
    }

    /**
     * Optimize font loading
     */
    optimizeFontLoading() {
        // Preload critical fonts
        const fonts = [
            '/admin-assets/fonts/primary-bold.woff2',
            '/admin-assets/fonts/primary-regular.woff2'
        ];

        fonts.forEach(font => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.href = font;
            link.as = 'font';
            link.type = 'font/woff2';
            link.crossOrigin = 'anonymous';
            document.head.appendChild(link);
        });

        // Font display swap CSS
        const style = document.createElement('style');
        style.textContent = `
            @font-face {
                font-family: 'Primary';
                font-display: swap;
                src: url('/admin-assets/fonts/primary-regular.woff2') format('woff2');
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Cache management
     */
    manageCaches() {
        // Clean old caches
        if ('caches' in window) {
            caches.keys().then(names => {
                names.forEach(name => {
                    if (name.includes('old-version')) {
                        caches.delete(name);
                    }
                });
            });
        }
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new RestroPerformance();
    });
} else {
    new RestroPerformance();
}

// Export for manual initialization
window.RestroPerformance = RestroPerformance;

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/performance.js'
            ],
            refresh: true,
        }),
        VitePWA({
            registerType: 'autoUpdate',
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,webp}'],
                runtimeCaching: [
                    {
                        urlPattern: /^https:\/\/fonts\.googleapis\.com\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'google-fonts-cache',
                            expiration: {
                                maxEntries: 10,
                                maxAgeSeconds: 60 * 60 * 24 * 365 // <== 365 days
                            },
                            cacheKeyWillBeUsed: async ({ request }) => {
                                return `${request.url}?version=1`;
                            }
                        }
                    },
                    {
                        urlPattern: /^https:\/\/fonts\.gstatic\.com\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'gstatic-fonts-cache',
                            expiration: {
                                maxEntries: 10,
                                maxAgeSeconds: 60 * 60 * 24 * 365 // <== 365 days
                            }
                        }
                    },
                    {
                        urlPattern: /\/api\/dashboard/i,
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'api-cache',
                            expiration: {
                                maxEntries: 50,
                                maxAgeSeconds: 5 * 60 // 5 minutes
                            }
                        }
                    },
                    {
                        urlPattern: /\.(?:png|jpg|jpeg|svg|gif|webp)$/,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'images-cache',
                            expiration: {
                                maxEntries: 100,
                                maxAgeSeconds: 60 * 60 * 24 * 30 // 30 days
                            }
                        }
                    }
                ]
            },
            manifest: {
                name: 'RestroSaaS - Gestion Restaurant',
                short_name: 'RestroSaaS',
                description: 'Plateforme SaaS complète pour la gestion multi-restaurants',
                theme_color: '#6777ef',
                background_color: '#6777ef',
                display: 'standalone',
                orientation: 'portrait-primary',
                scope: '/',
                start_url: '/',
                icons: [
                    {
                        src: '/admin-assets/images/icons/icon-192x192.png',
                        sizes: '192x192',
                        type: 'image/png'
                    },
                    {
                        src: '/admin-assets/images/icons/icon-512x512.png',
                        sizes: '512x512',
                        type: 'image/png'
                    }
                ]
            }
        })
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['jquery', 'bootstrap'],
                    charts: ['chart.js'],
                    utils: ['lodash', 'moment']
                }
            }
        },
        cssCodeSplit: true,
        sourcemap: false,
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,
                drop_debugger: true
            }
        },
        chunkSizeWarningLimit: 1000
    },
    server: {
        hmr: {
            overlay: false
        },
        watch: {
            usePolling: false,
            interval: 1000
        }
    },
    optimizeDeps: {
        include: ['jquery', 'bootstrap', 'chart.js']
    },
    define: {
        __APP_VERSION__: JSON.stringify(process.env.npm_package_version)
    }
});

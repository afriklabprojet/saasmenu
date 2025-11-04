<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixLanguagesTable extends Command
{
    protected $signature = 'fix:languages';
        protected $description = 'Crée et répare les tables manquantes : languages, systemaddons, pricing_plans, features, testimonials, social_links, store_category, city, promotionalbanner, about + corrections blogs, users, settings';

    public function handle()
    {
        $this->createLanguagesTable();
        $this->createSystemAddonsTable();
        $this->createPricingPlansTable();
        $this->createFeaturesTable();
        $this->createTestimonialsTable();
        $this->createSocialLinksTable();
        $this->createStoreCategoryTable();
        $this->createCityTable();
        $this->createPromotionalBannerTable();
        $this->createAboutTable();
        $this->fixBlogsTable();
        $this->fixUsersTable();
        $this->fixSettingsTable();

        return 0;
    }

    private function createLanguagesTable()
    {
        // Créer la table languages si elle n'existe pas
        if (!Schema::hasTable('languages')) {
            $this->info('Création de la table languages...');

            Schema::create('languages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 5);
                $table->string('layout', 10)->default('ltr');
                $table->string('image')->nullable();
                $table->enum('is_default', [1, 2])->default(2);
                $table->enum('is_available', [1, 2])->default(1);
                $table->enum('is_deleted', [1, 2])->default(2);
                $table->timestamps();
                $table->index('code');
            });

            $this->info('Table languages créée avec succès.');

            // Insérer les langues par défaut
            DB::table('languages')->insert([
                [
                    'name' => 'Français',
                    'code' => 'fr',
                    'layout' => 'ltr',
                    'is_default' => 1,
                    'is_available' => 1,
                    'is_deleted' => 2,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'name' => 'English',
                    'code' => 'en',
                    'layout' => 'ltr',
                    'is_default' => 2,
                    'is_available' => 1,
                    'is_deleted' => 2,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);

            $this->info('Langues par défaut insérées.');

        } else {
            $this->info('Table languages existe déjà.');
        }

        // Vérifier que la table contient des données
        $count = DB::table('languages')->count();
        $this->info("Nombre de langues dans la table: {$count}");
    }

    private function createSystemAddonsTable()
    {
        // Créer la table systemaddons si elle n'existe pas
        if (!Schema::hasTable('systemaddons')) {
            $this->info('Création de la table systemaddons...');

            Schema::create('systemaddons', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('unique_identifier');
                $table->string('version', 20);
                $table->integer('activated');
                $table->string('image');
                $table->integer('type')->nullable();
                $table->timestamps();
                $table->index('unique_identifier');
            });

            $this->info('Table systemaddons créée avec succès.');

            // Insérer les addons par défaut
            DB::table('systemaddons')->insert([
                [
                    'name' => 'Google Login',
                    'unique_identifier' => 'google_login',
                    'version' => '1.0.0',
                    'activated' => 1,
                    'image' => 'google-login.png',
                    'type' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'name' => 'Facebook Login',
                    'unique_identifier' => 'facebook_login',
                    'version' => '1.0.0',
                    'activated' => 1,
                    'image' => 'facebook-login.png',
                    'type' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'name' => 'Multi Language',
                    'unique_identifier' => 'multi_language',
                    'version' => '1.0.0',
                    'activated' => 1,
                    'image' => 'multi-language.png',
                    'type' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'name' => 'Restaurant QR Menu',
                    'unique_identifier' => 'restaurant_qr_menu',
                    'version' => '1.0.0',
                    'activated' => 1,
                    'image' => 'qr-menu.png',
                    'type' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'name' => 'Google reCAPTCHA',
                    'unique_identifier' => 'google_recaptcha',
                    'version' => '1.0.0',
                    'activated' => 1,
                    'image' => 'google-recaptcha.png',
                    'type' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'name' => 'Subscription System',
                    'unique_identifier' => 'subscription',
                    'version' => '1.0.0',
                    'activated' => 1,
                    'image' => 'subscription.png',
                    'type' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);

            $this->info('Addons par défaut insérés.');

        } else {
            $this->info('Table systemaddons existe déjà.');

            // Vérifier et ajouter les addons manquants
            $requiredAddons = [
                'google_login' => 'Google Login',
                'facebook_login' => 'Facebook Login',
                'multi_language' => 'Multi Language',
                'restaurant_qr_menu' => 'Restaurant QR Menu',
                'google_recaptcha' => 'Google reCAPTCHA',
                'subscription' => 'Subscription System'
            ];

            foreach ($requiredAddons as $identifier => $name) {
                $exists = DB::table('systemaddons')
                    ->where('unique_identifier', $identifier)
                    ->exists();

                if (!$exists) {
                    DB::table('systemaddons')->insert([
                        'name' => $name,
                        'unique_identifier' => $identifier,
                        'version' => '1.0.0',
                        'activated' => 1,
                        'image' => $identifier . '.png',
                        'type' => 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $this->info("Addon manquant ajouté: {$name} ({$identifier})");
                }
            }
        }

        // Vérifier que la table contient des données
        $count = DB::table('systemaddons')->count();
        $this->info("Nombre d'addons dans la table: {$count}");

        // Afficher les addons
        $addons = DB::table('systemaddons')->select('name', 'unique_identifier', 'activated')->get();
        foreach ($addons as $addon) {
            $status = $addon->activated ? ' (activé)' : ' (désactivé)';
            $this->line("- {$addon->name} ({$addon->unique_identifier}){$status}");
        }
    }

    private function createPricingPlansTable()
    {
        // Créer la table pricing_plans si elle n'existe pas
        if (!Schema::hasTable('pricing_plans')) {
            $this->info('Création de la table pricing_plans...');

            Schema::create('pricing_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->text('features')->nullable();
                $table->decimal('price', 10, 2)->default(0);
                $table->integer('duration')->default(30);
                $table->integer('service_limit')->default(-1);
                $table->integer('appoinment_limit')->default(-1);
                $table->enum('type', ['monthly', 'yearly', 'lifetime'])->default('monthly');
                $table->boolean('is_available')->default(1);
                $table->timestamps();
            });

            $this->info('Table pricing_plans créée avec succès.');

            // Insérer les plans par défaut
            DB::table('pricing_plans')->insert([
                [
                    'name' => 'Plan Gratuit',
                    'description' => 'Plan de base gratuit pour tester la plateforme',
                    'features' => 'Accès de base|Support email|5 services',
                    'price' => 0.00,
                    'duration' => 30,
                    'service_limit' => 5,
                    'appoinment_limit' => 50,
                    'type' => 'monthly',
                    'is_available' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'name' => 'Plan Starter',
                    'description' => 'Plan idéal pour les petites entreprises',
                    'features' => 'Tout du gratuit|20 services|Support prioritaire|Analytiques de base',
                    'price' => 19.99,
                    'duration' => 30,
                    'service_limit' => 20,
                    'appoinment_limit' => 200,
                    'type' => 'monthly',
                    'is_available' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'name' => 'Plan Business',
                    'description' => 'Plan professionnel pour entreprises en croissance',
                    'features' => 'Tout du Starter|Services illimités|Support 24/7|Analytiques avancées|Multi-utilisateurs',
                    'price' => 49.99,
                    'duration' => 30,
                    'service_limit' => -1,
                    'appoinment_limit' => -1,
                    'type' => 'monthly',
                    'is_available' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);

            $this->info('Plans de tarification par défaut insérés.');

        } else {
            $this->info('Table pricing_plans existe déjà.');

            // Vérifier qu'il y a au moins un plan disponible
            $availablePlans = DB::table('pricing_plans')->where('is_available', 1)->count();
            if ($availablePlans == 0) {
                $this->info('Aucun plan disponible trouvé, ajout du plan gratuit...');
                DB::table('pricing_plans')->insert([
                    'name' => 'Plan par Défaut',
                    'description' => 'Plan de base automatiquement créé',
                    'features' => 'Accès de base',
                    'price' => 0.00,
                    'duration' => 30,
                    'service_limit' => -1,
                    'appoinment_limit' => -1,
                    'type' => 'monthly',
                    'is_available' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $this->info('Plan par défaut ajouté.');
            }
        }

        // Afficher les plans disponibles
        $count = DB::table('pricing_plans')->where('is_available', 1)->count();
        $this->info("Nombre de plans disponibles: {$count}");

        $plans = DB::table('pricing_plans')->select('name', 'price', 'type', 'is_available')->get();
        foreach ($plans as $plan) {
            $status = $plan->is_available ? ' (disponible)' : ' (indisponible)';
            $this->line("- {$plan->name} - {$plan->price}€/{$plan->type}{$status}");
        }
    }

    private function createFeaturesTable()
    {
        $this->info("🔧 Vérification de la table features...");

        if (Schema::hasTable('features')) {
            $this->info("✅ Table features existe déjà");
            return;
        }

        $this->info("📋 Création de la table features...");

        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->integer('reorder_id');
            $table->integer('vendor_id');
            $table->string('title');
            $table->text('description');
            $table->string('image');
            $table->timestamps();
        });

        $this->info("✅ Table features créée avec succès");

        // Ajouter des données par défaut pour le vendor_id = 1
        $defaultFeatures = [
            [
                'reorder_id' => 1,
                'vendor_id' => 1,
                'title' => 'Commande en ligne',
                'description' => 'Permettez à vos clients de passer commande directement en ligne depuis votre site web.',
                'image' => 'default_online_order.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reorder_id' => 2,
                'vendor_id' => 1,
                'title' => 'Menu QR Code',
                'description' => 'Générez des QR codes pour permettre aux clients de voir votre menu directement sur leur téléphone.',
                'image' => 'default_qr_menu.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reorder_id' => 3,
                'vendor_id' => 1,
                'title' => 'Livraison à domicile',
                'description' => 'Organisez vos livraisons avec un système de gestion intégré et suivi des commandes.',
                'image' => 'default_delivery.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reorder_id' => 4,
                'vendor_id' => 1,
                'title' => 'Emporter',
                'description' => 'Gérez les commandes à emporter avec un système de notification efficace.',
                'image' => 'default_takeaway.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($defaultFeatures as $feature) {
            DB::table('features')->insert($feature);
        }

        $this->info("✅ Table features peuplée avec 4 fonctionnalités par défaut");

        $count = DB::table('features')->where('vendor_id', 1)->count();
        $this->info("Nombre de fonctionnalités ajoutées: {$count}");

        $features = DB::table('features')->where('vendor_id', 1)->orderBy('reorder_id')->get();
        foreach ($features as $feature) {
            $this->line("- {$feature->title} (ordre: {$feature->reorder_id})");
        }
    }

    private function createTestimonialsTable()
    {
        $this->info("🔧 Vérification de la table testimonials...");

        if (Schema::hasTable('testimonials')) {
            $this->info("✅ Table testimonials existe déjà");
            return;
        }

        $this->info("📋 Création de la table testimonials...");

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->integer('reorder_id')->nullable();
            $table->integer('vendor_id');
            $table->integer('star');
            $table->longText('description');
            $table->string('name');
            $table->string('image');
            $table->string('position');
            $table->timestamps();
        });

        $this->info("✅ Table testimonials créée avec succès");

        // Ajouter des témoignages par défaut pour le vendor_id = 1
        $defaultTestimonials = [
            [
                'reorder_id' => 1,
                'vendor_id' => 1,
                'star' => 5,
                'description' => 'Service excellent ! La nourriture était délicieuse et la livraison très rapide. Je recommande vivement ce restaurant.',
                'name' => 'Marie Dubois',
                'image' => 'default_customer1.png',
                'position' => 'Cliente régulière',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reorder_id' => 2,
                'vendor_id' => 1,
                'star' => 5,
                'description' => 'Une expérience culinaire fantastique ! Les plats sont authentiques et le service client est irréprochable.',
                'name' => 'Jean Martin',
                'image' => 'default_customer2.png',
                'position' => 'Gastronome',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reorder_id' => 3,
                'vendor_id' => 1,
                'star' => 4,
                'description' => 'Très bonne qualité des produits. Le système de commande en ligne est simple et efficace.',
                'name' => 'Sophie Bernard',
                'image' => 'default_customer3.png',
                'position' => 'Utilisatrice mobile',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reorder_id' => 4,
                'vendor_id' => 1,
                'star' => 5,
                'description' => 'Restaurant de qualité avec un service impeccable. Les menus sont variés et les prix très raisonnables.',
                'name' => 'Pierre Leroy',
                'image' => 'default_customer4.png',
                'position' => 'Chef cuisinier',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($defaultTestimonials as $testimonial) {
            DB::table('testimonials')->insert($testimonial);
        }

        $this->info("✅ Table testimonials peuplée avec 4 témoignages par défaut");

        $count = DB::table('testimonials')->where('vendor_id', 1)->count();
        $this->info("Nombre de témoignages ajoutés: {$count}");

        $testimonials = DB::table('testimonials')->where('vendor_id', 1)->orderBy('reorder_id')->get();
        foreach ($testimonials as $testimonial) {
            $stars = str_repeat('⭐', $testimonial->star);
            $this->line("- {$testimonial->name} ({$testimonial->position}) {$stars}");
        }
    }

    private function createSocialLinksTable()
    {
        $this->info("🔧 Vérification de la table social_links...");

        if (Schema::hasTable('social_links')) {
            $this->info("✅ Table social_links existe déjà");
            return;
        }

        $this->info("📋 Création de la table social_links...");

        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id');
            $table->text('icon');
            $table->text('link');
            $table->timestamps();
        });

        $this->info("✅ Table social_links créée avec succès");

        // Ajouter des liens sociaux par défaut pour le vendor_id = 1
        $defaultSocialLinks = [
            [
                'vendor_id' => 1,
                'icon' => 'fab fa-facebook-f',
                'link' => 'https://facebook.com/RestroSaaS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => 1,
                'icon' => 'fab fa-twitter',
                'link' => 'https://twitter.com/RestroSaaS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => 1,
                'icon' => 'fab fa-instagram',
                'link' => 'https://instagram.com/RestroSaaS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => 1,
                'icon' => 'fab fa-linkedin-in',
                'link' => 'https://linkedin.com/company/RestroSaaS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($defaultSocialLinks as $socialLink) {
            DB::table('social_links')->insert($socialLink);
        }

        $this->info("✅ Table social_links peuplée avec 4 liens sociaux par défaut");

        $count = DB::table('social_links')->where('vendor_id', 1)->count();
        $this->info("Nombre de liens sociaux ajoutés: {$count}");

        $socialLinks = DB::table('social_links')->where('vendor_id', 1)->get();
        foreach ($socialLinks as $socialLink) {
            $this->line("- {$socialLink->icon} -> {$socialLink->link}");
        }
    }

    private function createStoreCategoryTable()
    {
        $this->info("🔧 Vérification de la table store_category...");

        if (Schema::hasTable('store_category')) {
            $this->info("✅ Table store_category existe déjà");
            return;
        }

        $this->info("📋 Création de la table store_category...");

        Schema::create('store_category', function (Blueprint $table) {
            $table->id();
            $table->integer('reorder_id');
            $table->string('name');
            $table->integer('is_available')->default(1)->comment('1=Yes,2=No');
            $table->integer('is_deleted')->default(2)->comment('1=Yes,2=No');
            $table->timestamps();
        });

        $this->info("✅ Table store_category créée avec succès");

        // Ajouter des catégories de magasin par défaut
        $defaultStoreCategories = [
            [
                'reorder_id' => 1,
                'name' => 'Restaurant Traditionnel',
                'is_available' => 1,
                'is_deleted' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reorder_id' => 2,
                'name' => 'Fast Food',
                'is_available' => 1,
                'is_deleted' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reorder_id' => 3,
                'name' => 'Café & Bakery',
                'is_available' => 1,
                'is_deleted' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reorder_id' => 4,
                'name' => 'Food Truck',
                'is_available' => 1,
                'is_deleted' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reorder_id' => 5,
                'name' => 'Traiteur',
                'is_available' => 1,
                'is_deleted' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($defaultStoreCategories as $category) {
            DB::table('store_category')->insert($category);
        }

        $this->info("✅ Table store_category peuplée avec 5 catégories par défaut");

        $count = DB::table('store_category')->where('is_available', 1)->where('is_deleted', 2)->count();
        $this->info("Nombre de catégories de magasin ajoutées: {$count}");

        $storeCategories = DB::table('store_category')->where('is_available', 1)->where('is_deleted', 2)->orderBy('reorder_id')->get();
        foreach ($storeCategories as $category) {
            $this->line("- {$category->name} (ordre: {$category->reorder_id})");
        }
    }

    private function createCityTable()
    {
        $this->info("🔧 Vérification de la table city...");

        if (Schema::hasTable('city')) {
            $this->info("✅ Table city existe déjà");
            return;
        }

        $this->info("📋 Création de la table city...");

        Schema::create('city', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->integer('reorder_id')->default(0);
            $table->tinyInteger('is_available')->default(1);
            $table->tinyInteger('is_deleted')->default(2);
            $table->timestamps();
        });

        $this->info("✅ Table city créée avec succès");

        // Ajouter des villes par défaut
        $defaultCities = [
            [
                'name' => 'Dakar',
                'code' => 'DK',
                'description' => 'Capitale du Sénégal',
                'reorder_id' => 1,
                'is_available' => 1,
                'is_deleted' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Thiès',
                'code' => 'TH',
                'description' => 'Ville de Thiès',
                'reorder_id' => 2,
                'is_available' => 1,
                'is_deleted' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Saint-Louis',
                'code' => 'SL',
                'description' => 'Ville historique de Saint-Louis',
                'reorder_id' => 3,
                'is_available' => 1,
                'is_deleted' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ziguinchor',
                'code' => 'ZG',
                'description' => 'Ville de Ziguinchor en Casamance',
                'reorder_id' => 4,
                'is_available' => 1,
                'is_deleted' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Touba',
                'code' => 'TB',
                'description' => 'Ville sainte de Touba',
                'reorder_id' => 5,
                'is_available' => 1,
                'is_deleted' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($defaultCities as $city) {
            DB::table('city')->insert($city);
        }

        $this->info("✅ Table city peuplée avec 5 villes par défaut");

        $count = DB::table('city')->where('is_available', 1)->where('is_deleted', 2)->count();
        $this->info("Nombre de villes ajoutées: {$count}");

        $cities = DB::table('city')->where('is_available', 1)->where('is_deleted', 2)->orderBy('reorder_id')->get();
        foreach ($cities as $city) {
            $this->line("- {$city->name} ({$city->code}) - {$city->description}");
        }
    }

    private function createPromotionalBannerTable()
    {
        $this->info("🔧 Vérification de la table promotionalbanner...");

        if (Schema::hasTable('promotionalbanner')) {
            $this->info("✅ Table promotionalbanner existe déjà");
            return;
        }

        $this->info("📋 Création de la table promotionalbanner...");

        Schema::create('promotionalbanner', function (Blueprint $table) {
            $table->id();
            $table->integer('reorder_id')->nullable();
            $table->integer('vendor_id');
            $table->string('image', 255);
            $table->timestamps();

            // Add index for vendor_id for better performance
            $table->index('vendor_id');
            $table->index('reorder_id');
        });

        $this->info("✅ Table promotionalbanner créée avec succès");

        // Ajouter des bannières promotionnelles par défaut
        $defaultBanners = [
            [
                'vendor_id' => 1,
                'image' => 'default-banner-1.jpg',
                'reorder_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => 1,
                'image' => 'default-banner-2.jpg',
                'reorder_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => 1,
                'image' => 'default-banner-3.jpg',
                'reorder_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($defaultBanners as $banner) {
            DB::table('promotionalbanner')->insert($banner);
        }

        $this->info("✅ Table promotionalbanner peuplée avec 3 bannières par défaut");

        $count = DB::table('promotionalbanner')->where('vendor_id', 1)->count();
        $this->info("Nombre de bannières ajoutées pour vendor_id=1: {$count}");

        $banners = DB::table('promotionalbanner')->where('vendor_id', 1)->orderBy('reorder_id')->get();
        foreach ($banners as $banner) {
            $this->line("- {$banner->image} (ordre: {$banner->reorder_id})");
        }
    }

    private function createAboutTable()
    {
        $this->info("🔧 Vérification de la table about...");

        if (Schema::hasTable('about')) {
            $this->info("✅ Table about existe déjà");
            return;
        }

        $this->info("📋 Création de la table about...");

        Schema::create('about', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->longText('about_content')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('vendor_id');
            $table->unique('vendor_id'); // One about content per vendor
        });

        $this->info("✅ Table about créée avec succès");

        // Ajouter du contenu About par défaut
        $defaultAbout = [
            'vendor_id' => 1,
            'about_content' => 'Bienvenue dans notre restaurant ! Nous sommes une équipe passionnée dédiée à vous offrir la meilleure expérience culinaire. Notre équipe de chefs expérimentés utilise des ingrédients frais et de qualité pour préparer des plats savoureux. Que vous recherchiez un repas rapide ou une expérience gastronomique, nous avons quelque chose pour tous les goûts. Notre engagement envers l\'excellence se reflète dans chaque plat que nous servons.',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('about')->insert($defaultAbout);

        $this->info("✅ Table about peuplée avec du contenu par défaut");

        $count = DB::table('about')->where('vendor_id', 1)->count();
        $this->info("Nombre de contenus About pour vendor_id=1: {$count}");

        $about = DB::table('about')->where('vendor_id', 1)->first();
        if ($about) {
            $preview = substr($about->about_content, 0, 100) . '...';
            $this->line("- Contenu About: {$preview}");
        }
    }

    private function fixBlogsTable()
    {
        $this->info("🔧 Vérification de la table blogs...");

        if (!Schema::hasTable('blogs')) {
            $this->error("❌ Table blogs n'existe pas");
            return;
        }

        $this->info("✅ Table blogs existe");

        // Vérifier et ajouter la colonne vendor_id
        if (!Schema::hasColumn('blogs', 'vendor_id')) {
            $this->info("📋 Ajout de la colonne vendor_id à la table blogs...");
            Schema::table('blogs', function (Blueprint $table) {
                $table->bigInteger('vendor_id')->after('id')->default(1);
                $table->index('vendor_id');
            });
            $this->info("✅ Colonne vendor_id ajoutée avec succès");
        } else {
            $this->info("✅ Colonne vendor_id existe déjà");
        }

        // Vérifier et ajouter la colonne reorder_id
        if (!Schema::hasColumn('blogs', 'reorder_id')) {
            $this->info("📋 Ajout de la colonne reorder_id à la table blogs...");
            Schema::table('blogs', function (Blueprint $table) {
                $table->integer('reorder_id')->default(0)->after('id');
            });
            $this->info("✅ Colonne reorder_id ajoutée avec succès");
        } else {
            $this->info("✅ Colonne reorder_id existe déjà");
        }

        // Ajouter des blogs par défaut si la table est vide
        $existingBlogs = DB::table('blogs')->where('vendor_id', 1)->count();
        if ($existingBlogs == 0) {
            $this->info("📋 Ajout de blogs par défaut...");

            $defaultBlogs = [
                [
                    'reorder_id' => 1,
                    'vendor_id' => 1,
                    'slug' => 'ouverture-nouveau-restaurant',
                    'title' => 'Ouverture de notre nouveau restaurant',
                    'image' => 'blog_opening.jpg',
                    'description' => 'Nous sommes ravis d\'annoncer l\'ouverture de notre nouveau restaurant ! Venez découvrir nos spécialités culinaires dans un cadre moderne et convivial.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'reorder_id' => 2,
                    'vendor_id' => 1,
                    'slug' => 'menu-automne-2024',
                    'title' => 'Découvrez notre menu d\'automne 2024',
                    'image' => 'blog_autumn_menu.jpg',
                    'description' => 'Notre chef a concocté un menu spécial automne avec des produits de saison. Découvrez des saveurs authentiques et des plats réconfortants.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'reorder_id' => 3,
                    'vendor_id' => 1,
                    'slug' => 'livraison-gratuite',
                    'title' => 'Livraison gratuite pour toute commande',
                    'image' => 'blog_free_delivery.jpg',
                    'description' => 'Profitez de la livraison gratuite pour toute commande supérieure à 25€. Commandez en ligne et recevez vos plats préférés directement chez vous.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($defaultBlogs as $blog) {
                DB::table('blogs')->insert($blog);
            }

            $this->info("✅ Table blogs peuplée avec 3 articles par défaut");
        } else {
            $this->info("✅ Table blogs contient déjà des articles");
        }

        $count = DB::table('blogs')->where('vendor_id', 1)->count();
        $this->info("Nombre d'articles de blog: {$count}");

        $blogs = DB::table('blogs')->where('vendor_id', 1)->orderBy('reorder_id')->get(['title', 'reorder_id']);
        foreach ($blogs as $blog) {
            $this->line("- {$blog->title} (ordre: {$blog->reorder_id})");
        }
    }

    private function fixUsersTable()
    {
        $this->info("🔧 Vérification de la table users...");

        if (!Schema::hasTable('users')) {
            $this->error("❌ Table users n'existe pas");
            return;
        }

        $this->info("✅ Table users existe");

        // Vérifier et ajouter la colonne plan_id
        if (!Schema::hasColumn('users', 'plan_id')) {
            $this->info("📋 Ajout de la colonne plan_id à la table users...");
            Schema::table('users', function (Blueprint $table) {
                $table->bigInteger('plan_id')->nullable()->after('email');
                $table->index('plan_id');
            });
            $this->info("✅ Colonne plan_id ajoutée avec succès");
        } else {
            $this->info("✅ Colonne plan_id existe déjà");
        }

        // Vérifier et ajouter la colonne allow_without_subscription si nécessaire
        if (!Schema::hasColumn('users', 'allow_without_subscription')) {
            $this->info("📋 Ajout de la colonne allow_without_subscription à la table users...");
            Schema::table('users', function (Blueprint $table) {
                $table->integer('allow_without_subscription')->default(0)->after('plan_id');
            });
            $this->info("✅ Colonne allow_without_subscription ajoutée avec succès");
        } else {
            $this->info("✅ Colonne allow_without_subscription existe déjà");
        }

        // Assigner un plan par défaut à l'utilisateur 1 s'il n'en a pas
        $user1 = DB::table('users')->where('id', 1)->first();
        if ($user1 && !$user1->plan_id) {
            $firstPlan = DB::table('pricing_plans')->where('is_available', 1)->orderBy('price')->first();
            if ($firstPlan) {
                DB::table('users')->where('id', 1)->update([
                    'plan_id' => $firstPlan->id,
                    'allow_without_subscription' => 1
                ]);
                $this->info("✅ Plan '{$firstPlan->name}' assigné à l'utilisateur 1");
            }
        } else {
            $this->info("✅ Utilisateur 1 a déjà un plan assigné");
        }

        $user1Updated = DB::table('users')->where('id', 1)->first();
        if ($user1Updated && $user1Updated->plan_id) {
            $plan = DB::table('pricing_plans')->where('id', $user1Updated->plan_id)->first();
            $this->info("Plan actuel de l'utilisateur 1: {$plan->name}");
        }
    }

    private function fixSettingsTable()
    {
        $this->info("🔧 Vérification de la table settings...");

        if (!Schema::hasTable('settings')) {
            $this->error("❌ Table settings n'existe pas");
            return;
        }

        $this->info("✅ Table settings existe");

        // Vérifier et ajouter les colonnes de liens sociaux
        $socialColumns = ['facebook_link', 'twitter_link', 'instagram_link', 'linkedin_link'];
        foreach ($socialColumns as $column) {
            if (!Schema::hasColumn('settings', $column)) {
                $this->info("📋 Ajout de la colonne {$column} à la table settings...");
                Schema::table('settings', function (Blueprint $table) use ($column) {
                    $table->string($column)->nullable();
                });
                $this->info("✅ Colonne {$column} ajoutée avec succès");
            } else {
                $this->info("✅ Colonne {$column} existe déjà");
            }
        }

        // Vérifier et ajouter la colonne cover_image
        if (!Schema::hasColumn('settings', 'cover_image')) {
            $this->info("📋 Ajout de la colonne cover_image à la table settings...");
            Schema::table('settings', function (Blueprint $table) {
                $table->string('cover_image')->default('default-cover.png')->after('linkedin_link');
            });
            $this->info("✅ Colonne cover_image ajoutée avec succès");
        } else {
            $this->info("✅ Colonne cover_image existe déjà");
        }

        // Vérifier et ajouter la colonne tracking_id si elle n'existe pas
        if (!Schema::hasColumn('settings', 'tracking_id')) {
            $this->info("📋 Ajout de la colonne tracking_id à la table settings...");
            Schema::table('settings', function (Blueprint $table) {
                $table->string('tracking_id')->nullable()->after('cover_image');
            });
            $this->info("✅ Colonne tracking_id ajoutée avec succès");
        } else {
            $this->info("✅ Colonne tracking_id existe déjà");
        }

        // Vérifier et ajouter la colonne available_on_landing si elle n'existe pas
        if (!Schema::hasColumn('settings', 'available_on_landing')) {
            $this->info("📋 Ajout de la colonne available_on_landing à la table settings...");
            Schema::table('settings', function (Blueprint $table) {
                $table->boolean('available_on_landing')->default(1)->after('tracking_id');
            });
            $this->info("✅ Colonne available_on_landing ajoutée avec succès");
        } else {
            $this->info("✅ Colonne available_on_landing existe déjà");
        }

        // Vérifier qu'il y a des paramètres par défaut pour le vendor_id = 1
        $settings = DB::table('settings')->where('vendor_id', 1)->first();
        if (!$settings) {
            $this->info("📋 Création des paramètres par défaut pour le vendor_id = 1...");
            DB::table('settings')->insert([
                'vendor_id' => 1,
                'currency' => 'XOF',
                'currency_position' => 'left',
                'currency_space' => 1,
                'decimal_separator' => 1,
                'currency_formate' => 2,
                'maintenance_mode' => 0,
                'checkout_login_required' => 0,
                'is_checkout_login_required' => 0,
                'delivery_type' => '1,2',
                'timezone' => 'UTC',
                'website_title' => 'RestroSaaS',
                'meta_title' => 'RestroSaaS - Restaurant Management System',
                'language' => 'fr',
                'template' => 'default',
                'template_type' => 1,
                'primary_color' => '#181D31',
                'secondary_color' => '#6096B4',
                'landing_website_title' => 'RestroSaaS',
                'image_size' => 5,
                'time_format' => 'H:i',
                'date_format' => 'Y-m-d',
                'cover_image' => 'default-cover.png',
                'available_on_landing' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->info("✅ Paramètres par défaut créés pour le vendor_id = 1");
        } else {
            $this->info("✅ Paramètres existent déjà pour le vendor_id = 1");
        }

        $count = DB::table('settings')->count();
        $this->info("Nombre total de paramètres: {$count}");
    }
}

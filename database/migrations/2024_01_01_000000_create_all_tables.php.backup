<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration fusionnée de toutes les tables
     * Générée automatiquement le 2025-10-26 17:14:55
     */
    public function up()
    {
        // Table: users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // Table: password_resets
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Table: failed_jobs
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // Table: personal_access_tokens
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // Table: categories
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->string('name');
            $table->string('slug');
            $table->string('image');
            $table->boolean('is_available')->comment('1--> yes, 2-->No')->default('1');
            $table->boolean('is_deleted')->comment('1--> yes, 2-->No')->default('2');
            $table->timestamps();
        });

        // Table: services
        Schema::create('services', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->integer('category_id');
            $table->string('name');
            $table->string('slug');
            $table->double('price');
            $table->string('description');
            $table->boolean('is_available')->comment('1="yes",2="no"')->default('1');
            $table->boolean('is_deleted')->comment('1="yes",2="no"')->default('2');
            $table->timestamps();
        });

        // Table: service_images
        Schema::create('service_images', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_id');
            $table->string('image');
            $table->timestamps();
        });

        // Table: banners
        Schema::create('banners', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->integer('service_id')->nullable()->default(null);
            $table->integer('category_id')->nullable()->default(null);
            $table->string('image');
            $table->boolean('type')->nullable()->comment('1=category,2=service,3=')->default(null);
            $table->integer('section')->nullable()->comment('1=banner1,2=banner2,3=banner3')->default(1);
            $table->boolean('is_available')->comment('1=yes,2=no')->default('1');
            $table->timestamps();
        });

        // Table: blogs
        Schema::create('blogs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug');
            $table->string('title');
            $table->string('image');
            $table->longText('description');
            $table->timestamps();
        });

        // Table: promocodes
        Schema::create('promocodes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->string('offer_name');
            $table->string('offer_code');
            $table->integer('offer_type')->comment('1=fixed,2=percentage');
            $table->string('offer_amount');
            $table->integer('min_amount');
            $table->integer('usage_type')->comment('1=one time,2=multiple times');
            $table->integer('usage_limit');
            $table->date('start_date');
            $table->date('exp_date');
            $table->longText('description');
            $table->longText('is_available')->comment('1=yes,2=no');
            $table->timestamps();
        });

        // Table: payments
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->string('payment_name');
            $table->string('payment_type');
            // 1=COD, 2=Razorpay, 3=Stripe, etc. $table->string('environment')->nullable();
            // sandbox/live $table->text('public_key')->nullable();
            $table->text('secret_key')->nullable();
            $table->string('currency')->nullable();
            $table->string('image')->nullable();
            $table->text('payment_description')->nullable();
            // for bank transfer $table->string('account_holder_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('bank_ifsc_code')->nullable();
            $table->string('encryption_key')->nullable();
            // for flutterwave $table->string('base_url_by_region')->nullable();
            // for paytab $table->integer('is_available')->default(1);
            // 1=yes, 2=no $table->integer('is_activate')->default(1);
            // 1=active, 2=inactive $table->integer('reorder_id')->default(0);
            $table->timestamps();
        });

        // Table: bookings
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('booking_number');
            $table->integer('vendor_id');
            $table->integer('service_id');
            $table->string('service_image');
            $table->string('service_name');
            $table->string('offer_code');
            $table->double('offer_amount');
            $table->date('booking_date');
            $table->time('booking_time');
            $table->longText('address');
            $table->integer('payment_status')->comment('1=Pending,2=for paid');
            $table->string('customer_name');
            $table->string('mobile');
            $table->string('email');
            $table->longText('message');
            $table->double('sub_total');
            $table->double('tax');
            $table->double('grand_total');
            $table->string('transaction_id');
            $table->string('transaction_type');
            $table->timestamps();
        });

        // Table: products
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->integer('category_id');
            $table->string('name');
            $table->string('slug');
            $table->double('price');
            $table->string('description');
            $table->boolean('is_available')->comment('1="yes",2="no"')->default('1');
            $table->boolean('is_deleted')->comment('1="yes",2="no"')->default('2');
            $table->timestamps();
        });

        // Table: payment_methods
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->integer('type');
            $table->string('name');
            $table->text('image')->nullable();
            $table->json('credentials')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        // Table: settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vendor_id')->default(1);
            $table->string('currency', 20)->default('XOF');
            $table->enum('currency_position', ['left', 'right'])->default('left');
            $table->boolean('currency_space')->default(1);
            $table->integer('decimal_separator')->default(1);
            $table->integer('currency_formate')->default(2);
            $table->boolean('maintenance_mode')->default(0);
            $table->boolean('checkout_login_required')->default(0);
            $table->boolean('is_checkout_login_required')->default(0);
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('delivery_type')->default('1,2');
            $table->string('timezone')->default('UTC');
            $table->text('address')->nullable();
            $table->string('email')->nullable();
            $table->text('description')->nullable();
            $table->string('contact')->nullable();
            $table->text('copyright')->nullable();
            $table->string('website_title')->default('RestroSaaS');
            $table->string('meta_title')->default('RestroSaaS - Restaurant Management System');
            $table->text('meta_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('language', 10)->default('en');
            $table->string('template')->default('default');
            $table->integer('template_type')->default(1);
            $table->string('primary_color')->default('#181D31');
            $table->string('secondary_color')->default('#6096B4');
            $table->string('landing_website_title')->default('RestroSaaS');
            $table->string('custom_domain')->nullable();
            $table->integer('image_size')->default(5);
            $table->string('time_format')->default('H:i');
            $table->string('date_format')->default('Y-m-d');
            $table->timestamps();
            $table->index('vendor_id');
            $table->index('custom_domain');
        });

        // Table: footerfeatures
        Schema::create('footerfeatures', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->string('icon');
            $table->text('title');
            $table->text('description');
            $table->timestamps();
        });

        // Table: subscribers
        Schema::create('subscribers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->string('email');
            $table->timestamps();
        });

        // Table: favorites
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        // Table: carts
        Schema::create('carts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->integer('user_id')->nullable()->default(0);
            $table->text('session_id')->nullable()->default('');
            $table->integer('product_id');
            $table->string('product_name');
            $table->string('product_slug');
            $table->string('product_image');
            $table->string('attribute')->nullable();
            $table->integer('variation_id')->nullable();
            $table->string('variation_name')->nullable();
            $table->integer('qty')->default(1);
            $table->double('product_price');
            $table->double('product_tax');
            $table->timestamps();
        });

        // Table: orders
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->integer('user_id')->nullable();
            $table->text('session_id')->nullable();
            $table->string('order_number');
            $table->string('user_name');
            $table->string('user_email');
            $table->string('user_mobile');
            $table->string('billing_address');
            $table->string('billing_landmark');
            $table->string('billing_postal_code');
            $table->string('billing_city');
            $table->string('billing_state');
            $table->string('billing_country');
            $table->string('shipping_address');
            $table->string('shipping_landmark');
            $table->string('shipping_postal_code');
            $table->string('shipping_city');
            $table->string('shipping_state');
            $table->string('shipping_country');
            $table->double('sub_total')->default(0.0);
            $table->string('offer_code')->nullable();
            $table->double('offer_amount')->nullable()->default(0.0);
            $table->double('tax_amount')->default(0.0);
            $table->string('shipping_area');
            $table->double('delivery_charge')->default(0.0);
            $table->double('grand_total')->default(0.0);
            $table->string('transaction_id')->nullable();
            $table->boolean('transaction_type')->default(1);
            $table->integer('status')->comment('1 = order placed , 2 = order confirmed/accepted , 3 = order cancelled/rejected - by admin , 4 = order cancelled/rejected - by user/customer , 5 = order delivered , ');
            $table->longText('notes')->nullable();
            $table->timestamps();
        });

        // Table: order_details
        Schema::create('order_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->integer('user_id')->nullable();
            $table->text('session_id')->nullable();
            $table->integer('order_id');
            $table->integer('product_id');
            $table->string('product_name');
            $table->string('product_slug');
            $table->string('product_image');
            $table->string('attribute')->nullable();
            $table->integer('variation_id')->nullable();
            $table->string('variation_name')->nullable();
            $table->double('product_price');
            $table->double('product_tax');
            $table->integer('qty')->default(1);
            $table->timestamps();
        });

        // Table: contacts
        Schema::create('contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->string('name');
            $table->string('email');
            $table->string('mobile');
            $table->longText('message');
            $table->timestamps();
        });

        // Table: firebase_devices
        Schema::create('firebase_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('device_token')->unique();
            $table->enum('device_type', ['android', 'ios', 'web']);
            $table->string('device_name')->nullable();
            $table->string('device_model')->nullable();
            $table->string('device_os')->nullable();
            $table->string('app_version')->nullable();
            $table->string('os_version')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->json('topics')->nullable();
            $table->json('preferences')->nullable();
            $table->string('timezone')->nullable();
            $table->string('language', 10)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'is_active']);
            $table->index(['device_type', 'is_active']);
            $table->index('last_seen_at');
        });

        // Table: firebase_notifications
        Schema::create('firebase_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->json('data')->nullable();
            $table->string('action_url')->nullable();
            $table->enum('recipients_type', ['users', 'devices', 'topics', 'all', 'segment']);
            $table->json('recipients_data')->nullable();
            $table->enum('status', ['pending', 'scheduled', 'sent', 'failed', 'cancelled'])->default('pending');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('firebase_response')->nullable();
            $table->integer('success_count')->default(0);
            $table->integer('failure_count')->default(0);
            $table->integer('read_count')->default(0);
            $table->integer('click_count')->default(0);
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->unsignedBigInteger('automation_id')->nullable();
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->integer('ttl')->nullable();
            // Time to live in seconds $table->string('sound')->nullable();
            $table->integer('badge')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'scheduled_at']);
            $table->index(['recipients_type', 'status']);
            $table->index('sent_at');
            $table->index('created_at');
        });

        // Table: firebase_templates
        Schema::create('firebase_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('title');
            $table->text('body');
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->json('data')->nullable();
            $table->string('action_url')->nullable();
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('variables')->nullable();
            // Variables définies dans le template $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['category', 'is_active']);
            $table->index('created_by');
        });

        // Table: firebase_campaigns
        Schema::create('firebase_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('title');
            $table->text('body');
            $table->string('image')->nullable();
            $table->json('data')->nullable();
            $table->string('action_url')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'active', 'paused', 'completed', 'cancelled'])->default('draft');
            $table->enum('recipients_type', ['users', 'devices', 'topics', 'all', 'segment']);
            $table->json('recipients_data')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'scheduled_at']);
            $table->index('created_by');
        });

        // Table: firebase_automations
        Schema::create('firebase_automations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('trigger_type', ['order_created', 'order_confirmed', 'user_registered', 'payment_success', 'birthday', 'inactivity', 'custom']);
            $table->json('trigger_conditions')->nullable();
            // Conditions pour déclencher l'automation $table->string('title');
            $table->text('body');
            $table->string('image')->nullable();
            $table->json('data')->nullable();
            $table->string('action_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('delay_minutes')->default(0);
            // Délai avant envoi $table->integer('max_sends_per_user')->default(1);
            // Limite d'envois par utilisateur $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['trigger_type', 'is_active']);
            $table->index('created_by');
        });

        // Table: firebase_topics
        Schema::create('firebase_topics', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('subscriber_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index('is_active');
        });

        // Table: firebase_segments
        Schema::create('firebase_segments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('conditions');
            // Conditions pour inclure les utilisateurs $table->boolean('is_active')->default(true);
            $table->integer('user_count')->default(0);
            // Cache du nombre d'utilisateurs $table->timestamp('last_calculated_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['is_active', 'last_calculated_at']);
            $table->index('created_by');
        });

        // Table: firebase_analytics
        Schema::create('firebase_analytics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('metric_type');
            // delivery, open, click, etc. $table->string('metric_key');
            // notification_id, campaign_id, etc. $table->integer('metric_value')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['date', 'metric_type', 'metric_key']);
            $table->index(['date', 'metric_type']);
        });

        // Table: import_jobs
        Schema::create('import_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id');
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            // menus, customers, orders, categories $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('file_path');
            $table->string('original_filename');
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->integer('successful_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->json('errors')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['restaurant_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['type', 'status']);
            $table->index('scheduled_at');
        });

        // Table: export_jobs
        Schema::create('export_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id');
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            // menus, customers, orders, categories $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('file_path')->nullable();
            $table->string('filename');
            $table->enum('format', ['csv', 'xlsx', 'json'])->default('csv');
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->json('filters')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->integer('download_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            // Commented out until restaurants table exists $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['restaurant_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['type', 'status']);
            $table->index('scheduled_at');
            $table->index('expires_at');
        });

        // Table: device_tokens
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('device_token', 500);
            // Changed from text to string with length $table->string('device_type')->default('unknown');
            // ios, android, web $table->json('device_info')->nullable();
            $table->string('app_version')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'device_token']);
            $table->index(['user_id', 'is_active']);
            $table->index('device_type');
            $table->index('last_used_at');
        });

        // Table: import_export_jobs
        Schema::create('import_export_jobs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['import', 'export']);
            $table->string('data_type');
            // menus, products, customers, orders, etc. $table->string('file_path')->nullable();
            // Chemin du fichier source (import) $table->string('export_file_path')->nullable();
            // Chemin du fichier généré (export) $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('format')->nullable();
            // csv, xlsx, json, pdf $table->json('mapping')->nullable();
            // Mapping des champs $table->json('filters')->nullable();
            // Filtres pour l'export $table->json('options')->nullable();
            // Options diverses $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('total_records')->default(0);
            $table->integer('processed_records')->default(0);
            $table->integer('successful_records')->default(0);
            $table->integer('failed_records')->default(0);
            $table->json('errors')->nullable();
            // Erreurs rencontrées $table->json('warnings')->nullable();
            // Avertissements $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('progress', 5, 2)->default(0);
            // Pourcentage de progression $table->timestamp('estimated_completion')->nullable();
            $table->bigInteger('file_size')->nullable();
            // Taille du fichier en bytes $table->integer('memory_usage')->nullable();
            // Utilisation mémoire en MB $table->integer('execution_time')->nullable();
            // Temps d'exécution en secondes $table->json('metadata')->nullable();
            // Métadonnées diverses $table->timestamps();
            $table->softDeletes();
            $table->index(['type', 'status']);
            $table->index(['data_type', 'status']);
            $table->index(['user_id', 'type']);
            $table->index('status');
            $table->index('created_at');
        });

        // Table: import_export_mappings
        Schema::create('import_export_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('data_type');
            // Type de données (menus, products, etc.) $table->json('source_fields');
            // Champs sources disponibles $table->json('target_fields');
            // Champs cibles disponibles $table->json('field_mappings');
            // Mapping source -> cible $table->json('transformations')->nullable();
            // Transformations à appliquer $table->json('validation_rules')->nullable();
            // Règles de validation $table->boolean('is_default')->default(false);
            // Mapping par défaut pour ce type $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('usage_count')->default(0);
            // Nombre d'utilisations $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['data_type', 'is_active']);
            $table->index(['is_default', 'data_type']);
            $table->index('user_id');
        });

        // Table: import_export_templates
        Schema::create('import_export_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['import', 'export']);
            // Type de template $table->string('data_type');
            // Type de données $table->string('format');
            // Format du fichier (csv, xlsx, json) $table->json('fields');
            // Configuration des champs $table->json('sample_data')->nullable();
            // Données d'exemple $table->json('validation_rules')->nullable();
            // Règles de validation $table->json('transformations')->nullable();
            // Transformations par défaut $table->boolean('is_system')->default(false);
            // Template système $table->boolean('is_active')->default(true);
            $table->integer('download_count')->default(0);
            // Nombre de téléchargements $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['type', 'data_type']);
            $table->index(['format', 'is_active']);
            $table->index('is_system');
            $table->index('user_id');
        });

        // Table: import_export_schedules
        Schema::create('import_export_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['import', 'export']);
            $table->string('data_type');
            $table->string('format')->nullable();
            $table->string('frequency');
            // daily, weekly, monthly, custom $table->string('cron_expression')->nullable();
            // Expression cron pour custom $table->json('options')->nullable();
            // Options d'import/export $table->json('filters')->nullable();
            // Filtres pour export $table->string('file_path')->nullable();
            // Chemin du fichier pour import $table->string('export_destination')->nullable();
            // Destination pour export $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->integer('run_count')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('failure_count')->default(0);
            $table->json('last_result')->nullable();
            // Résultat de la dernière exécution $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['type', 'is_active']);
            $table->index('next_run_at');
            $table->index('user_id');
        });

        // Table: import_export_logs
        Schema::create('import_export_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->nullable()->constrained('import_export_jobs')->onDelete('set null');
            $table->enum('level', ['info', 'warning', 'error', 'debug']);
            $table->string('message');
            $table->text('details')->nullable();
            $table->json('context')->nullable();
            // Contexte supplémentaire $table->string('file')->nullable();
            // Fichier source de l'erreur $table->integer('line')->nullable();
            // Ligne source de l'erreur $table->timestamp('logged_at');
            $table->json('metadata')->nullable();
            $table->index(['job_id', 'level']);
            $table->index(['level', 'logged_at']);
            $table->index('logged_at');
        });

        // Table: import_export_files
        Schema::create('import_export_files', function (Blueprint $table) {
            $table->id();
            $table->string('original_name');
            // Nom original du fichier $table->string('stored_name');
            // Nom stocké $table->string('path');
            // Chemin de stockage $table->string('disk')->default('local');
            // Disque de stockage $table->string('mime_type');
            $table->bigInteger('size');
            // Taille en bytes $table->string('hash')->nullable();
            // Hash MD5/SHA1 $table->enum('type', ['import_source', 'export_result', 'template', 'sample']);
            $table->string('data_type')->nullable();
            // Type de données $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('job_id')->nullable()->constrained('import_export_jobs')->onDelete('set null');
            $table->integer('download_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            // Date d'expiration $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['type', 'data_type']);
            $table->index('user_id');
            $table->index('job_id');
            $table->index('expires_at');
            $table->index('hash');
        });

        // Table: import_export_field_mappings
        Schema::create('import_export_field_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Nom du mapping $table->string('data_type');
            // Type de données $table->string('source_field');
            // Champ source $table->string('target_field');
            // Champ cible $table->json('transformation_rules')->nullable();
            // Règles de transformation $table->json('validation_rules')->nullable();
            // Règles de validation $table->string('default_value')->nullable();
            // Valeur par défaut $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            // Ordre d'affichage $table->foreignId('mapping_id')->constrained('import_export_mappings')->onDelete('cascade');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['mapping_id', 'sort_order']);
            $table->index(['data_type', 'target_field']);
        });

        // Table: import_export_transformations
        Schema::create('import_export_transformations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Nom de la transformation $table->text('description')->nullable();
            $table->string('type');
            // Type de transformation (replace, regex, format, etc.) $table->json('parameters');
            // Paramètres de la transformation $table->string('input_type')->nullable();
            // Type d'entrée attendu $table->string('output_type')->nullable();
            // Type de sortie produit $table->text('example_input')->nullable();
            // Exemple d'entrée $table->text('example_output')->nullable();
            // Exemple de sortie $table->boolean('is_system')->default(false);
            // Transformation système $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('usage_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['type', 'is_active']);
            $table->index('is_system');
            $table->index('user_id');
        });

        // Table: import_export_validation_rules
        Schema::create('import_export_validation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Nom de la règle $table->text('description')->nullable();
            $table->string('field_type');
            // Type de champ (string, number, date, etc.) $table->string('rule_type');
            // Type de règle (required, min, max, regex, etc.) $table->json('parameters')->nullable();
            // Paramètres de la règle $table->string('error_message');
            // Message d'erreur $table->text('example')->nullable();
            // Exemple de validation $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('severity')->default(1);
            // 1=error, 2=warning, 3=info $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['field_type', 'rule_type']);
            $table->index(['is_system', 'is_active']);
            $table->index('user_id');
        });

        // Table: restaurants
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('restaurant_name');
            $table->string('restaurant_slug')->unique();
            $table->text('restaurant_address');
            $table->string('restaurant_phone', 20)->nullable();
            $table->string('restaurant_email')->nullable();
            $table->string('restaurant_image')->nullable();
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_active')->default(1);
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->decimal('minimum_order', 8, 2)->default(0);
            $table->integer('delivery_time')->default(30);
            // minutes $table->time('opening_time')->default('09:00');
            $table->time('closing_time')->default('22:00');
            $table->boolean('is_open')->default(1);
            $table->decimal('rating', 3, 1)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->timestamps();
        });

        // Table: customers
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->json('addresses')->nullable();
            $table->json('notification_preferences')->nullable();
            $table->boolean('status')->default(1);
            $table->rememberToken();
            $table->timestamps();
        });

        // Table: items
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('cat_id')->nullable();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('original_price', 10, 2)->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(1);
            $table->boolean('is_deleted')->default(0);
            $table->timestamps();
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            // Supprimer la clé étrangère pour categories pour le moment;
        });

        // Table: tables
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('table_number', 20);
            $table->string('name', 100)->nullable();
            $table->integer('capacity')->default(4);
            $table->string('location', 100)->nullable();
            $table->string('table_code', 20)->unique();
            $table->string('qr_code_path')->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance', 'occupied', 'free'])->default('active');
            $table->timestamp('last_accessed')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // Index pour optimiser les requêtes $table->index(['restaurant_id', 'status']);
            $table->index(['restaurant_id', 'table_number']);
            $table->unique(['restaurant_id', 'table_number']);
        });

        // Table: order_items
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 8, 2);
            $table->decimal('subtotal', 8, 2);
            $table->string('item_name');
            $table->json('item_options')->nullable();
            $table->text('special_instructions')->nullable();
            $table->timestamps();
        });

        // Table: table_ratings
        Schema::create('table_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('table_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('order_id')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->string('customer_name', 100)->nullable();
            $table->string('customer_email', 100)->nullable();
            $table->integer('rating')->between(1, 5);
            $table->text('comment')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            // Index pour optimiser les requêtes $table->index(['restaurant_id', 'created_at']);
            $table->index(['table_id', 'created_at']);
            $table->index(['rating']);
        });

        // Table: loyalty_cards
        Schema::create('loyalty_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('card_number')->unique();
            $table->integer('points')->default(0);
            $table->decimal('total_spent', 10, 2)->default(0);
            $table->integer('visits_count')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // Table: notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('action_url')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->timestamps();
            $table->index(['customer_id', 'read_at']);
            $table->index(['type', 'created_at']);
        });

        // Table: customer_password_resets
        Schema::create('customer_password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Table: loyalty_programs
        Schema::create('loyalty_programs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('type', ['points', 'visits', 'spend'])->default('points');
            $table->decimal('points_per_currency', 8, 2)->default(1.00);
            // Points par unité monétaire $table->decimal('currency_per_point', 8, 2)->default(0.01);
            // Valeur monétaire par point $table->integer('min_points_redemption')->default(100);
            $table->integer('points_expiry_months')->nullable();
            // null = jamais expire $table->json('tiers')->nullable();
            // Configuration des niveaux $table->json('rules')->nullable();
            // Règles personnalisées $table->json('settings')->nullable();
            $table->timestamps();
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            $table->index(['restaurant_id', 'is_active']);
            $table->index('type');
        });

        // Table: loyalty_tiers
        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('min_points')->default(0);
            $table->integer('min_spent')->default(0);
            $table->integer('min_visits')->default(0);
            $table->decimal('points_multiplier', 3, 2)->default(1.00);
            $table->json('benefits')->nullable();
            // Avantages spéciaux $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('program_id')->references('id')->on('loyalty_programs')->onDelete('cascade');
            $table->index(['program_id', 'is_active']);
            $table->index('sort_order');
        });

        // Table: loyalty_members
        Schema::create('loyalty_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name', 100);
            $table->string('email', 100);
            $table->string('phone', 20);
            $table->date('birth_date')->nullable();
            $table->string('member_code', 20)->unique();
            $table->integer('points_balance')->default(0);
            $table->integer('lifetime_points')->default(0);
            $table->foreignId('tier_id')->nullable()->constrained('loyalty_tiers')->onDelete('set null');
            $table->string('referral_code', 20)->unique();
            $table->timestamp('joined_at');
            $table->timestamp('last_activity_at')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->json('preferences')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // Index pour optimiser les requêtes $table->index(['restaurant_id', 'status']);
            $table->index(['restaurant_id', 'email']);
            $table->index(['restaurant_id', 'phone']);
            $table->index(['restaurant_id', 'points_balance']);
            $table->unique(['restaurant_id', 'email']);
            $table->unique(['restaurant_id', 'phone']);
        });

        // Table: loyalty_transactions
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('loyalty_members')->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('order_id')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            $table->enum('type', [ 'welcome_bonus', 'order_purchase', 'referral_bonus', 'birthday_bonus', 'challenge_completion', 'admin_adjustment', 'reward_redemption', 'points_expiry', 'tier_upgrade_bonus' ]);
            $table->integer('points');
            $table->integer('balance_after');
            $table->text('description');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            // Index pour optimiser les requêtes $table->index(['member_id', 'created_at']);
            $table->index(['restaurant_id', 'created_at']);
            $table->index(['type']);
            $table->index(['expires_at']);
        });

        // Table: loyalty_rewards
        Schema::create('loyalty_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('tier_id')->nullable()->constrained('loyalty_tiers')->onDelete('set null');
            $table->string('title', 200);
            $table->text('description');
            $table->enum('reward_type', [ 'discount_percentage', 'discount_fixed', 'free_item', 'free_delivery', 'cashback', 'special_offer' ]);
            $table->decimal('reward_value', 10, 2);
            $table->integer('points_required');
            $table->string('image_url')->nullable();
            $table->json('terms_conditions')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_limit_per_member')->nullable();
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            // Index pour optimiser les requêtes $table->index(['restaurant_id', 'status']);
            $table->index(['restaurant_id', 'tier_id']);
            $table->index(['points_required']);
            $table->index(['valid_from', 'valid_until']);
        });

        // Table: loyalty_redemptions
        Schema::create('loyalty_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('loyalty_members')->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('reward_id')->constrained('loyalty_rewards')->onDelete('cascade');
            $table->unsignedInteger('order_id')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            $table->integer('points_used');
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->string('redeem_code', 20)->unique();
            $table->enum('status', ['pending', 'used', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('redeemed_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('used_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            // Index pour optimiser les requêtes $table->index(['member_id', 'status']);
            $table->index(['restaurant_id', 'status']);
            $table->index(['reward_id']);
            $table->index(['redeem_code']);
            $table->index(['expires_at']);
        });

        // Table: pos_terminals
        Schema::create('pos_terminals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id');
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->string('location')->nullable();
            $table->unsignedBigInteger('current_user_id')->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->json('settings')->nullable();
            $table->json('hardware_info')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('mac_address')->nullable();
            $table->timestamps();
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            $table->foreign('current_user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['restaurant_id', 'status']);
            $table->index('current_user_id');
            $table->index('last_activity');
        });

        // Table: pos_sessions
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id');
            $table->unsignedBigInteger('terminal_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('status', ['active', 'closed', 'suspended'])->default('active');
            $table->decimal('opening_cash', 10, 2)->default(0);
            $table->decimal('closing_cash', 10, 2)->nullable();
            $table->decimal('expected_cash', 10, 2)->nullable();
            $table->decimal('cash_difference', 10, 2)->nullable();
            $table->integer('total_transactions')->default(0);
            $table->decimal('total_sales', 10, 2)->default(0);
            $table->json('payment_summary')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->json('settings')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            $table->foreign('terminal_id')->references('id')->on('pos_terminals')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['restaurant_id', 'status']);
            $table->index(['terminal_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('started_at');
        });

        // Table: menu_items
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id');
            $table->unsignedInteger('category_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('image_url')->nullable();
            $table->enum('status', ['active', 'inactive', 'out_of_stock'])->default('active');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_vegetarian')->default(false);
            $table->boolean('is_vegan')->default(false);
            $table->boolean('is_gluten_free')->default(false);
            $table->json('allergens')->nullable();
            $table->json('nutritional_info')->nullable();
            $table->string('preparation_time')->nullable();
            $table->string('barcode')->nullable();
            $table->string('sku')->nullable();
            $table->boolean('track_inventory')->default(false);
            $table->integer('stock_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(0);
            $table->json('modifiers')->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->index(['restaurant_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index('is_featured');
            $table->index('barcode');
            $table->index('sku');
            $table->index('sort_order');
        });

        // Table: pos_carts
        Schema::create('pos_carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('terminal_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('menu_item_id');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->json('modifiers')->nullable();
            $table->text('special_instructions')->nullable();
            $table->timestamps();
            $table->foreign('terminal_id')->references('id')->on('pos_terminals')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('session_id')->references('id')->on('pos_sessions')->onDelete('cascade');
            $table->foreign('menu_item_id')->references('id')->on('menu_items')->onDelete('cascade');
            $table->index(['terminal_id', 'user_id']);
            $table->index('session_id');
            $table->index('menu_item_id');
        });

        // Table: api_keys
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('hashed_key')->unique();
            $table->json('permissions')->nullable();
            $table->foreignId('restaurant_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['hashed_key', 'is_active']);
            $table->index(['restaurant_id', 'is_active']);
            $table->index(['user_id', 'is_active']);
            $table->index('expires_at');
        });

        // Table: table_bookings
        Schema::create('table_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->integer('guests_count');
            $table->date('booking_date');
            $table->time('booking_time');
            $table->text('special_requests')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            // Indexes $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['vendor_id', 'booking_date', 'booking_time']);
            $table->index('status');
        });

        // Table: paypal_transactions
        Schema::create('paypal_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->string('paypal_payment_id')->unique();
            $table->string('paypal_order_id')->nullable();
            $table->string('payer_id')->nullable();
            $table->enum('type', [ 'express_checkout', 'direct_credit_card', 'subscription', 'billing_agreement', 'refund' ])->default('express_checkout');
            $table->enum('status', [ 'created', 'approved', 'completed', 'failed', 'cancelled', 'denied', 'pending', 'refunded' ])->default('created');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->decimal('fee_amount', 10, 2)->nullable();
            $table->decimal('net_amount', 10, 2)->nullable();
            // Informations de remboursement $table->string('refund_id')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->enum('refund_status', ['pending', 'completed', 'failed'])->nullable();
            // Détails de la transaction $table->json('transaction_details')->nullable();
            $table->json('webhook_data')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'status']);
            $table->index(['paypal_payment_id']);
            $table->index(['status', 'created_at']);
        });

        // Table: paypal_subscriptions
        Schema::create('paypal_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('restaurant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('paypal_subscription_id')->unique();
            $table->string('plan_id');
            $table->enum('status', [ 'active', 'cancelled', 'suspended', 'expired', 'pending', 'approval_pending' ])->default('pending');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            // Cycle de facturation $table->enum('billing_cycle', ['daily', 'weekly', 'monthly', 'yearly'])->default('monthly');
            $table->integer('billing_frequency')->default(1);
            // Dates importantes $table->timestamp('start_date')->nullable();
            $table->timestamp('next_billing_date')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            // Statistiques $table->integer('failure_count')->default(0);
            // Détails $table->json('subscription_details')->nullable();
            $table->json('webhook_data')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['restaurant_id', 'status']);
            $table->index(['paypal_subscription_id']);
            $table->index(['status', 'next_billing_date']);
        });

        // Table: paypal_plans
        Schema::create('paypal_plans', function (Blueprint $table) {
            $table->id();
            $table->string('paypal_plan_id')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            // Configuration du cycle $table->enum('billing_cycle', ['daily', 'weekly', 'monthly', 'yearly'])->default('monthly');
            $table->integer('billing_frequency')->default(1);
            // URLs de retour $table->string('return_url')->nullable();
            $table->string('cancel_url')->nullable();
            // Paramètres avancés $table->decimal('setup_fee', 10, 2)->default(0);
            $table->integer('max_fail_attempts')->default(3);
            $table->boolean('auto_bill_amount')->default(true);
            // Détails du plan $table->json('plan_details')->nullable();
            $table->timestamps();
            $table->index(['paypal_plan_id']);
            $table->index(['is_active']);
        });

        // Table: paypal_webhooks
        Schema::create('paypal_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('webhook_id')->nullable();
            $table->string('event_id')->unique();
            $table->string('event_type');
            $table->enum('status', ['received', 'processed', 'failed'])->default('received');
            // Données de l'événement $table->json('event_data');
            $table->timestamp('event_time');
            // Traitement $table->text('processing_result')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();
            $table->index(['event_id']);
            $table->index(['event_type', 'status']);
            $table->index(['status', 'created_at']);
        });

        // Table: paypal_disputes
        Schema::create('paypal_disputes', function (Blueprint $table) {
            $table->id();
            $table->string('dispute_id')->unique();
            $table->foreignId('transaction_id')->nullable()->constrained('paypal_transactions')->onDelete('set null');
            $table->enum('status', [ 'open', 'waiting_for_buyer_response', 'waiting_for_seller_response', 'under_paypal_review', 'resolved', 'other' ]);
            $table->enum('reason', [ 'merchandise_or_service_not_received', 'merchandise_or_service_not_as_described', 'unauthorized', 'credit_not_processed', 'cancelled_recurring_billing', 'problem_with_remittance', 'other' ]);
            $table->decimal('dispute_amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            // Dates importantes $table->timestamp('dispute_time');
            $table->timestamp('respond_by_date')->nullable();
            // Communication $table->json('messages')->nullable();
            $table->json('evidence_documents')->nullable();
            // Résolution $table->enum('outcome', ['resolved_buyer_favour', 'resolved_seller_favour', 'resolved_with_payout'])->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['dispute_id']);
            $table->index(['status', 'respond_by_date']);
            $table->index(['transaction_id']);
        });

        // Table: paypal_settings
        Schema::create('paypal_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->nullable()->constrained()->onDelete('cascade');
            // Configuration PayPal $table->string('client_id')->nullable();
            $table->text('client_secret')->nullable();
            // Encrypted $table->enum('mode', ['sandbox', 'live'])->default('sandbox');
            $table->string('webhook_url')->nullable();
            $table->string('webhook_id')->nullable();
            // Paramètres de paiement $table->string('currency', 3)->default('EUR');
            $table->boolean('enabled')->default(false);
            $table->boolean('express_checkout_enabled')->default(true);
            $table->boolean('credit_card_enabled')->default(true);
            $table->boolean('subscriptions_enabled')->default(false);
            // URLs par défaut $table->string('return_url')->nullable();
            $table->string('cancel_url')->nullable();
            // Configuration des frais $table->decimal('transaction_fee_percentage', 5, 2)->default(0);
            $table->decimal('transaction_fee_fixed', 10, 2)->default(0);
            // Dernière synchronisation $table->timestamp('last_sync_at')->nullable();
            $table->json('sync_status')->nullable();
            $table->timestamps();
            $table->index(['restaurant_id']);
            $table->index(['enabled']);
        });

        // Table: social_accounts
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Informations du provider $table->string('provider');
            // google, facebook, twitter, etc. $table->string('provider_id');
            $table->string('provider_token', 500)->nullable();
            $table->string('provider_refresh_token', 500)->nullable();
            $table->timestamp('provider_expires_at')->nullable();
            // Informations du profil $table->string('avatar')->nullable();
            $table->json('profile_data')->nullable();
            // Données du profil social // Métadonnées $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            // Index et contraintes $table->unique(['provider', 'provider_id']);
            $table->index(['user_id', 'provider']);
            $table->index(['provider', 'is_active']);
            $table->index(['last_login_at']);
        });

        // Table: social_login_attempts
        Schema::create('social_login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('provider_id')->nullable();
            $table->string('email')->nullable();
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            // Résultat de la tentative $table->enum('status', ['success', 'failed', 'blocked'])->default('failed');
            $table->text('failure_reason')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            // Données de la tentative $table->json('attempt_data')->nullable();
            $table->timestamp('attempted_at')->useCurrent();
            $table->timestamps();
            $table->index(['provider', 'status']);
            $table->index(['email', 'attempted_at']);
            $table->index(['ip_address', 'attempted_at']);
        });

        // Table: social_access_tokens
        Schema::create('social_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_account_id')->constrained()->onDelete('cascade');
            // Token information $table->string('token_type')->default('Bearer');
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->integer('expires_in')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('scopes')->nullable();
            // Permissions accordées // Métadonnées $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            $table->index(['social_account_id', 'is_active']);
            $table->index(['expires_at']);
            $table->index(['last_used_at']);
        });

        // Table: social_invitations
        Schema::create('social_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Utilisateur qui invite $table->string('provider');
            // google, facebook, etc. $table->string('provider_user_id');
            // ID de l'ami sur le réseau social // Informations de l'invitation $table->string('invited_email')->nullable();
            $table->string('invited_name')->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['sent', 'accepted', 'declined', 'expired'])->default('sent');
            // Suivi $table->timestamp('sent_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
            $table->foreignId('invited_user_id')->nullable()->constrained('users')->onDelete('set null');
            // Récompenses $table->boolean('reward_given')->default(false);
            $table->decimal('reward_amount', 8, 2)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'provider']);
            $table->index(['status', 'sent_at']);
            $table->index(['invited_email']);
        });

        // Table: social_shares
        Schema::create('social_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider');
            // Contenu partagé $table->string('content_type');
            // restaurant, menu_item, order, review $table->unsignedBigInteger('content_id');
            $table->text('shared_content')->nullable();
            // Contenu du partage $table->string('share_url')->nullable();
            // Résultat du partage $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
            $table->string('provider_post_id')->nullable();
            // ID du post sur le réseau social $table->json('response_data')->nullable();
            // Statistiques $table->integer('likes_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->timestamp('last_stats_update')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'provider']);
            $table->index(['content_type', 'content_id']);
            $table->index(['status', 'created_at']);
        });

        // Table: social_profile_syncs
        Schema::create('social_profile_syncs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_account_id')->constrained()->onDelete('cascade');
            // Données synchronisées $table->json('synced_fields');
            // Champs qui ont été synchronisés $table->json('previous_data')->nullable();
            // Données avant sync $table->json('new_data')->nullable();
            // Nouvelles données // Métadonnées de synchronisation $table->enum('sync_type', ['manual', 'automatic', 'login']);
            $table->enum('status', ['success', 'failed', 'partial'])->default('success');
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index(['social_account_id', 'sync_type']);
            $table->index(['status', 'created_at']);
        });

        // Table: social_settings
        Schema::create('social_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->nullable()->constrained()->onDelete('cascade');
            // Configuration Google $table->boolean('google_enabled')->default(false);
            $table->string('google_client_id')->nullable();
            $table->text('google_client_secret')->nullable();
            // Encrypted $table->json('google_scopes')->nullable();
            // Configuration Facebook $table->boolean('facebook_enabled')->default(false);
            $table->string('facebook_app_id')->nullable();
            $table->text('facebook_app_secret')->nullable();
            // Encrypted $table->json('facebook_permissions')->nullable();
            // Configuration Twitter/X $table->boolean('twitter_enabled')->default(false);
            $table->string('twitter_api_key')->nullable();
            $table->text('twitter_api_secret')->nullable();
            // Encrypted // Paramètres généraux $table->boolean('auto_sync_profiles')->default(true);
            $table->boolean('allow_registration')->default(true);
            $table->boolean('link_existing_accounts')->default(true);
            $table->json('default_user_roles')->nullable();
            // URLs de redirection $table->string('success_redirect_url')->nullable();
            $table->string('error_redirect_url')->nullable();
            $table->timestamps();
            $table->index(['restaurant_id']);
        });

        // Table: social_webhooks
        Schema::create('social_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('webhook_id')->nullable();
            // ID du webhook chez le provider $table->string('event_type');
            // Données de l'événement $table->json('payload');
            $table->string('signature')->nullable();
            // Pour vérification // Traitement $table->enum('status', ['received', 'processing', 'processed', 'failed'])->default('received');
            $table->text('processing_result')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();
            $table->index(['provider', 'event_type']);
            $table->index(['status', 'created_at']);
        });

        // Table: restaurant_qr_menus
        Schema::create('restaurant_qr_menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('qr_code_path');
            $table->string('menu_url');
            $table->json('table_numbers')->nullable();
            // Tables spécifiques $table->json('settings')->nullable();
            // Paramètres QR (couleur, taille, etc.) $table->boolean('is_active')->default(true);
            $table->integer('scan_count')->default(0);
            $table->timestamp('last_scanned_at')->nullable();
            $table->timestamps();
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['vendor_id', 'is_active']);
            $table->index('slug');
        });

        // Table: qr_menu_scans
        Schema::create('qr_menu_scans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('qr_menu_id');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_type')->nullable();
            $table->string('table_number')->nullable();
            $table->json('location_data')->nullable();
            // Géolocalisation si disponible $table->timestamp('scanned_at');
            $table->timestamps();
            $table->foreign('qr_menu_id')->references('id')->on('restaurant_qr_menus')->onDelete('cascade');
            $table->index(['qr_menu_id', 'scanned_at']);
        });

        // Table: qr_menu_designs
        Schema::create('qr_menu_designs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('name');
            $table->string('logo_path')->nullable();
            $table->string('background_color')->default('#ffffff');
            $table->string('foreground_color')->default('#000000');
            $table->integer('size')->default(300);
            $table->string('format')->default('png');
            $table->json('custom_settings')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('vendor_id');
        });

        // Table: restaurant_wallets
        Schema::create('restaurant_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->unique();
            $table->decimal('balance', 15, 2)->default(0);
            // Solde disponible $table->decimal('pending_balance', 15, 2)->default(0);
            // En attente $table->decimal('total_earnings', 15, 2)->default(0);
            // Total généré $table->decimal('total_withdrawn', 15, 2)->default(0);
            // Total retiré $table->timestamps();
            $table->foreign('vendor_id')->references('id')->on('users');
            $table->index(['vendor_id', 'balance']);
        });

        // Table: wallet_transactions
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('transaction_id')->unique();
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 15, 2);
            $table->string('source');
            // 'order', 'withdrawal', 'commission', 'refund' $table->unsignedBigInteger('reference_id')->nullable();
            // order_id, withdrawal_id $table->string('description');
            $table->enum('status', ['pending', 'completed', 'failed']);
            $table->timestamps();
            $table->foreign('vendor_id')->references('id')->on('users');
            $table->index(['vendor_id', 'type', 'status']);
        });

        // Table: withdrawal_methods
        Schema::create('withdrawal_methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('type');
            // 'orange_money', 'mtn_money', 'moov_money', 'bank', 'cinetpay' $table->string('account_number');
            $table->string('account_name');
            $table->json('additional_info')->nullable();
            // Infos supplémentaires $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->foreign('vendor_id')->references('id')->on('users');
            $table->index(['vendor_id', 'type', 'is_active']);
        });

        // Table: withdrawal_requests
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('request_id')->unique();
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2);
            $table->unsignedBigInteger('withdrawal_method_id');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled']);
            $table->string('provider_transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->foreign('vendor_id')->references('id')->on('users');
            $table->foreign('withdrawal_method_id')->references('id')->on('withdrawal_methods');
            $table->index(['vendor_id', 'status']);
        });

        // Table: push_subscriptions
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('endpoint', 500);
            $table->string('auth_key');
            $table->string('p256dh_key');
            $table->boolean('is_active')->default(true);
            $table->string('user_agent')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'endpoint'], 'user_endpoint_unique');
        });

        // Table: languages
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Nom de la langue $table->string('code', 5);
            // Code de la langue (en, fr, etc.) $table->string('layout', 10)->default('ltr');
            // Direction (ltr, rtl) $table->string('image')->nullable();
            // Image/flag de la langue $table->enum('is_default', [1, 2])->default(2);
            // 1 = default, 2 = not default $table->enum('is_available', [1, 2])->default(1);
            // 1 = available, 2 = not available $table->enum('is_deleted', [1, 2])->default(2);
            // 1 = deleted, 2 = not deleted $table->timestamps();
            // Index sur le code pour les recherches fréquentes $table->index('code');
        });

        // Table: systemaddons
        Schema::create('systemaddons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unique_identifier');
            $table->string('version', 20);
            $table->integer('activated');
            $table->string('image');
            $table->integer('type')->nullable();
            $table->timestamps();
            // Index sur unique_identifier pour les recherches fréquentes $table->index('unique_identifier');
        });

        // Table: pricing_plans
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('features')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('duration')->default(30);
            // en jours $table->integer('service_limit')->default(-1);
            // -1 = illimité $table->integer('appoinment_limit')->default(-1);
            // -1 = illimité $table->enum('type', ['monthly', 'yearly', 'lifetime'])->default('monthly');
            $table->boolean('is_available')->default(1);
            $table->timestamps();
        });

        // Table: transactions
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('payment_id')->nullable();
            $table->string('payment_type')->default('1');
            // 1=COD, 2=Online, etc. $table->enum('status', ['1', '2', '3'])->default('2');
            // 1=pending, 2=success, 3=failed $table->date('expire_date')->nullable();
            $table->integer('service_limit')->default(-1);
            $table->integer('appoinment_limit')->default(-1);
            $table->text('response')->nullable();
            $table->timestamps();
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('pricing_plans')->onDelete('set null');
        });

        // Table: top_deals
        Schema::create('top_deals', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_available')->default(1);
            $table->timestamps();
        });

        // Table: app_settings
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id');
            $table->string('android_link')->nullable();
            $table->string('ios_link')->nullable();
            $table->tinyInteger('mobile_app_on_off')->default(2);
            // 1 = on, 2 = off $table->string('image')->nullable();
            $table->timestamps();
        });

        // Table: social_links
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id');
            $table->text('icon');
            $table->text('link');
            $table->timestamps();
        });

        // Table: timings
        Schema::create('timings', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id');
            $table->string('day', 50);
            $table->string('open_time', 30);
            $table->string('break_start');
            $table->string('break_end');
            $table->string('close_time', 30);
            $table->tinyInteger('is_always_close')->comment('1 For Yes, 2 For No');
            $table->timestamps();
        });

        // Table: city
        Schema::create('city', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Nom de la ville $table->string('code')->nullable();
            // Code de la ville $table->text('description')->nullable();
            // Description $table->integer('reorder_id')->default(0);
            // Ordre d'affichage $table->tinyInteger('is_available')->default(1);
            // 1=disponible, 0=indisponible $table->tinyInteger('Is_deleted')->default(2);
            // 1=supprimé, 2=actif $table->timestamps();
        });

        // Table: area
        Schema::create('area', function (Blueprint $table) {
            $table->id();
            $table->string('area');
            $table->unsignedBigInteger('city_id');
            $table->string('description')->nullable();
            $table->integer('reorder_id')->default(0);
            $table->integer('is_available')->default(1);
            $table->integer('is_deleted')->default(2);
            $table->timestamps();
            $table->foreign('city_id')->references('id')->on('city')->onDelete('cascade');
        });

        // Table: features
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->integer('reorder_id')->nullable();
            $table->integer('vendor_id');
            $table->string('title');
            $table->longText('description');
            $table->string('image');
            $table->timestamps();
        });

        // Table: testimonials
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

        // Table: store_category
        Schema::create('store_category', function (Blueprint $table) {
            $table->id();
            $table->integer('reorder_id');
            $table->string('name');
            $table->integer('is_available')->default(1)->comment('1=Yes,2=No');
            $table->integer('is_deleted')->default(2)->comment('1=Yes,2=No');
            $table->timestamps();
        });

        // Table: promotionalbanner
        Schema::create('promotionalbanner', function (Blueprint $table) {
            $table->id();
            $table->integer('reorder_id')->nullable();
            $table->integer('vendor_id');
            $table->string('image', 255);
            $table->timestamps();
            // Add index for vendor_id for better performance $table->index('vendor_id');
            $table->index('reorder_id');
        });

        // Table: tax
        Schema::create('tax', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vendor_id');
            $table->string('name');
            $table->decimal('percentage', 8, 2)->default(0);
            $table->text('description')->nullable();
            $table->integer('reorder_id')->default(0);
            $table->tinyInteger('is_available')->default(1)->comment('1=Yes, 2=No');
            $table->tinyInteger('is_deleted')->default(2)->comment('1=Yes, 2=No');
            $table->timestamps();
            $table->index(['vendor_id', 'is_deleted', 'is_available']);
        });

        // Table: coupons
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['fixed', 'percentage']);
            $table->decimal('price', 10, 2);
            $table->datetime('active_from');
            $table->datetime('active_to');
            $table->integer('limit')->default(0);
            $table->integer('reorder_id')->default(0);
            $table->boolean('is_available')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
            // Indexes for performance $table->index('vendor_id');
            $table->index('reorder_id');
            $table->index(['is_available', 'is_deleted']);
            $table->index('code');
        });

        // Table: pixcel_settings
        Schema::create('pixcel_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('facebook_pixcel_id')->nullable();
            $table->string('twitter_pixcel_id')->nullable();
            $table->string('linkedin_pixcel_id')->nullable();
            $table->string('googletag_pixcel_id')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            // Indexes for performance $table->index('vendor_id');
            $table->unique('vendor_id');
            // One pixel setting per vendor;
        });

        // Table: about
        Schema::create('about', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->longText('about_content')->nullable();
            $table->timestamps();
            // Indexes for performance $table->index('vendor_id');
            $table->unique('vendor_id');
            // One about content per vendor;
        });

        // Table: faqs
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('question')->nullable();
            $table->text('answer')->nullable();
            $table->integer('reorder_id')->default(0);
            $table->timestamps();
            // Indexes for performance $table->index('vendor_id');
            $table->index('reorder_id');
        });

        // Table: privacypolicy
        Schema::create('privacypolicy', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->longText('privacypolicy_content')->nullable();
            $table->timestamps();
            // Indexes for performance $table->index('vendor_id');
            $table->unique('vendor_id');
            // One privacy policy per vendor;
        });

        // Table: refund_policy
        Schema::create('refund_policy', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->longText('refund_policy_content')->nullable();
            $table->timestamps();
            // Indexes for performance $table->index('vendor_id');
            $table->unique('vendor_id');
            // One refund policy per vendor;
        });

        // Table: terms
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->longText('terms_content')->nullable();
            $table->timestamps();
            // Indexes for performance $table->index('vendor_id');
            $table->unique('vendor_id');
            // One terms and conditions per vendor;
        });

        // Table: whatsapp_messages_log
        Schema::create('whatsapp_messages_log', function (Blueprint $table) {
            $table->id();
            // Relations $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('restaurant_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            // Informations du message $table->string('phone', 20);
            // Numéro de téléphone destinataire $table->string('message_type', 50);
            // order_notification, payment_confirmation, delivery_update $table->string('message_id', 255)->nullable();
            // ID du message retourné par WhatsApp // Statut $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('pending');
            $table->text('error')->nullable();
            // Message d'erreur en cas d'échec $table->string('error_code', 50)->nullable();
            // Code d'erreur WhatsApp // Métadonnées $table->integer('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            // Index $table->index('order_id');
            $table->index('restaurant_id');
            $table->index('customer_id');
            $table->index('message_id');
            $table->index('status');
            $table->index('message_type');
            $table->index(['phone', 'created_at']);
        });

        // Table: customer_addresses
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            // Client $table->string('address_name', 100);
            // Maison, Bureau, etc. $table->text('address');
            // Adresse complète $table->string('phone', 20);
            // Téléphone de contact $table->boolean('is_default')->default(false);
            $table->timestamps();
            // Index $table->index('user_id');
            $table->index(['user_id', 'is_default']);
        });

        // Table: wishlists
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            // Client $table->unsignedBigInteger('item_id');
            // Produit $table->timestamps();
            // Index et contraintes d'unicité $table->index('user_id');
            $table->index('item_id');
            $table->unique(['user_id', 'item_id']);
            // Un produit une seule fois par utilisateur;
        });

        // Table: whatsapp_logs
        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->string('to', 20)->index()->comment('Numéro destinataire');
            $table->text('message')->comment('Contenu du message');
            $table->string('status', 100)->default('pending')->comment('Statut de l\'envoi');
            $table->boolean('success')->default(false)->index()->comment('Succès ou échec');
            $table->string('message_id', 100)->nullable()->comment('ID WhatsApp du message');
            $table->json('response')->nullable()->comment('Réponse de l\'API WhatsApp');
            $table->json('context')->nullable()->comment('Contexte additionnel (order_id, etc.)');
            $table->timestamp('sent_at')->nullable()->comment('Date et heure d\'envoi');
            $table->timestamps();
            // Index pour les requêtes fréquentes $table->index('created_at');
            $table->index(['success', 'created_at']);
        });

        // Table: custom_status
        Schema::create('custom_status', function (Blueprint $table) {
            $table->id();
            $table->integer('reorder_id')->default(0);
            $table->integer('vendor_id');
            $table->string('name');
            $table->integer('type')->comment('1=default,2=process,3=complete,4=cancel');
            $table->integer('is_available')->default(1);
            $table->integer('is_deleted')->default(2);
            $table->integer('order_type')->default(1)->comment('1=delivery,2=pickup,3=dinein,4=pos');
            $table->timestamps();
            $table->index('vendor_id');
            $table->index(['vendor_id', 'order_type']);
        });

        // Table: table_qr_scans
        Schema::create('table_qr_scans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('table_id');
            $table->unsignedBigInteger('restaurant_id');
            $table->timestamp('scanned_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->string('device_type', 50)->nullable();
            // mobile, tablet, desktop $table->string('browser', 100)->nullable();
            $table->string('platform', 100)->nullable();
            $table->string('country', 2)->nullable();
            $table->string('city', 100)->nullable();
            $table->timestamps();
            // Index pour performances $table->index('table_id');
            $table->index('restaurant_id');
            $table->index('scanned_at');
            $table->index(['restaurant_id', 'scanned_at']);
            // Clés étrangères $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('restaurant_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Table: variants
        Schema::create('variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('original_price', 10, 2)->default(0);
            $table->integer('qty')->default(0);
            $table->integer('min_order')->default(1);
            $table->integer('max_order')->default(0);
            $table->integer('low_qty')->default(0);
            $table->boolean('is_available')->default(1);
            $table->boolean('stock_management')->default(0)->comment('stck_management in model');
            $table->timestamps();
            $table->index('item_id');
        });

        // Table: item_images
        Schema::create('item_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->string('image')->nullable();
            $table->timestamps();
            $table->index('item_id');
        });

        // Table: global_extras
        Schema::create('global_extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('reorder_id')->default(0);
            $table->boolean('is_available')->default(1);
            $table->timestamps();
            $table->index('vendor_id');
            $table->index('reorder_id');
        });

        // Table: deliveryareas
        Schema::create('deliveryareas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('reorder_id')->default(0);
            $table->timestamps();
            $table->index('vendor_id');
            $table->index('reorder_id');
        });

        // Table: table_book
        Schema::create('table_book', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->integer('total_members')->default(1);
            $table->date('booking_date');
            $table->time('booking_time');
            $table->text('message')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=pending, 2=confirmed, 3=cancelled');
            $table->timestamps();
            $table->index('vendor_id');
            $table->index('status');
            $table->index('booking_date');
        });

        // Table: seo_meta
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->string('page_type', 50);
            // home, menu, product, category, blog, contact $table->unsignedBigInteger('page_id')->nullable();
            // ID du produit, catégorie, article... $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('twitter_card', 50)->default('summary_large_image');
            $table->text('schema_markup')->nullable();
            // JSON-LD Schema.org $table->string('canonical_url')->nullable();
            $table->boolean('index')->default(true);
            // Indexable par Google $table->boolean('follow')->default(true);
            // Suivre les liens $table->timestamps();
            // Foreign key $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            // Index composé pour performance $table->index(['vendor_id', 'page_type', 'page_id']);
        });

        // Table: seo_metas
        Schema::create('seo_metas', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('seo_metas');
        Schema::dropIfExists('seo_meta');
        Schema::dropIfExists('table_book');
        Schema::dropIfExists('deliveryareas');
        Schema::dropIfExists('global_extras');
        Schema::dropIfExists('item_images');
        Schema::dropIfExists('variants');
        Schema::dropIfExists('table_qr_scans');
        Schema::dropIfExists('custom_status');
        Schema::dropIfExists('whatsapp_logs');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('whatsapp_messages_log');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('refund_policy');
        Schema::dropIfExists('privacypolicy');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('about');
        Schema::dropIfExists('pixcel_settings');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('tax');
        Schema::dropIfExists('promotionalbanner');
        Schema::dropIfExists('store_category');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('features');
        Schema::dropIfExists('area');
        Schema::dropIfExists('city');
        Schema::dropIfExists('timings');
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('top_deals');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('pricing_plans');
        Schema::dropIfExists('systemaddons');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('withdrawal_requests');
        Schema::dropIfExists('withdrawal_methods');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('restaurant_wallets');
        Schema::dropIfExists('qr_menu_designs');
        Schema::dropIfExists('qr_menu_scans');
        Schema::dropIfExists('restaurant_qr_menus');
        Schema::dropIfExists('social_webhooks');
        Schema::dropIfExists('social_settings');
        Schema::dropIfExists('social_profile_syncs');
        Schema::dropIfExists('social_shares');
        Schema::dropIfExists('social_invitations');
        Schema::dropIfExists('social_access_tokens');
        Schema::dropIfExists('social_login_attempts');
        Schema::dropIfExists('social_accounts');
        Schema::dropIfExists('paypal_settings');
        Schema::dropIfExists('paypal_disputes');
        Schema::dropIfExists('paypal_webhooks');
        Schema::dropIfExists('paypal_plans');
        Schema::dropIfExists('paypal_subscriptions');
        Schema::dropIfExists('paypal_transactions');
        Schema::dropIfExists('table_bookings');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('pos_carts');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('pos_sessions');
        Schema::dropIfExists('pos_terminals');
        Schema::dropIfExists('loyalty_redemptions');
        Schema::dropIfExists('loyalty_rewards');
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('loyalty_members');
        Schema::dropIfExists('loyalty_tiers');
        Schema::dropIfExists('loyalty_programs');
        Schema::dropIfExists('customer_password_resets');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('loyalty_cards');
        Schema::dropIfExists('table_ratings');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('tables');
        Schema::dropIfExists('items');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('restaurants');
        Schema::dropIfExists('import_export_validation_rules');
        Schema::dropIfExists('import_export_transformations');
        Schema::dropIfExists('import_export_field_mappings');
        Schema::dropIfExists('import_export_files');
        Schema::dropIfExists('import_export_logs');
        Schema::dropIfExists('import_export_schedules');
        Schema::dropIfExists('import_export_templates');
        Schema::dropIfExists('import_export_mappings');
        Schema::dropIfExists('import_export_jobs');
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('export_jobs');
        Schema::dropIfExists('import_jobs');
        Schema::dropIfExists('firebase_analytics');
        Schema::dropIfExists('firebase_segments');
        Schema::dropIfExists('firebase_topics');
        Schema::dropIfExists('firebase_automations');
        Schema::dropIfExists('firebase_campaigns');
        Schema::dropIfExists('firebase_templates');
        Schema::dropIfExists('firebase_notifications');
        Schema::dropIfExists('firebase_devices');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('order_details');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('subscribers');
        Schema::dropIfExists('footerfeatures');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('products');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('promocodes');
        Schema::dropIfExists('blogs');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('service_images');
        Schema::dropIfExists('services');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('users');
    }
};

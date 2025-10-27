<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table des comptes sociaux liés
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Informations du provider
            $table->string('provider'); // google, facebook, twitter, etc.
            $table->string('provider_id');
            $table->string('provider_token', 500)->nullable();
            $table->string('provider_refresh_token', 500)->nullable();
            $table->timestamp('provider_expires_at')->nullable();

            // Informations du profil
            $table->string('avatar')->nullable();
            $table->json('profile_data')->nullable(); // Données du profil social

            // Métadonnées
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Index et contraintes
            $table->unique(['provider', 'provider_id']);
            $table->index(['user_id', 'provider']);
            $table->index(['provider', 'is_active']);
            $table->index(['last_login_at']);
        });

        // Table des tentatives de connexion sociale
        Schema::create('social_login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('provider_id')->nullable();
            $table->string('email')->nullable();
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();

            // Résultat de la tentative
            $table->enum('status', ['success', 'failed', 'blocked'])->default('failed');
            $table->text('failure_reason')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            // Données de la tentative
            $table->json('attempt_data')->nullable();
            $table->timestamp('attempted_at')->useCurrent();

            $table->timestamps();

            $table->index(['provider', 'status']);
            $table->index(['email', 'attempted_at']);
            $table->index(['ip_address', 'attempted_at']);
        });

        // Table des tokens d'accès sociaux
        Schema::create('social_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_account_id')->constrained()->onDelete('cascade');

            // Token information
            $table->string('token_type')->default('Bearer');
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->integer('expires_in')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('scopes')->nullable(); // Permissions accordées

            // Métadonnées
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->integer('usage_count')->default(0);

            $table->timestamps();

            $table->index(['social_account_id', 'is_active']);
            $table->index(['expires_at']);
            $table->index(['last_used_at']);
        });

        // Table des invitations sociales
        Schema::create('social_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Utilisateur qui invite
            $table->string('provider'); // google, facebook, etc.
            $table->string('provider_user_id'); // ID de l'ami sur le réseau social

            // Informations de l'invitation
            $table->string('invited_email')->nullable();
            $table->string('invited_name')->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['sent', 'accepted', 'declined', 'expired'])->default('sent');

            // Suivi
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
            $table->foreignId('invited_user_id')->nullable()->constrained('users')->onDelete('set null');

            // Récompenses
            $table->boolean('reward_given')->default(false);
            $table->decimal('reward_amount', 8, 2)->nullable();

            $table->timestamps();

            $table->index(['user_id', 'provider']);
            $table->index(['status', 'sent_at']);
            $table->index(['invited_email']);
        });

        // Table des partages sociaux
        Schema::create('social_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider');

            // Contenu partagé
            $table->string('content_type'); // restaurant, menu_item, order, review
            $table->unsignedBigInteger('content_id');
            $table->text('shared_content')->nullable(); // Contenu du partage
            $table->string('share_url')->nullable();

            // Résultat du partage
            $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
            $table->string('provider_post_id')->nullable(); // ID du post sur le réseau social
            $table->json('response_data')->nullable();

            // Statistiques
            $table->integer('likes_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->timestamp('last_stats_update')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'provider']);
            $table->index(['content_type', 'content_id']);
            $table->index(['status', 'created_at']);
        });

        // Table des synchronisations de profil
        Schema::create('social_profile_syncs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_account_id')->constrained()->onDelete('cascade');

            // Données synchronisées
            $table->json('synced_fields'); // Champs qui ont été synchronisés
            $table->json('previous_data')->nullable(); // Données avant sync
            $table->json('new_data')->nullable(); // Nouvelles données

            // Métadonnées de synchronisation
            $table->enum('sync_type', ['manual', 'automatic', 'login']);
            $table->enum('status', ['success', 'failed', 'partial'])->default('success');
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index(['social_account_id', 'sync_type']);
            $table->index(['status', 'created_at']);
        });

        // Table des paramètres sociaux par restaurant
        Schema::create('social_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->nullable()->constrained()->onDelete('cascade');

            // Configuration Google
            $table->boolean('google_enabled')->default(false);
            $table->string('google_client_id')->nullable();
            $table->text('google_client_secret')->nullable(); // Encrypted
            $table->json('google_scopes')->nullable();

            // Configuration Facebook
            $table->boolean('facebook_enabled')->default(false);
            $table->string('facebook_app_id')->nullable();
            $table->text('facebook_app_secret')->nullable(); // Encrypted
            $table->json('facebook_permissions')->nullable();

            // Configuration Twitter/X
            $table->boolean('twitter_enabled')->default(false);
            $table->string('twitter_api_key')->nullable();
            $table->text('twitter_api_secret')->nullable(); // Encrypted

            // Paramètres généraux
            $table->boolean('auto_sync_profiles')->default(true);
            $table->boolean('allow_registration')->default(true);
            $table->boolean('link_existing_accounts')->default(true);
            $table->json('default_user_roles')->nullable();

            // URLs de redirection
            $table->string('success_redirect_url')->nullable();
            $table->string('error_redirect_url')->nullable();

            $table->timestamps();

            $table->index(['restaurant_id']);
        });

        // Table des webhooks sociaux
        Schema::create('social_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('webhook_id')->nullable(); // ID du webhook chez le provider
            $table->string('event_type');

            // Données de l'événement
            $table->json('payload');
            $table->string('signature')->nullable(); // Pour vérification

            // Traitement
            $table->enum('status', ['received', 'processing', 'processed', 'failed'])->default('received');
            $table->text('processing_result')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->integer('retry_count')->default(0);

            $table->timestamps();

            $table->index(['provider', 'event_type']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_webhooks');
        Schema::dropIfExists('social_settings');
        Schema::dropIfExists('social_profile_syncs');
        Schema::dropIfExists('social_shares');
        Schema::dropIfExists('social_invitations');
        Schema::dropIfExists('social_access_tokens');
        Schema::dropIfExists('social_login_attempts');
        Schema::dropIfExists('social_accounts');
    }
};

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
            $table->integer('ttl')->nullable(); // Time to live in seconds
            $table->string('sound')->nullable();
            $table->integer('badge')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'scheduled_at']);
            $table->index(['recipients_type', 'status']);
            $table->index('sent_at');
            $table->index('created_at');
        });

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
            $table->json('variables')->nullable(); // Variables définies dans le template
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category', 'is_active']);
            $table->index('created_by');
        });

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

        Schema::create('firebase_automations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('trigger_type', ['order_created', 'order_confirmed', 'user_registered', 'payment_success', 'birthday', 'inactivity', 'custom']);
            $table->json('trigger_conditions')->nullable(); // Conditions pour déclencher l'automation
            $table->string('title');
            $table->text('body');
            $table->string('image')->nullable();
            $table->json('data')->nullable();
            $table->string('action_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('delay_minutes')->default(0); // Délai avant envoi
            $table->integer('max_sends_per_user')->default(1); // Limite d'envois par utilisateur
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['trigger_type', 'is_active']);
            $table->index('created_by');
        });

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

        Schema::create('firebase_segments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('conditions'); // Conditions pour inclure les utilisateurs
            $table->boolean('is_active')->default(true);
            $table->integer('user_count')->default(0); // Cache du nombre d'utilisateurs
            $table->timestamp('last_calculated_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'last_calculated_at']);
            $table->index('created_by');
        });

        Schema::create('firebase_analytics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('metric_type'); // delivery, open, click, etc.
            $table->string('metric_key'); // notification_id, campaign_id, etc.
            $table->integer('metric_value')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['date', 'metric_type', 'metric_key']);
            $table->index(['date', 'metric_type']);
        });

        // Add foreign key constraints after all tables are created
        Schema::table('firebase_notifications', function (Blueprint $table) {
            $table->foreign('campaign_id')->references('id')->on('firebase_campaigns')->onDelete('set null');
            $table->foreign('template_id')->references('id')->on('firebase_templates')->onDelete('set null');
            $table->foreign('automation_id')->references('id')->on('firebase_automations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firebase_analytics');
        Schema::dropIfExists('firebase_segments');
        Schema::dropIfExists('firebase_topics');
        Schema::dropIfExists('firebase_automations');
        Schema::dropIfExists('firebase_campaigns');
        Schema::dropIfExists('firebase_templates');
        Schema::dropIfExists('firebase_notifications');
        Schema::dropIfExists('firebase_devices');
    }
};

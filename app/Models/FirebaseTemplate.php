<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FirebaseTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'firebase_templates';

    protected $fillable = [
        'name',
        'description',
        'title',
        'body',
        'image',
        'icon',
        'data',
        'action_url',
        'category',
        'is_active',
        'variables',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'data' => 'array',
        'variables' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'data' => '[]',
        'variables' => '[]',
        'metadata' => '[]',
        'is_active' => true,
    ];

    /**
     * Relation avec le créateur
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec les notifications
     */
    public function notifications()
    {
        return $this->hasMany(FirebaseNotification::class, 'template_id');
    }

    /**
     * Templates actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Templates par catégorie
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Compiler le template avec des variables
     */
    public function compile($variables = [])
    {
        $compiled = [
            'title' => $this->title,
            'body' => $this->body,
            'image' => $this->image,
            'icon' => $this->icon,
            'data' => $this->data,
            'action_url' => $this->action_url,
        ];

        // Remplacer les variables dans le titre et le corps
        foreach ($variables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $compiled['title'] = str_replace($placeholder, $value, $compiled['title']);
            $compiled['body'] = str_replace($placeholder, $value, $compiled['body']);
            $compiled['action_url'] = str_replace($placeholder, $value, $compiled['action_url']);
        }

        return $compiled;
    }

    /**
     * Obtenir les variables utilisées dans le template
     */
    public function getUsedVariables()
    {
        $text = $this->title . ' ' . $this->body . ' ' . $this->action_url;
        preg_match_all('/\{\{(\w+)\}\}/', $text, $matches);
        return array_unique($matches[1]);
    }

    /**
     * Valider les variables requises
     */
    public function validateVariables($variables = [])
    {
        $required = $this->getUsedVariables();
        $provided = array_keys($variables);
        $missing = array_diff($required, $provided);

        return [
            'valid' => empty($missing),
            'missing' => $missing,
            'required' => $required
        ];
    }

    /**
     * Obtenir les statistiques d'utilisation
     */
    public function getUsageStats()
    {
        return [
            'total_uses' => $this->notifications()->count(),
            'recent_uses' => $this->notifications()->where('created_at', '>=', now()->subDays(30))->count(),
            'success_rate' => $this->getSuccessRate(),
            'last_used' => $this->notifications()->latest()->first()?->created_at,
        ];
    }

    /**
     * Obtenir le taux de réussite
     */
    public function getSuccessRate()
    {
        $notifications = $this->notifications()->where('status', 'sent')->get();
        if ($notifications->isEmpty()) return 0;

        $totalSent = $notifications->sum('success_count');
        $totalFailed = $notifications->sum('failure_count');
        $total = $totalSent + $totalFailed;

        return $total > 0 ? round(($totalSent / $total) * 100, 2) : 0;
    }
}

class FirebaseCampaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'firebase_campaigns';

    protected $fillable = [
        'name',
        'description',
        'title',
        'body',
        'image',
        'data',
        'action_url',
        'status',
        'recipients_type',
        'recipients_data',
        'scheduled_at',
        'started_at',
        'ended_at',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'data' => 'array',
        'recipients_data' => 'array',
        'metadata' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'data' => '[]',
        'recipients_data' => '[]',
        'metadata' => '[]',
        'status' => 'draft',
    ];

    /**
     * Relation avec le créateur
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec les notifications
     */
    public function notifications()
    {
        return $this->hasMany(FirebaseNotification::class, 'campaign_id');
    }

    /**
     * Campagnes actives
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Campagnes par statut
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Démarrer la campagne
     */
    public function start()
    {
        $this->update([
            'status' => 'active',
            'started_at' => now()
        ]);
    }

    /**
     * Terminer la campagne
     */
    public function stop()
    {
        $this->update([
            'status' => 'completed',
            'ended_at' => now()
        ]);
    }

    /**
     * Mettre en pause la campagne
     */
    public function pause()
    {
        $this->update(['status' => 'paused']);
    }

    /**
     * Reprendre la campagne
     */
    public function resume()
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Obtenir les statistiques de la campagne
     */
    public function getStats()
    {
        $notifications = $this->notifications;

        return [
            'total_notifications' => $notifications->count(),
            'total_sent' => $notifications->sum('success_count'),
            'total_failed' => $notifications->sum('failure_count'),
            'total_read' => $notifications->sum('read_count'),
            'total_clicks' => $notifications->sum('click_count'),
            'success_rate' => $this->getSuccessRate(),
            'read_rate' => $this->getReadRate(),
            'click_rate' => $this->getClickRate(),
        ];
    }

    /**
     * Obtenir le taux de réussite
     */
    public function getSuccessRate()
    {
        $notifications = $this->notifications;
        $totalSent = $notifications->sum('success_count');
        $totalFailed = $notifications->sum('failure_count');
        $total = $totalSent + $totalFailed;

        return $total > 0 ? round(($totalSent / $total) * 100, 2) : 0;
    }

    /**
     * Obtenir le taux de lecture
     */
    public function getReadRate()
    {
        $notifications = $this->notifications;
        $totalSent = $notifications->sum('success_count');
        $totalRead = $notifications->sum('read_count');

        return $totalSent > 0 ? round(($totalRead / $totalSent) * 100, 2) : 0;
    }

    /**
     * Obtenir le taux de clic
     */
    public function getClickRate()
    {
        $notifications = $this->notifications;
        $totalRead = $notifications->sum('read_count');
        $totalClicks = $notifications->sum('click_count');

        return $totalRead > 0 ? round(($totalClicks / $totalRead) * 100, 2) : 0;
    }
}

class FirebaseAutomation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'firebase_automations';

    protected $fillable = [
        'name',
        'description',
        'trigger_type',
        'trigger_conditions',
        'title',
        'body',
        'image',
        'data',
        'action_url',
        'is_active',
        'delay_minutes',
        'max_sends_per_user',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'trigger_conditions' => 'array',
        'data' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'delay_minutes' => 'integer',
        'max_sends_per_user' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'trigger_conditions' => '[]',
        'data' => '[]',
        'metadata' => '[]',
        'is_active' => true,
        'delay_minutes' => 0,
        'max_sends_per_user' => 1,
    ];

    /**
     * Relation avec le créateur
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec les notifications
     */
    public function notifications()
    {
        return $this->hasMany(FirebaseNotification::class, 'automation_id');
    }

    /**
     * Automations actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Automations par type de déclencheur
     */
    public function scopeByTriggerType($query, $type)
    {
        return $query->where('trigger_type', $type);
    }

    /**
     * Activer l'automation
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Désactiver l'automation
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Vérifier si les conditions sont remplies
     */
    public function checkConditions($data = [])
    {
        $conditions = $this->trigger_conditions ?? [];

        foreach ($conditions as $condition) {
            $field = $condition['field'] ?? null;
            $operator = $condition['operator'] ?? '=';
            $value = $condition['value'] ?? null;
            $dataValue = $data[$field] ?? null;

            if (!$this->evaluateCondition($dataValue, $operator, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Évaluer une condition
     */
    protected function evaluateCondition($dataValue, $operator, $expectedValue)
    {
        return match($operator) {
            '=' => $dataValue == $expectedValue,
            '!=' => $dataValue != $expectedValue,
            '>' => $dataValue > $expectedValue,
            '<' => $dataValue < $expectedValue,
            '>=' => $dataValue >= $expectedValue,
            '<=' => $dataValue <= $expectedValue,
            'contains' => str_contains($dataValue, $expectedValue),
            'not_contains' => !str_contains($dataValue, $expectedValue),
            'in' => in_array($dataValue, (array)$expectedValue),
            'not_in' => !in_array($dataValue, (array)$expectedValue),
            default => false,
        };
    }

    /**
     * Obtenir les statistiques de l'automation
     */
    public function getStats()
    {
        $notifications = $this->notifications;

        return [
            'total_triggered' => $notifications->count(),
            'total_sent' => $notifications->sum('success_count'),
            'total_failed' => $notifications->sum('failure_count'),
            'success_rate' => $this->getSuccessRate(),
            'last_triggered' => $notifications->latest()->first()?->created_at,
        ];
    }

    /**
     * Obtenir le taux de réussite
     */
    public function getSuccessRate()
    {
        $notifications = $this->notifications;
        $totalSent = $notifications->sum('success_count');
        $totalFailed = $notifications->sum('failure_count');
        $total = $totalSent + $totalFailed;

        return $total > 0 ? round(($totalSent / $total) * 100, 2) : 0;
    }
}

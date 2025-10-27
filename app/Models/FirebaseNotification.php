<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FirebaseNotification extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'firebase_notifications';

    protected $fillable = [
        'title',
        'body',
        'image',
        'icon',
        'data',
        'action_url',
        'recipients_type',
        'recipients_data',
        'status',
        'scheduled_at',
        'sent_at',
        'sent_by',
        'firebase_response',
        'success_count',
        'failure_count',
        'read_count',
        'click_count',
        'campaign_id',
        'template_id',
        'automation_id',
        'priority',
        'ttl',
        'sound',
        'badge',
        'metadata',
    ];

    protected $casts = [
        'data' => 'array',
        'recipients_data' => 'array',
        'firebase_response' => 'array',
        'metadata' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'success_count' => 'integer',
        'failure_count' => 'integer',
        'read_count' => 'integer',
        'click_count' => 'integer',
        'ttl' => 'integer',
        'badge' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'data' => '[]',
        'recipients_data' => '[]',
        'firebase_response' => '[]',
        'metadata' => '[]',
        'status' => 'pending',
        'success_count' => 0,
        'failure_count' => 0,
        'read_count' => 0,
        'click_count' => 0,
        'priority' => 'normal',
    ];

    /**
     * Relation avec l'utilisateur qui a envoyé
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Relation avec la campagne
     */
    public function campaign()
    {
        return $this->belongsTo(FirebaseCampaign::class, 'campaign_id');
    }

    /**
     * Relation avec le template
     */
    public function template()
    {
        return $this->belongsTo(FirebaseTemplate::class, 'template_id');
    }

    /**
     * Relation avec l'automation
     */
    public function automation()
    {
        return $this->belongsTo(FirebaseAutomation::class, 'automation_id');
    }

    /**
     * Notifications en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Notifications programmées
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
            ->whereNotNull('scheduled_at');
    }

    /**
     * Notifications envoyées
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Notifications échouées
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Notifications récentes
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Notifications par type de destinataire
     */
    public function scopeByRecipientType($query, $type)
    {
        return $query->where('recipients_type', $type);
    }

    /**
     * Notifications par statut
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Marquer comme envoyée
     */
    public function markAsSent($response = null)
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'firebase_response' => $response ?? []
        ]);
    }

    /**
     * Marquer comme échouée
     */
    public function markAsFailed($response = null)
    {
        $this->update([
            'status' => 'failed',
            'firebase_response' => $response ?? []
        ]);
    }

    /**
     * Incrémenter le compteur de succès
     */
    public function incrementSuccess($count = 1)
    {
        $this->increment('success_count', $count);
    }

    /**
     * Incrémenter le compteur d'échecs
     */
    public function incrementFailure($count = 1)
    {
        $this->increment('failure_count', $count);
    }

    /**
     * Incrémenter le compteur de lectures
     */
    public function incrementRead($count = 1)
    {
        $this->increment('read_count', $count);
    }

    /**
     * Incrémenter le compteur de clics
     */
    public function incrementClick($count = 1)
    {
        $this->increment('click_count', $count);
    }

    /**
     * Obtenir le taux de réussite
     */
    public function getSuccessRateAttribute()
    {
        $total = $this->success_count + $this->failure_count;
        return $total > 0 ? round(($this->success_count / $total) * 100, 2) : 0;
    }

    /**
     * Obtenir le taux de lecture
     */
    public function getReadRateAttribute()
    {
        return $this->success_count > 0 ? round(($this->read_count / $this->success_count) * 100, 2) : 0;
    }

    /**
     * Obtenir le taux de clic
     */
    public function getClickRateAttribute()
    {
        return $this->read_count > 0 ? round(($this->click_count / $this->read_count) * 100, 2) : 0;
    }

    /**
     * Vérifier si la notification est programmée
     */
    public function isScheduled()
    {
        return $this->status === 'scheduled' && $this->scheduled_at && $this->scheduled_at->isFuture();
    }

    /**
     * Vérifier si la notification est prête à être envoyée
     */
    public function isReadyToSend()
    {
        return $this->status === 'scheduled' && $this->scheduled_at && $this->scheduled_at->isPast();
    }

    /**
     * Obtenir les destinataires sous forme de texte
     */
    public function getRecipientsDescriptionAttribute()
    {
        switch ($this->recipients_type) {
            case 'users':
                $count = count($this->recipients_data['values'] ?? []);
                return "{$count} utilisateur(s)";
            case 'devices':
                $count = count($this->recipients_data['values'] ?? []);
                return "{$count} appareil(s)";
            case 'topics':
                $topics = implode(', ', $this->recipients_data['values'] ?? []);
                return "Topics: {$topics}";
            case 'all':
                return "Tous les utilisateurs";
            default:
                return "Inconnu";
        }
    }

    /**
     * Obtenir l'icône du statut
     */
    public function getStatusIconAttribute()
    {
        return match($this->status) {
            'pending' => 'fas fa-clock text-warning',
            'scheduled' => 'fas fa-calendar-alt text-info',
            'sent' => 'fas fa-check-circle text-success',
            'failed' => 'fas fa-times-circle text-danger',
            'cancelled' => 'fas fa-ban text-muted',
            default => 'fas fa-question-circle text-secondary'
        };
    }

    /**
     * Obtenir la couleur du statut
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'scheduled' => 'info',
            'sent' => 'success',
            'failed' => 'danger',
            'cancelled' => 'secondary',
            default => 'light'
        };
    }

    /**
     * Nettoyer les notifications anciennes
     */
    public static function cleanupOldNotifications($days = 90)
    {
        return static::where('created_at', '<', now()->subDays($days))
            ->where('status', '!=', 'scheduled')
            ->delete();
    }

    /**
     * Obtenir les notifications à envoyer
     */
    public static function getNotificationsToSend()
    {
        return static::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();
    }

    /**
     * Statistiques des notifications
     */
    public static function getStats($days = 30)
    {
        $query = static::where('created_at', '>=', now()->subDays($days));

        return [
            'total' => $query->count(),
            'sent' => $query->where('status', 'sent')->count(),
            'failed' => $query->where('status', 'failed')->count(),
            'scheduled' => $query->where('status', 'scheduled')->count(),
            'success_rate' => static::getOverallSuccessRate($days),
            'by_type' => static::selectRaw('recipients_type, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('recipients_type')
                ->pluck('count', 'recipients_type')
                ->toArray(),
            'by_status' => static::selectRaw('status, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
        ];
    }

    /**
     * Obtenir le taux de réussite global
     */
    public static function getOverallSuccessRate($days = 30)
    {
        $total = static::where('created_at', '>=', now()->subDays($days))
            ->sum('success_count') + static::where('created_at', '>=', now()->subDays($days))
            ->sum('failure_count');

        $success = static::where('created_at', '>=', now()->subDays($days))
            ->sum('success_count');

        return $total > 0 ? round(($success / $total) * 100, 2) : 0;
    }

    /**
     * Obtenir les métriques d'engagement
     */
    public static function getEngagementMetrics($days = 30)
    {
        $notifications = static::where('created_at', '>=', now()->subDays($days))
            ->where('status', 'sent')
            ->get();

        $totalSent = $notifications->sum('success_count');
        $totalRead = $notifications->sum('read_count');
        $totalClicks = $notifications->sum('click_count');

        return [
            'sent' => $totalSent,
            'read' => $totalRead,
            'clicks' => $totalClicks,
            'read_rate' => $totalSent > 0 ? round(($totalRead / $totalSent) * 100, 2) : 0,
            'click_rate' => $totalRead > 0 ? round(($totalClicks / $totalRead) * 100, 2) : 0,
            'ctr' => $totalSent > 0 ? round(($totalClicks / $totalSent) * 100, 2) : 0,
        ];
    }
}

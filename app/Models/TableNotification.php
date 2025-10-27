<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'table_id',
        'order_id',
        'user_id',
        'type',
        'title',
        'message',
        'status',
        'priority',
        'resolved_at',
        'resolved_by',
        'data',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'data' => 'array',
    ];

    /**
     * Types de notifications possibles
     */
    const TYPE_NEW_ORDER = 'new_order';
    const TYPE_CALL_WAITER = 'call_waiter';
    const TYPE_REQUEST_BILL = 'request_bill';
    const TYPE_CUSTOMER_FEEDBACK = 'customer_feedback';
    const TYPE_TABLE_ISSUE = 'table_issue';
    const TYPE_PAYMENT_REQUEST = 'payment_request';

    /**
     * Statuts possibles
     */
    const STATUS_UNREAD = 'unread';
    const STATUS_SEEN = 'seen';
    const STATUS_RESOLVED = 'resolved';

    /**
     * Niveaux de priorité
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Restaurant auquel appartient cette notification
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Table concernée par cette notification
     */
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Commande liée à cette notification (optionnel)
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Utilisateur qui a résolu la notification
     */
    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Utilisateur qui a créé la notification (si applicable)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour les notifications non lues
     */
    public function scopeUnread($query)
    {
        return $query->where('status', self::STATUS_UNREAD);
    }

    /**
     * Scope pour les notifications vues mais non résolues
     */
    public function scopeSeen($query)
    {
        return $query->where('status', self::STATUS_SEEN);
    }

    /**
     * Scope pour les notifications résolues
     */
    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    /**
     * Scope pour les notifications non résolues
     */
    public function scopeUnresolved($query)
    {
        return $query->whereIn('status', [self::STATUS_UNREAD, self::STATUS_SEEN]);
    }

    /**
     * Scope pour les notifications par type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour les notifications par priorité
     */
    public function scopeWithPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope pour les notifications urgentes
     */
    public function scopeUrgent($query)
    {
        return $query->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_URGENT]);
    }

    /**
     * Marquer comme vue
     */
    public function markAsSeen()
    {
        if ($this->status === self::STATUS_UNREAD) {
            $this->update(['status' => self::STATUS_SEEN]);
        }
    }

    /**
     * Marquer comme résolue
     */
    public function markAsResolved($userId = null)
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'resolved_at' => now(),
            'resolved_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Vérifier si la notification est résolue
     */
    public function isResolved()
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    /**
     * Vérifier si la notification est urgente
     */
    public function isUrgent()
    {
        return in_array($this->priority, [self::PRIORITY_HIGH, self::PRIORITY_URGENT]);
    }

    /**
     * Obtenir la couleur associée au type de notification
     */
    public function getTypeColor()
    {
        return match($this->type) {
            self::TYPE_NEW_ORDER => 'blue',
            self::TYPE_CALL_WAITER => 'orange',
            self::TYPE_REQUEST_BILL => 'green',
            self::TYPE_CUSTOMER_FEEDBACK => 'purple',
            self::TYPE_TABLE_ISSUE => 'red',
            self::TYPE_PAYMENT_REQUEST => 'yellow',
            default => 'gray',
        };
    }

    /**
     * Obtenir la couleur associée à la priorité
     */
    public function getPriorityColor()
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'gray',
            self::PRIORITY_NORMAL => 'blue',
            self::PRIORITY_HIGH => 'orange',
            self::PRIORITY_URGENT => 'red',
            default => 'gray',
        };
    }

    /**
     * Obtenir le libellé du type de notification
     */
    public function getTypeLabel()
    {
        return match($this->type) {
            self::TYPE_NEW_ORDER => 'Nouvelle commande',
            self::TYPE_CALL_WAITER => 'Appel serveur',
            self::TYPE_REQUEST_BILL => 'Demande d\'addition',
            self::TYPE_CUSTOMER_FEEDBACK => 'Commentaire client',
            self::TYPE_TABLE_ISSUE => 'Problème table',
            self::TYPE_PAYMENT_REQUEST => 'Demande de paiement',
            default => 'Notification',
        };
    }

    /**
     * Obtenir le libellé de la priorité
     */
    public function getPriorityLabel()
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'Faible',
            self::PRIORITY_NORMAL => 'Normale',
            self::PRIORITY_HIGH => 'Élevée',
            self::PRIORITY_URGENT => 'Urgente',
            default => 'Normale',
        };
    }

    /**
     * Format pour l'affichage
     */
    public function toDisplayArray()
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'type_label' => $this->getTypeLabel(),
            'type_color' => $this->getTypeColor(),
            'title' => $this->title,
            'message' => $this->message,
            'status' => $this->status,
            'priority' => $this->priority,
            'priority_label' => $this->getPriorityLabel(),
            'priority_color' => $this->getPriorityColor(),
            'table' => [
                'id' => $this->table->id,
                'number' => $this->table->table_number,
                'name' => $this->table->name,
            ],
            'order_id' => $this->order_id,
            'is_urgent' => $this->isUrgent(),
            'is_resolved' => $this->isResolved(),
            'created_at' => $this->created_at,
            'created_at_human' => $this->created_at->diffForHumans(),
            'resolved_at' => $this->resolved_at,
            'resolved_by_user' => $this->resolvedBy?->name,
        ];
    }

    /**
     * Créer une notification pour une nouvelle commande
     */
    public static function createForNewOrder($order)
    {
        return static::create([
            'restaurant_id' => $order->restaurant_id,
            'table_id' => $order->table_id,
            'order_id' => $order->id,
            'type' => self::TYPE_NEW_ORDER,
            'title' => 'Nouvelle commande',
            'message' => "Nouvelle commande #{$order->order_number} de la table {$order->table->table_number}",
            'status' => self::STATUS_UNREAD,
            'priority' => self::PRIORITY_NORMAL,
            'data' => [
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
            ],
        ]);
    }

    /**
     * Créer une notification pour appel serveur
     */
    public static function createForWaiterCall($table, $message = null)
    {
        return static::create([
            'restaurant_id' => $table->restaurant_id,
            'table_id' => $table->id,
            'type' => self::TYPE_CALL_WAITER,
            'title' => 'Appel serveur',
            'message' => $message ?? "La table {$table->table_number} demande un serveur",
            'status' => self::STATUS_UNREAD,
            'priority' => self::PRIORITY_HIGH,
        ]);
    }

    /**
     * Créer une notification pour demande d'addition
     */
    public static function createForBillRequest($table, $message = null)
    {
        return static::create([
            'restaurant_id' => $table->restaurant_id,
            'table_id' => $table->id,
            'type' => self::TYPE_REQUEST_BILL,
            'title' => 'Demande d\'addition',
            'message' => $message ?? "La table {$table->table_number} demande l'addition",
            'status' => self::STATUS_UNREAD,
            'priority' => self::PRIORITY_NORMAL,
        ]);
    }

    /**
     * Créer une notification pour un commentaire client
     */
    public static function createForCustomerFeedback($table, $rating, $comment = null)
    {
        return static::create([
            'restaurant_id' => $table->restaurant_id,
            'table_id' => $table->id,
            'type' => self::TYPE_CUSTOMER_FEEDBACK,
            'title' => 'Évaluation client',
            'message' => "La table {$table->table_number} a laissé une note de {$rating}/5",
            'status' => self::STATUS_UNREAD,
            'priority' => $rating <= 2 ? self::PRIORITY_HIGH : self::PRIORITY_LOW,
            'data' => [
                'rating' => $rating,
                'comment' => $comment,
            ],
        ]);
    }

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        // Définir la priorité par défaut
        static::creating(function ($notification) {
            if (empty($notification->priority)) {
                $notification->priority = self::PRIORITY_NORMAL;
            }
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'table_number',
        'name',
        'capacity',
        'location',
        'table_code',
        'qr_code_path',
        'status',
        'last_accessed',
    ];

    protected $casts = [
        'last_accessed' => 'datetime',
        'capacity' => 'integer',
    ];

    protected $appends = [
        'qr_url',
        'is_occupied',
        'current_orders_count',
    ];

    /**
     * Status possibles pour une table
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_OCCUPIED = 'occupied';
    const STATUS_FREE = 'free';

    /**
     * Restaurant auquel appartient cette table
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Commandes de cette table
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Commandes actives de cette table
     */
    public function currentOrders()
    {
        return $this->hasMany(Order::class)
            ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready']);
    }

    /**
     * Notifications liées à cette table
     */
    public function notifications()
    {
        return $this->hasMany(TableNotification::class);
    }

    /**
     * Évaluations de cette table
     */
    public function ratings()
    {
        return $this->hasMany(TableRating::class);
    }

    /**
     * URL du QR code pour accéder au menu
     */
    public function getQrUrlAttribute()
    {
        if (!$this->restaurant) {
            return null;
        }

        return route('table.menu', [
            'restaurant_slug' => $this->restaurant->slug,
            'table_code' => $this->table_code
        ]);
    }

    /**
     * Vérifier si la table est actuellement occupée
     */
    public function getIsOccupiedAttribute()
    {
        return $this->currentOrders()->exists();
    }

    /**
     * Nombre de commandes actives
     */
    public function getCurrentOrdersCountAttribute()
    {
        return $this->currentOrders()->count();
    }

    /**
     * Générer un code unique pour la table
     */
    public static function generateUniqueCode()
    {
        do {
            $code = strtoupper(\Illuminate\Support\Str::random(8));
        } while (static::where('table_code', $code)->exists());

        return $code;
    }

    /**
     * Scope pour les tables actives
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope pour les tables disponibles
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereDoesntHave('currentOrders');
    }

    /**
     * Scope pour les tables occupées
     */
    public function scopeOccupied($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereHas('currentOrders');
    }

    /**
     * Mettre à jour le timestamp de dernier accès
     */
    public function updateLastAccessed()
    {
        $this->update(['last_accessed' => now()]);
    }

    /**
     * Obtenir le statut d'occupation en temps réel
     */
    public function getRealTimeStatus()
    {
        $activeOrders = $this->currentOrders()->count();
        $unreadNotifications = $this->notifications()
            ->where('status', 'unread')
            ->count();

        return [
            'table_number' => $this->table_number,
            'status' => $this->status,
            'is_occupied' => $activeOrders > 0,
            'active_orders' => $activeOrders,
            'unread_notifications' => $unreadNotifications,
            'last_activity' => $this->last_accessed?->diffForHumans(),
        ];
    }

    /**
     * Statistiques de la table pour une période donnée
     */
    public function getStatsForPeriod($startDate, $endDate)
    {
        $orders = $this->orders()
            ->whereBetween('created_at', [$startDate, $endDate]);

        $completedOrders = (clone $orders)->where('status', 'completed');

        return [
            'total_orders' => $orders->count(),
            'completed_orders' => $completedOrders->count(),
            'total_revenue' => $completedOrders->sum('total_amount'),
            'avg_order_value' => $completedOrders->avg('total_amount') ?? 0,
            'avg_order_time' => $completedOrders
                ->whereNotNull('completed_at')
                ->avg(\DB::raw('TIMESTAMPDIFF(MINUTE, created_at, completed_at)')) ?? 0,
        ];
    }

    /**
     * Obtenir la note moyenne de la table
     */
    public function getAverageRating()
    {
        return $this->ratings()->avg('rating') ?? 0;
    }

    /**
     * Vérifier si la table peut accepter de nouvelles commandes
     */
    public function canAcceptOrders()
    {
        return $this->status === self::STATUS_ACTIVE &&
               $this->restaurant &&
               $this->restaurant->status === 'active';
    }

    /**
     * Obtenir les notifications non résolues
     */
    public function getUnresolvedNotifications()
    {
        return $this->notifications()
            ->whereIn('status', ['unread', 'seen'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Marquer la table comme libre (plus de commandes actives)
     */
    public function markAsFree()
    {
        if (!$this->currentOrders()->exists()) {
            $this->update(['status' => self::STATUS_FREE]);
        }
    }

    /**
     * Marquer la table comme occupée (nouvelles commandes)
     */
    public function markAsOccupied()
    {
        if ($this->status === self::STATUS_FREE) {
            $this->update(['status' => self::STATUS_OCCUPIED]);
        }
    }

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        // Générer un code unique lors de la création
        static::creating(function ($table) {
            if (empty($table->table_code)) {
                $table->table_code = static::generateUniqueCode();
            }
        });
    }
}

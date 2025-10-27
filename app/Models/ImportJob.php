<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportJob extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'user_id',
        'type',
        'status',
        'file_path',
        'original_filename',
        'total_rows',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'errors',
        'settings',
        'started_at',
        'completed_at',
        'scheduled_at',
    ];

    protected $casts = [
        'errors' => 'array',
        'settings' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    /**
     * Relation avec le restaurant
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour les jobs en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope pour les jobs programmés
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now());
    }

    /**
     * Calculer le pourcentage de progression
     */
    public function getProgressPercentageAttribute()
    {
        if ($this->total_rows == 0) {
            return 0;
        }

        return round(($this->processed_rows / $this->total_rows) * 100, 2);
    }

    /**
     * Vérifier si le job est terminé
     */
    public function getIsCompletedAttribute()
    {
        return in_array($this->status, ['completed', 'failed', 'cancelled']);
    }

    /**
     * Obtenir le taux de succès
     */
    public function getSuccessRateAttribute()
    {
        if ($this->processed_rows == 0) {
            return 0;
        }

        return round(($this->successful_rows / $this->processed_rows) * 100, 2);
    }
}

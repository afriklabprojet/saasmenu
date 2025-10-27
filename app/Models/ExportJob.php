<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExportJob extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'user_id',
        'type',
        'status',
        'file_path',
        'filename',
        'format',
        'total_rows',
        'processed_rows',
        'filters',
        'settings',
        'started_at',
        'completed_at',
        'scheduled_at',
        'download_count',
        'expires_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'settings' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'expires_at' => 'datetime',
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
     * Scope pour les exports non expirés
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
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
     * Vérifier si le fichier est disponible au téléchargement
     */
    public function getIsDownloadableAttribute()
    {
        return $this->status === 'completed' 
            && $this->file_path
            && (!$this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Obtenir l'URL de téléchargement
     */
    public function getDownloadUrlAttribute()
    {
        if (!$this->is_downloadable) {
            return null;
        }

        return route('admin.import-export.download', $this->id);
    }
}
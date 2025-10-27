<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportExportJob extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'import_export_jobs';

    protected $fillable = [
        'type',
        'data_type',
        'file_path',
        'export_file_path',
        'status',
        'format',
        'mapping',
        'filters',
        'options',
        'user_id',
        'total_records',
        'processed_records',
        'successful_records',
        'failed_records',
        'errors',
        'warnings',
        'started_at',
        'completed_at',
        'progress',
        'estimated_completion',
        'file_size',
        'memory_usage',
        'execution_time',
        'metadata',
    ];

    protected $casts = [
        'mapping' => 'array',
        'filters' => 'array',
        'options' => 'array',
        'errors' => 'array',
        'warnings' => 'array',
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'estimated_completion' => 'datetime',
        'progress' => 'decimal:2',
        'total_records' => 'integer',
        'processed_records' => 'integer',
        'successful_records' => 'integer',
        'failed_records' => 'integer',
        'file_size' => 'integer',
        'memory_usage' => 'integer',
        'execution_time' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'mapping' => '[]',
        'filters' => '[]',
        'options' => '[]',
        'errors' => '[]',
        'warnings' => '[]',
        'metadata' => '[]',
        'status' => 'pending',
        'progress' => 0,
        'total_records' => 0,
        'processed_records' => 0,
        'successful_records' => 0,
        'failed_records' => 0,
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Jobs en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Jobs en cours de traitement
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Jobs terminés
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Jobs échoués
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Jobs d'import
     */
    public function scopeImports($query)
    {
        return $query->where('type', 'import');
    }

    /**
     * Jobs d'export
     */
    public function scopeExports($query)
    {
        return $query->where('type', 'export');
    }

    /**
     * Jobs par type de données
     */
    public function scopeByDataType($query, $dataType)
    {
        return $query->where('data_type', $dataType);
    }

    /**
     * Jobs récents
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Démarrer le job
     */
    public function start()
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    /**
     * Marquer comme terminé
     */
    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress' => 100,
        ]);
    }

    /**
     * Marquer comme échoué
     */
    public function fail($error = null)
    {
        $errors = $this->errors ?? [];
        if ($error) {
            $errors[] = $error;
        }

        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'errors' => $errors,
        ]);
    }

    /**
     * Annuler le job
     */
    public function cancel()
    {
        $this->update([
            'status' => 'cancelled',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mettre à jour le progrès
     */
    public function updateProgress($processed, $successful = null, $failed = null)
    {
        $data = [
            'processed_records' => $processed,
        ];

        if ($this->total_records > 0) {
            $data['progress'] = ($processed / $this->total_records) * 100;
        }

        if ($successful !== null) {
            $data['successful_records'] = $successful;
        }

        if ($failed !== null) {
            $data['failed_records'] = $failed;
        }

        $this->update($data);
    }

    /**
     * Ajouter une erreur
     */
    public function addError($error)
    {
        $errors = $this->errors ?? [];
        $errors[] = [
            'message' => $error,
            'timestamp' => now()->toISOString(),
        ];

        $this->update(['errors' => $errors]);
    }

    /**
     * Ajouter un avertissement
     */
    public function addWarning($warning)
    {
        $warnings = $this->warnings ?? [];
        $warnings[] = [
            'message' => $warning,
            'timestamp' => now()->toISOString(),
        ];

        $this->update(['warnings' => $warnings]);
    }

    /**
     * Obtenir le taux de réussite
     */
    public function getSuccessRateAttribute()
    {
        if ($this->processed_records === 0) {
            return 0;
        }

        return round(($this->successful_records / $this->processed_records) * 100, 2);
    }

    /**
     * Obtenir le taux d'échec
     */
    public function getFailureRateAttribute()
    {
        if ($this->processed_records === 0) {
            return 0;
        }

        return round(($this->failed_records / $this->processed_records) * 100, 2);
    }

    /**
     * Vérifier si le job peut être annulé
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Vérifier si le job peut être relancé
     */
    public function canBeRetried()
    {
        return in_array($this->status, ['failed', 'cancelled']);
    }

    /**
     * Obtenir la durée d'exécution
     */
    public function getDurationAttribute()
    {
        if (!$this->started_at) {
            return null;
        }

        $endTime = $this->completed_at ?? now();
        return $this->started_at->diffInSeconds($endTime);
    }

    /**
     * Obtenir le temps estimé de completion
     */
    public function getEstimatedCompletionTimeAttribute()
    {
        if ($this->status !== 'processing' || $this->progress <= 0) {
            return null;
        }

        $elapsedTime = $this->started_at->diffInSeconds(now());
        $estimatedTotal = ($elapsedTime / $this->progress) * 100;
        $remaining = $estimatedTotal - $elapsedTime;

        return now()->addSeconds($remaining);
    }

    /**
     * Obtenir l'icône du statut
     */
    public function getStatusIconAttribute()
    {
        return match($this->status) {
            'pending' => 'fas fa-clock text-warning',
            'processing' => 'fas fa-spinner fa-spin text-info',
            'completed' => 'fas fa-check-circle text-success',
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
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'secondary',
            default => 'light'
        };
    }

    /**
     * Obtenir le label du type
     */
    public function getTypeLabdattribute()
    {
        return match($this->type) {
            'import' => 'Import',
            'export' => 'Export',
            default => 'Inconnu'
        };
    }

    /**
     * Obtenir le label du type de données
     */
    public function getDataTypeLabel()
    {
        return match($this->data_type) {
            'menus' => 'Menus',
            'products' => 'Produits',
            'customers' => 'Clients',
            'orders' => 'Commandes',
            'categories' => 'Catégories',
            'restaurants' => 'Restaurants',
            'coupons' => 'Coupons',
            'inventory' => 'Inventaire',
            default => ucfirst($this->data_type ?? 'Inconnu')
        };
    }

    /**
     * Nettoyer les anciens jobs
     */
    public static function cleanupOldJobs($days = 30)
    {
        return static::where('created_at', '<', now()->subDays($days))
            ->whereIn('status', ['completed', 'failed', 'cancelled'])
            ->delete();
    }

    /**
     * Obtenir les statistiques
     */
    public static function getStats($days = 30)
    {
        $query = static::where('created_at', '>=', now()->subDays($days));

        return [
            'total' => $query->count(),
            'imports' => $query->where('type', 'import')->count(),
            'exports' => $query->where('type', 'export')->count(),
            'completed' => $query->where('status', 'completed')->count(),
            'failed' => $query->where('status', 'failed')->count(),
            'pending' => $query->where('status', 'pending')->count(),
            'processing' => $query->where('status', 'processing')->count(),
            'by_data_type' => static::selectRaw('data_type, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('data_type')
                ->pluck('count', 'data_type')
                ->toArray(),
            'success_rate' => static::getOverallSuccessRate($days),
        ];
    }

    /**
     * Obtenir le taux de réussite global
     */
    public static function getOverallSuccessRate($days = 30)
    {
        $completed = static::where('created_at', '>=', now()->subDays($days))
            ->whereIn('status', ['completed', 'failed'])
            ->count();

        if ($completed === 0) {
            return 0;
        }

        $successful = static::where('created_at', '>=', now()->subDays($days))
            ->where('status', 'completed')
            ->count();

        return round(($successful / $completed) * 100, 2);
    }
}

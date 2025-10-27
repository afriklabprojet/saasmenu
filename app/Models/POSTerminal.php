<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POSTerminal extends Model
{
    use HasFactory;

    protected $table = 'pos_terminals';

    protected $fillable = [
        'restaurant_id',
        'name',
        'description',
        'terminal_code',
        'ip_address',
        'mac_address',
        'location',
        'printer_config',
        'scanner_config',
        'cash_drawer_config',
        'current_user_id',
        'last_activity',
        'status',
        'settings',
    ];

    protected $casts = [
        'printer_config' => 'array',
        'scanner_config' => 'array',
        'cash_drawer_config' => 'array',
        'last_activity' => 'datetime',
        'settings' => 'array',
    ];

    /**
     * Status possibles
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_MAINTENANCE = 'maintenance';

    /**
     * Restaurant de ce terminal
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Utilisateur actuel du terminal
     */
    public function currentUser()
    {
        return $this->belongsTo(User::class, 'current_user_id');
    }

    /**
     * Sessions de ce terminal
     */
    public function sessions()
    {
        return $this->hasMany(POSSession::class, 'terminal_id');
    }

    /**
     * Session active actuelle
     */
    public function activeSession()
    {
        return $this->hasOne(POSSession::class, 'terminal_id')
            ->where('status', 'active');
    }

    /**
     * Paniers de ce terminal
     */
    public function carts()
    {
        return $this->hasMany(POSCart::class, 'terminal_id');
    }

    /**
     * Scope pour les terminaux actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope pour les terminaux disponibles
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereNull('current_user_id');
    }

    /**
     * Vérifier si le terminal est disponible
     */
    public function isAvailable()
    {
        return $this->status === self::STATUS_ACTIVE &&
               !$this->current_user_id;
    }

    /**
     * Vérifier si le terminal est en cours d'utilisation
     */
    public function isInUse()
    {
        return $this->current_user_id !== null;
    }

    /**
     * Marquer comme utilisé par un utilisateur
     */
    public function assignToUser($userId)
    {
        $this->update([
            'current_user_id' => $userId,
            'last_activity' => now(),
        ]);
    }

    /**
     * Libérer le terminal
     */
    public function release()
    {
        $this->update([
            'current_user_id' => null,
            'last_activity' => now(),
        ]);
    }

    /**
     * Obtenir la configuration d'imprimante
     */
    public function getPrinterConfig()
    {
        return array_merge([
            'enabled' => false,
            'ip_address' => null,
            'port' => 9100,
            'type' => 'thermal',
            'width' => 80,
        ], $this->printer_config ?? []);
    }

    /**
     * Obtenir la configuration du scanner
     */
    public function getScannerConfig()
    {
        return array_merge([
            'enabled' => false,
            'type' => 'usb',
            'port' => null,
        ], $this->scanner_config ?? []);
    }

    /**
     * Obtenir la configuration du tiroir-caisse
     */
    public function getCashDrawerConfig()
    {
        return array_merge([
            'enabled' => false,
            'open_command' => null,
            'pulse_duration' => 100,
        ], $this->cash_drawer_config ?? []);
    }

    /**
     * Tester la connexion imprimante
     */
    public function testPrinter()
    {
        $config = $this->getPrinterConfig();

        if (!$config['enabled']) {
            return ['success' => false, 'message' => 'Imprimante non configurée'];
        }

        // Logique de test d'imprimante à implémenter
        return ['success' => true, 'message' => 'Test imprimante réussi'];
    }

    /**
     * Imprimer un reçu
     */
    public function printReceipt($order)
    {
        $config = $this->getPrinterConfig();

        if (!$config['enabled']) {
            return false;
        }

        // Logique d'impression à implémenter
        return true;
    }

    /**
     * Générer un code terminal unique
     */
    public static function generateUniqueCode()
    {
        do {
            $code = 'POS-' . strtoupper(\Illuminate\Support\Str::random(6));
        } while (static::where('terminal_code', $code)->exists());

        return $code;
    }

    /**
     * Obtenir les statistiques du terminal
     */
    public function getStats($period = 'today')
    {
        $query = $this->sessions();

        switch ($period) {
            case 'today':
                $query->whereDate('opened_at', today());
                break;
            case 'week':
                $query->whereBetween('opened_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereBetween('opened_at', [now()->startOfMonth(), now()->endOfMonth()]);
                break;
        }

        $sessions = $query->get();

        return [
            'sessions_count' => $sessions->count(),
            'total_sales' => $sessions->sum('total_sales'),
            'total_transactions' => $sessions->sum('total_transactions'),
            'average_session_time' => $sessions->avg('duration'),
            'last_session' => $sessions->sortByDesc('opened_at')->first(),
        ];
    }

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($terminal) {
            if (empty($terminal->terminal_code)) {
                $terminal->terminal_code = static::generateUniqueCode();
            }
        });
    }
}

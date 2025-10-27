<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle pour logger les messages WhatsApp envoyés
 *
 * @property int $id
 * @property string $to Numéro destinataire
 * @property string $message Contenu du message
 * @property string $status Statut de l'envoi
 * @property bool $success Succès ou échec
 * @property string|null $message_id ID WhatsApp du message
 * @property json|null $response Réponse de l'API
 * @property string|null $context Contexte additionnel
 * @property \Illuminate\Support\Carbon $sent_at Date d'envoi
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class WhatsAppLog extends Model
{
    use HasFactory;

    /**
     * Nom de la table
     */
    protected $table = 'whatsapp_logs';

    /**
     * Les attributs qui sont assignables en masse
     */
    protected $fillable = [
        'to',
        'message',
        'status',
        'success',
        'message_id',
        'response',
        'context',
        'sent_at',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'success' => 'boolean',
        'response' => 'array',
        'context' => 'array',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope pour les messages réussis
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope pour les messages échoués
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope pour une période donnée
     */
    public function scopeInPeriod($query, $startDate, $endDate = null)
    {
        $query->where('created_at', '>=', $startDate);

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Obtenir un résumé du message (100 premiers caractères)
     */
    public function getMessagePreviewAttribute(): string
    {
        return strlen($this->message) > 100
            ? substr($this->message, 0, 97) . '...'
            : $this->message;
    }
}

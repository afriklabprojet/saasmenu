<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableQrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'table_id',
        'qr_code',
        'qr_image_path',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Relations
     */

    /**
     * Restaurant
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Table associée
     */
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Scopes
     */

    /**
     * QR codes actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Accesseurs
     */

    /**
     * URL complète du QR code
     */
    public function getQrUrlAttribute()
    {
        return route('table.menu', ['restaurant' => $this->restaurant->restaurant_slug, 'table' => $this->table->id, 'qr' => $this->qr_code]);
    }

    /**
     * URL de l'image QR
     */
    public function getQrImageUrlAttribute()
    {
        return $this->qr_image_path ? asset('storage/' . $this->qr_image_path) : null;
    }

    /**
     * Méthodes utilitaires
     */

    /**
     * Générer le QR code
     */
    public function generateQrCode()
    {
        if (!$this->qr_code) {
            $this->qr_code = 'qr_' . $this->restaurant_id . '_' . $this->table_id . '_' . now()->timestamp;
        }

        $qrUrl = $this->qr_url;

        // Générer l'image QR
        $qrImage = QrCode::format('png')
            ->size(300)
            ->generate($qrUrl);

        // Sauvegarder l'image
        $fileName = 'qrcodes/table_' . $this->table_id . '_' . time() . '.png';
        \Storage::disk('public')->put($fileName, $qrImage);

        $this->qr_image_path = $fileName;
        $this->save();

        return $this;
    }

    /**
     * Régénérer le QR code
     */
    public function regenerateQrCode()
    {
        // Supprimer l'ancienne image si elle existe
        if ($this->qr_image_path) {
            \Storage::disk('public')->delete($this->qr_image_path);
        }

        // Générer un nouveau code
        $this->qr_code = null;
        $this->qr_image_path = null;

        return $this->generateQrCode();
    }

    /**
     * Télécharger le QR code
     */
    public function downloadQrCode()
    {
        if (!$this->qr_image_path) {
            $this->generateQrCode();
        }

        return response()->download(storage_path('app/public/' . $this->qr_image_path));
    }
}

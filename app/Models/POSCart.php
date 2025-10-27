<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POSCart extends Model
{
    use HasFactory;

    protected $table = 'pos_cart_items';

    protected $fillable = [
        'terminal_id',
        'user_id',
        'session_id',
        'menu_item_id',
        'quantity',
        'unit_price',
        'total_price',
        'modifiers',
        'special_instructions',
        'created_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'modifiers' => 'array',
    ];

    /**
     * Terminal associé
     */
    public function terminal()
    {
        return $this->belongsTo(POSTerminal::class, 'terminal_id');
    }

    /**
     * Utilisateur qui a ajouté l'article
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Session POS associée
     */
    public function session()
    {
        return $this->belongsTo(POSSession::class, 'session_id');
    }

    /**
     * Article du menu
     */
    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }

    /**
     * Calculer le prix total avec modificateurs
     */
    public function calculateTotalPrice()
    {
        $basePrice = $this->unit_price * $this->quantity;
        $modifiersPrice = 0;

        if ($this->modifiers) {
            foreach ($this->modifiers as $modifier) {
                $modifiersPrice += ($modifier['price'] ?? 0) * $this->quantity;
            }
        }

        return $basePrice + $modifiersPrice;
    }

    /**
     * Mettre à jour le prix total
     */
    public function updateTotalPrice()
    {
        $this->update([
            'total_price' => $this->calculateTotalPrice()
        ]);
    }

    /**
     * Obtenir les modificateurs formatés
     */
    public function getFormattedModifiers()
    {
        if (!$this->modifiers) {
            return [];
        }

        return collect($this->modifiers)->map(function($modifier) {
            return [
                'name' => $modifier['name'] ?? '',
                'price' => $modifier['price'] ?? 0,
                'formatted_price' => number_format($modifier['price'] ?? 0, 2) . '€',
            ];
        });
    }

    /**
     * Format pour l'affichage
     */
    public function toDisplayArray()
    {
        return [
            'id' => $this->id,
            'menu_item' => [
                'id' => $this->menuItem->id,
                'name' => $this->menuItem->name,
                'image_url' => $this->menuItem->image_url,
            ],
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'modifiers' => $this->getFormattedModifiers(),
            'special_instructions' => $this->special_instructions,
            'created_at' => $this->created_at,
        ];
    }

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cartItem) {
            if (!$cartItem->unit_price && $cartItem->menuItem) {
                $cartItem->unit_price = $cartItem->menuItem->price;
            }

            $cartItem->total_price = $cartItem->calculateTotalPrice();
        });

        static::updating(function ($cartItem) {
            $cartItem->total_price = $cartItem->calculateTotalPrice();
        });
    }
}

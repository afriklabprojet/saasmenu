<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_id',
    ];

    /**
     * Relation avec le client (User)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le produit (Item)
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}

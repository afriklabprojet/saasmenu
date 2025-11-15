<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    use HasFactory;

    protected $table = 'extras';

    protected $fillable = [
        'item_id',
        'name',
        'price',
        'is_available',
        'reorder_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'reorder_id' => 'integer',
    ];

    /**
     * Get the item that owns the extra
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}

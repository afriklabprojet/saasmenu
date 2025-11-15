<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetails extends Model
{
    use HasFactory;

    protected $table = 'order_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'item_name',
        'item_price',
        'item_image',
        'qty',
        'price',
        'tax',
        'variants_id',
        'variants_name',
        'variants_price',
        'extras_id',
        'extras_name',
        'extras_price',
    ];
}

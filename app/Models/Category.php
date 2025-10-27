<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categories';

    protected $fillable = [
        'name',
        'vendor_id',
        'is_available',
        'is_deleted',
        'reorder_id',
        'description',
        'image',
        'slug'
    ];

}

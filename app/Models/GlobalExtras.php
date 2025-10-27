<?php

namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;



use Illuminate\Database\Eloquent\Model;



class GlobalExtras extends Model
{
    use HasFactory;

    protected $table = 'global_extras';

    protected $fillable = [
        'vendor_id',
        'name',
        'price',
        'reorder_id',
        'is_available'
    ];
}

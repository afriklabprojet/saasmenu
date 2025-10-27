<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotionalbanner extends Model
{
    use HasFactory;

    protected $table = 'promotionalbanner';

    protected $fillable = [
        'reorder_id',
        'vendor_id',
        'image'
    ];

    public function vendor_info()
    {
        return $this->hasOne('App\Models\User','id','vendor_id');
    }
}


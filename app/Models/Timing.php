<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timing extends Model
{
    use HasFactory;
    
    protected $table = 'timings';
    
    protected $fillable = [
        'vendor_id',
        'day',
        'open_time',
        'close_time',
        'is_closed',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
    ];

    /**
     * Get the vendor that owns the timing.
     */
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
}

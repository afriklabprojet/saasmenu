<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableBook extends Model
{
    use HasFactory;

    protected $table = "table_book";

    protected $fillable = [
        'vendor_id',
        'name',
        'email',
        'mobile',
        'total_members',
        'booking_date',
        'booking_time',
        'message',
        'status'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_number',
        'vendor_id',
        'service_id',
        'service_image',
        'service_name',
        'offer_code',
        'offer_amount',
        'booking_date',
        'booking_time',
        'address',
        'payment_status',
        'customer_name',
        'mobile',
        'email',
        'message',
        'sub_total',
        'tax',
        'grand_total',
        'transaction_id',
        'transaction_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'booking_date' => 'date',
        'booking_time' => 'datetime:H:i',
        'offer_amount' => 'float',
        'sub_total' => 'float',
        'tax' => 'float',
        'grand_total' => 'float',
        'payment_status' => 'integer',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'guests_count',
        'booking_date',
        'booking_time',
        'special_requests',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'booking_time' => 'datetime:H:i',
    ];

    /**
     * Get the vendor that owns the booking
     */
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get the user who made the booking (if registered)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope to get bookings for a specific vendor
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope to get bookings by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get upcoming bookings
     */
    public function scopeUpcoming($query)
    {
        return $query->where('booking_date', '>=', now()->toDateString())
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->orderBy('booking_date')
                    ->orderBy('booking_time');
    }

    /**
     * Check if this time slot is available
     */
    public static function isTimeSlotAvailable($vendorId, $date, $time, $excludeId = null)
    {
        $query = self::where('vendor_id', $vendorId)
                    ->where('booking_date', $date)
                    ->where('booking_time', $time)
                    ->whereIn('status', ['pending', 'confirmed']);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->count() < 5; // Max 5 bookings per time slot
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'confirmed' => 'success',
            'cancelled' => 'danger',
            'completed' => 'info',
            default => 'secondary'
        };
    }

    /**
     * Get formatted date time
     */
    public function getFormattedDateTimeAttribute()
    {
        return $this->booking_date->format('d/m/Y') . ' à ' . $this->booking_time->format('H:i');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Item extends Model
{
    use HasFactory;
    protected $table = 'items';

    /**
     * The attributes that are mass assignable.
     *
     * Security: Reduced from 19 to 14 fields
     * Price and availability fields moved to $guarded
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',              // Item name
        'category_id',       // Category assignment
        'cat_id',            // Category ID (legacy)
        'vendor_id',         // Which restaurant owns this
        'description',       // Item description
        'image',             // Item image
        'min_order',         // Minimum order quantity
        'max_order',         // Maximum order quantity
        'reorder_id',        // Display order
        'slug',              // SEO slug
        'sku',               // Stock keeping unit
        'stock_management',  // Enable/disable stock tracking
        'low_qty',           // Low stock alert threshold
        'tax',               // Tax ID/rate reference
    ];

    /**
     * The attributes that are NOT mass assignable.
     * These fields should only be modified through specific business logic.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
        'price',             // Security: Price should be validated and controlled
        'original_price',    // Security: Original price for discount calculation
        'is_available',      // Business logic: Availability should be controlled
        'is_featured',       // Business logic: Featured status (admin control)
        'qty',               // Business logic: Stock quantity should be updated via inventory management
        'created_at',
        'updated_at',
    ];

    public function extras()
    {
        return $this->hasMany('App\Models\Extra', 'item_id', 'id')->select('id', 'name', 'price', 'item_id');
    }
    public function variation(){
        return $this->hasMany('App\Models\Variants','item_id','id')->select('id','item_id','name','price','original_price','qty','min_order','max_order','is_available','stock_management');
    }
    public function category_info()
    {
        return $this->hasOne('App\Models\Category', 'id', 'cat_id');
    }

    // Single image (backward compatibility)
    public function item_image(){
        return $this->hasOne('App\Models\ItemImages','item_id','id')->select('*', DB::raw("CONCAT('".url('/storage/app/public/item/')."/', image) AS image_url"));
    }

    // Multiple images
    public function images(){
        return $this->hasMany('App\Models\ItemImages','item_id','id')->select('*', DB::raw("CONCAT('".url('/storage/app/public/item/')."/', image) AS image_url"));
    }

    public static function possibleVariants($groups, $prefix = '')
    {
        $result = [];
        $group  = array_shift($groups);
        foreach($group as $selected)
        {
            if($groups)
            {
                $result = array_merge($result, self::possibleVariants($groups, $prefix . $selected . '|'));
            }
            else
            {
                $result[] = $prefix . $selected;
            }
        }
        return $result;
    }
}

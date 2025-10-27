<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;



class PricingPlan extends Model

{

    use HasFactory;

    protected $table = 'pricing_plans';

    protected $fillable = [
        'name',
        'description',
        'features',
        'price',
        'duration',
        'service_limit',
        'appoinment_limit',
        'products_limit',
        'categories_limit',
        'staff_limit',
        'order_limit',
        'custom_domain',
        'whatsapp_integration',
        'analytics',
        'type',
        'themes_id',
        'is_available'
    ];

}


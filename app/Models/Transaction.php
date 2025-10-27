<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $table = 'transactions';

    protected $fillable = [
        'vendor_id',
        'plan_id',
        'themes_id',
        'amount',
        'payment_id',
        'payment_type',
        'purchase_date',
        'status',
        'expire_date',
        'service_limit',
        'appoinment_limit',
        'response'
    ];

    public function vendor_info(){
        return $this->hasOne('App\Models\User','id','vendor_id')->select('id','name','email','mobile');
    }
    public function plan_info()
    {
        return $this->hasOne('App\Models\PricingPlan','id','plan_id')->select('id','name','description','features');
    }

}

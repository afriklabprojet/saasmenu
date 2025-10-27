<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;



class Areas extends Model
{
    use HasFactory;
    protected $table = 'area';

    protected $fillable = [
        'area',
        'city_id',
        'description',
        'reorder_id',
        'is_available',
        'is_deleted'
    ];

    public function city_info()
    {
        return $this->hasOne('App\Models\City','id','city_id');
    }
}


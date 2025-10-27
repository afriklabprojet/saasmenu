<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;



class Pixcel extends Model

{

    use HasFactory;

    protected $table = 'pixcel_settings';

    protected $fillable = [
        'vendor_id',
        'facebook_pixcel_id',
        'twitter_pixcel_id',
        'linkedin_pixcel_id',
        'googletag_pixcel_id',
        'is_available'
    ];

}


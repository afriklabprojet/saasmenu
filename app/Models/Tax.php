<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Tax extends Model
{
    use HasFactory;
    protected $table = 'tax';

    protected $fillable = [
        'vendor_id',
        'name',
        'percentage',
        'description',
        'reorder_id',
        'is_available',
        'is_deleted'
    ];
}

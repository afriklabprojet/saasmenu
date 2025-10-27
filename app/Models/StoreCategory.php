<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class StoreCategory extends Model
{
    use HasFactory;
    protected $table = 'store_category';
    protected $fillable = ['reorder_id', 'name', 'is_available', 'is_deleted'];
}

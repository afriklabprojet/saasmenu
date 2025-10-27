<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Testimonials extends Model
{
    use HasFactory;
    protected $table = 'testimonials';
    protected $fillable = ['vendor_id', 'reorder_id', 'star', 'description', 'name', 'image', 'position'];
}


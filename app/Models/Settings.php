<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'vendor_id', 'currency', 'currency_position', 'currency_space', 'decimal_separator',
        'currency_formate', 'maintenance_mode', 'checkout_login_required', 'is_checkout_login_required',
        'logo', 'favicon', 'delivery_type', 'timezone', 'address', 'email', 'description',
        'contact', 'copyright', 'website_title', 'meta_title', 'meta_description', 'og_image',
        'language', 'languages', 'template', 'template_type', 'primary_color', 'secondary_color',
        'landing_website_title', 'custom_domain', 'image_size', 'time_format', 'date_format',
        'whatsapp_chat_on_off', 'facebook_link', 'twitter_link', 'instagram_link',
        'linkedin_link', 'cover_image', 'tracking_id', 'notification_sound'
    ];
}

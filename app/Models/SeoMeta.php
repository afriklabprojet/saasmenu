<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{
    use HasFactory;

    protected $table = 'seo_meta';

    protected $fillable = [
        'vendor_id',
        'page_type',
        'page_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'twitter_card',
        'schema_markup',
        'canonical_url',
        'index',
        'follow',
    ];

    protected $casts = [
        'index' => 'boolean',
        'follow' => 'boolean',
    ];

    /**
     * Relation avec le vendor
     */
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Générer les balises robots
     */
    public function getRobotsAttribute()
    {
        $robots = [];
        $robots[] = $this->index ? 'index' : 'noindex';
        $robots[] = $this->follow ? 'follow' : 'nofollow';
        return implode(', ', $robots);
    }

    /**
     * Récupérer les meta tags pour une page
     */
    public static function getMetaTags($vendorId, $pageType, $pageId = null)
    {
        $query = self::where('vendor_id', $vendorId)
                    ->where('page_type', $pageType);
        
        if ($pageId) {
            $query->where('page_id', $pageId);
        }

        return $query->first();
    }

    /**
     * Créer ou mettre à jour les meta tags
     */
    public static function updateOrCreateMeta($vendorId, $pageType, $data, $pageId = null)
    {
        return self::updateOrCreate(
            [
                'vendor_id' => $vendorId,
                'page_type' => $pageType,
                'page_id' => $pageId,
            ],
            $data
        );
    }
}

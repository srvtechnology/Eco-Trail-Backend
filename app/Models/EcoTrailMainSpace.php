<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcoTrailMainSpace extends Model
{
    use HasFactory;

    protected $table = 'ecotrail_main_spaces';

    protected $fillable = [
        'place_name',
        'description',
        'category_id',
        'latitude',
        'longitude',
        'google_maps_link',
        'full_address',
        'featured_image',
        'gallery_images',
        'highlight_info',
        'about_info',
        'latlong_info',
    ];

    protected $casts = [
        'gallery_images' => 'array'
    ];

    public function nearbyPlaces()
    {
        return $this->hasMany(EcoTrailNearbyPlace::class, 'main_space_id');
    }


    public function CatDetails()
    {
        return $this->hasOne('App\Models\SpaceCategory','id','category_id') ;
    }
}
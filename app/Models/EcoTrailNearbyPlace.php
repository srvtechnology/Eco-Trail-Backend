<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcoTrailNearbyPlace extends Model
{
    use HasFactory;

    protected $table = 'ecotrail_nearby_places';

    protected $fillable = [
        'main_space_id',
        'sub_cat_id',
        'place_name',
        'latitude',
        'longitude',
        'address',
        'description',
        'distance_from_main',
        'distance_unit',
        'images',
        'trees',
        'wildlife',
        'best_time_to_visit',
        'entry_fee',
        'opening_hours',
        'facilities_available',
        'safety_tips',
        'estimated_time_spend',
        'distance_from_last_point',
        'additional_info'
    ];

    protected $casts = [
        'images' => 'array',
        'trees' => 'array',
        'facilities_available' => 'array',
        'additional_info' => 'array'
    ];

    public function mainSpace()
    {
        return $this->belongsTo(EcoTrailMainSpace::class, 'main_space_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpaceSubCategory extends Model
{
    protected $table = 'spaces_sub_category';

    protected $fillable = [
        'cat_id',
        'long_description',
        'short_description',
        'name',
        'images',
        'additional_info',
    ];

    protected $casts = [
        'images' => 'array',
        'additional_info' => 'array',
    ];

       public function spaceCat() {
        return $this->hasOne('App\Models\SpaceCategory','id','cat_id') ;
    }
}

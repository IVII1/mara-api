<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'image_url',
        'hover_image_url',
        'hover_image_cloudinary_id',
        'cloudinary_id',
        'title',
        'material',
        'height',
        'width',
        'depth',
        'units',
        'production_year',
        'description',
        'position',
        
    ];
    public function images(){
        return $this->hasMany(Image::class);
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'thumbnail',
        'cloudinary_id',
        'title',
        'material',
        'height',
        'width',
        'depth',
        'units',
        'production_year',
        'description',
    ];
    public function images(){
        return $this->hasMany(Image::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = [
        'project_id',
        'image_url',
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
    public function project(){
        return $this->belongsTo(Project::class);
    }
}

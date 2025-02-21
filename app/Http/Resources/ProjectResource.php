<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image_url' => $this->image_url,
            'cloudinary_id'=> $this->cloudinary_id,
            'title' => $this->title,
            'description'=> $this->description,
            'material' => $this->material,
            'height' => $this->height,
            'width' => $this->width,
            'depth' => $this->depth,
            'units' => $this->units,
            'production_year' => $this->production_year,
            'position'=> $this->position,
            'images' =>  ImageResource::collection($this->whenLoaded('images')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'categories' => CategoryResource::collection($this->whenLoaded('categories',)),
        ];
    }
}

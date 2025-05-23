<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
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
            'project_id' => $this->project_id,
            'image_url'=> $this->image_url,
            'cloudinary_id' => $this->cloudinary_id,
            'title' => $this->title,
           'description'=> $this->description,
            'material' => $this->material,
            'height' => $this->height,
            'width' => $this->width,
            'depth' => $this->depth,
            'units' => $this->units,
            'production_year' => $this->production_year,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'project' => $this->whenLoaded('project', function() {
            return new ProjectResource($this->project);
})


        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image_url' => 'nullable|url',
            'cloudinary_id' => 'nullable|string',
            'title' => 'required|string|max:255',
            'material' => 'required|string|max:255',
            'height' => 'required|numeric',
            'width' => 'required|numeric',
            'depth' => 'required|numeric',
            'units' => 'required|string',
            'production_year' => 'required|integer',
            'description' => 'required|string',
            'position' => 'nullable|integer',
            'category_ids' => 'sometimes|array',
            'category_ids.*' => 'exists:categories,id'
        ];
    }
} 
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image_url' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'hover_image_url' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'cloudinary_id'=> 'string',
            'hover_image_cloudinary_id'=> 'string',
            'title' => 'required|string',
            'material' => 'nullable|string',
            'height' => 'nullable|decimal:0,2',
            'width' => 'nullable|decimal:0,2',
            'depth' => 'nullable|decimal:0,2',
            'units' => ['nullable', Rule::in(['cm', 'm', 'mm', 'ft', '"'])],
            'production_year' => 'required|integer',
            'description' =>'nullable|string',
            'position' => 'nullable|integer',
            'category_ids' => 'sometimes|array',
            'category_ids.*' => 'exists:categories,id'
        ];
    }
    public function messages()
    { return [
        'units.in' => 'Units can only be one of the following: cm, m, ", ft, in'
    ];
        
    }
}

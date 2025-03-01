<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectUpdateRequest extends FormRequest
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
            'image_url' => 'string|url',
            'title' => 'string',
            'material' => 'nullable|string',
            'height' => 'nullable|decimal:0,2',
            'width' => 'nullable|decimal:0,2',
            'depth' => 'nullable|decimal:0,2',
            'units' => [ 'nullable',Rule::in(['cm', 'm', 'mm', 'ft', '"'])],
            'production_year' => 'integer',
            'description' =>'string|nullable',
            'position'=> 'integer',
            'category_ids' => 'sometimes|array',
            'category_ids.*' => 'exists:categories,id'
        ];
        
    }

    public function messages(): array{
        return [
            'units.in' => 'Units can only be one of the following: cm, m, mm, ft, "'
        ];
    }
}

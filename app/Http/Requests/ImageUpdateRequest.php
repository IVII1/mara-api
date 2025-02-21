<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImageUpdateRequest extends FormRequest
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
            'project_id'=> 'nullable|integer|exists:projects,id',
            'title' => 'nullable|string',
            'material' => 'nullable|string',
            'height' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'depth' => 'nullable|numeric',
            'units' => ['nullable', Rule::in(['cm', 'm', 'mm', 'ft', '"'])],
            'production_year' => 'nullable|integer',
            'description' =>'nullable|string',
        ];
    }
    public function messages(): array{
        return [
            'units.in' => 'Units can only be one of the following: cm, m, mm, ft, "'
        ];
    }
}

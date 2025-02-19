<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImageStoreRequest extends FormRequest
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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'title' => 'string',
            'material' => 'string',
            'height' => 'decimal:0,2',
            'width' => 'decimal:0,2',
            'depth' => 'decimal:0,2',
            'units' => [ Rule::in(['cm', 'm', 'mm', 'ft', '"'])],
            'production_year' => 'integer',
            'description' =>'string|nullable',
        ];
    }
}

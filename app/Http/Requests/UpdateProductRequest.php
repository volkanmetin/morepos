<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'variants' => 'required|array|min:1',
            'variants.*.sku' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_variants', 'sku')->ignore($this->route('product')->id, 'product_id'),
            ],
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.attributes' => 'required|array',
            'variants.*.attributes.*' => 'required|exists:attribute_values,id',
        ];
    }

    public function messages(): array
    {
        return [
            'variants.required' => 'En az bir varyant gereklidir.',
            'variants.*.sku.required' => 'Her varyant için SKU gereklidir.',
            'variants.*.sku.unique' => 'Bu SKU zaten kullanılmaktadır.',
            'variants.*.price.required' => 'Her varyant için fiyat gereklidir.',
            'variants.*.price.numeric' => 'Fiyat sayısal bir değer olmalıdır.',
            'variants.*.price.min' => 'Fiyat 0\'dan büyük olmalıdır.',
            'variants.*.attributes.required' => 'Her varyant için özellikler gereklidir.',
            'variants.*.attributes.*.required' => 'Her özellik grubu için bir değer seçilmelidir.',
            'variants.*.attributes.*.exists' => 'Seçilen özellik değeri geçerli değil.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'name' => 'required',
            'category_id' => 'required|exists:categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'brand_id' => 'nullable|exists:brands,id',
            'purchase_price' => 'nullable|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'discounted_price' => 'nullable|numeric|min:0',
            'variants' => 'required|array|min:1',
            'variants.*.image' => 'nullable',
            'variants.*.variant' => 'required|array',
            'variants.*.purchase_price' => 'required|numeric|min:0',
            'variants.*.sale_price' => 'required|numeric|min:0',
            'variants.*.stock' => 'required|array',
            'variants.*.stock.*' => 'required|numeric|min:0',
        ];
    }
}

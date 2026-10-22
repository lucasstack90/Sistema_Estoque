<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cost_price' => parse_money($this->input('cost_price')),
            'sale_price' => parse_money($this->input('sale_price')),
            'sku' => strtoupper(trim((string) $this->input('sku'))),
            'active' => $this->boolean('active'),
        ]);
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product)],
            'name' => ['required', 'string', 'max:150'],
            'brand' => ['required', 'string', 'max:80'],
            'model' => ['nullable', 'string', 'max:80'],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cost_price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'sale_price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'min_stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'initial_stock' => [$product ? 'prohibited' : 'nullable', 'integer', 'min:0', 'max:1000000'],
            'location' => ['nullable', 'string', 'max:50'],
            'warranty_months' => ['nullable', 'integer', 'min:0', 'max:120'],
            'active' => ['boolean'],
        ];
    }
}

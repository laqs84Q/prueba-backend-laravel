<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'currency_id' => [
                'required',
                'integer',
                'exists:currencies,id',
                // No repetimos la misma divisa para un mismo producto.
                Rule::unique('product_prices', 'currency_id')
                    ->where('product_id', $product->id),
                Rule::notIn([$product->currency_id]), // La divisa base ya tiene su precio.
            ],
            'price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
        ];
    }
}

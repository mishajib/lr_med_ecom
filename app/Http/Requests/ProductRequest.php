<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title'                          => 'bail|required|string',
            'sku'                            => 'bail|required|string|unique:products',
            'description'                    => 'bail|nullable|string',
            'product_image'                  => 'bail|required|array',
            'product_image.*'                => 'bail|required|string',
            'product_variant.*.option'       => 'bail|required|integer',
            'product_variant.*.tags'         => 'bail|required|array',
            'product_variant.*.tags.*'       => 'bail|required|string',
            'product_variant_prices'         => 'bail|required|array',
            'product_variant_prices.*.title' => 'bail|required|string',
            'product_variant_prices.*.price' => 'bail|required|numeric|min:0',
            'product_variant_prices.*.stock' => 'bail|required|numeric|min:0',
        ];

        if ($this->method() == 'PUT') {
            $rules = [
                'title'                          => 'bail|required|string',
                'sku'                            => 'bail|required|string|unique:products,id,:id',
                'description'                    => 'bail|nullable|string',
                'product_image'                  => 'bail|nullable|array',
                'product_image.*'                => 'bail|nullable|string',
                'product_variant.*.option'       => 'bail|required|integer',
                'product_variant.*.tags'         => 'bail|required|array',
                'product_variant.*.tags.*'       => 'bail|required|string',
                'product_variant_prices'         => 'bail|required|array',
                'product_variant_prices.*.title' => 'bail|required|string',
                'product_variant_prices.*.price' => 'bail|required|numeric|min:0',
                'product_variant_prices.*.stock' => 'bail|required|numeric|min:0',
            ];
        }

        return $rules;
    }
}

<?php

namespace App\QueryFilters\ProductFilters;

use App\QueryFilters\Filter;

class PriceFilter extends Filter
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    protected function applyFilters($builder)
    {
        if (isset($this->request->price_from) && isset($this->request->price_to)) {
            return $builder->whereHas('product_variant_prices', function ($variant_price) {
                $variant_price->whereBetween('price', [$this->request->price_from, $this->request->price_to]);
            });
        }
        return $builder;
    }
}

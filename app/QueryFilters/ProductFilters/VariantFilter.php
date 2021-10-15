<?php

namespace App\QueryFilters\ProductFilters;

use App\QueryFilters\Filter;

class VariantFilter extends Filter
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    protected function applyFilters($builder)
    {
        if (isset($this->request->variant)) {
            return $builder->whereHas('product_variants', function ($query) {
                $query->where('id', $this->request->variant);
            });
        }
        return $builder;
    }
}

<?php

namespace App\QueryFilters\ProductFilters;

use App\QueryFilters\Filter;

class TitleFilter extends Filter
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    protected function applyFilters($builder)
    {
        if (isset($this->request->title)) {
            return $builder->where('title', 'like', '%' . $this->request->title . '%');
        }
        return $builder;
    }
}

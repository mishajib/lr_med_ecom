<?php

namespace App\QueryFilters\ProductFilters;

use App\QueryFilters\Filter;
use Carbon\Carbon;

class DateFilter extends Filter
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    protected function applyFilters($builder)
    {
        if (isset($this->request->date)) {
            return $builder->whereDate('created_at', Carbon::parse($this->request->date)->format("Y-m-d"));
        }
        return $builder;
    }
}

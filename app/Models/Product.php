<?php

namespace App\Models;

use App\QueryFilters\ProductFilters\DateFilter;
use App\QueryFilters\ProductFilters\PriceFilter;
use App\QueryFilters\ProductFilters\TitleFilter;
use App\QueryFilters\ProductFilters\VariantFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pipeline\Pipeline;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    public function product_images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function product_variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function product_variant_prices()
    {
        return $this->hasMany(ProductVariantPrice::class, 'product_id');
    }


    // Filter method through pipelie
    public static function filtered($request)
    {
        $query = self::query()->with([
            'product_variants',
            'product_variants.variation',
            'product_variant_prices'
        ]);
        return app(Pipeline::class)
            ->send($query)
            ->through([
                new TitleFilter($request),
                new VariantFilter($request),
                new PriceFilter($request),
                new DateFilter($request)
            ])
            ->thenReturn();
    }

}

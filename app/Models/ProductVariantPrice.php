<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantPrice extends Model
{
    protected $guarded = ['id'];

    protected $appends = [
        'product_variant_one_text',
        'product_variant_two_text',
        'product_variant_three_text'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class)->withDefault();
    }

    public function getProductVariantOneTextAttribute()
    {
        $variant = ProductVariant::where([['id', $this->product_variant_one], ['product_id', $this->product_id]])->first();

        return $variant ? $variant->variant : 'N/A';
    }

    public function getProductVariantTwoTextAttribute()
    {
        $variant = ProductVariant::where([['id', $this->product_variant_two], ['product_id', $this->product_id]])->first();
        return $variant ? $variant->variant : 'N/A';
    }

    public function getProductVariantThreeTextAttribute()
    {
        $variant = ProductVariant::where([['id', $this->product_variant_three], ['product_id', $this->product_id]])->first();
        return $variant ? $variant->variant : 'N/A';
    }
}

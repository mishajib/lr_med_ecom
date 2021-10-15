<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class)->withDefault();
    }

    public function variation()
    {
        return $this->belongsTo(Variant::class, 'variant_id')->withDefault();
    }
}

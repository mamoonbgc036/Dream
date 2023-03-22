<?php

namespace App\Models;

use App\Models\ProductVariantPrice;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'variant', 'variant_id', 'product_id'
    ];

    public function product_variant_prices(){
        return $this->hasMany(ProductVariantPrice::class,'product_id','product_id');
    }
}
<?php

namespace App\Models;

use App\Models\ProductImage;
use App\Models\ProductVariantPrice;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];
    public function product_variants_price(){
        return $this->hasMany(ProductVariantPrice::class,'product_id','id');
    }
    public function images(){
        return $this->hasMany(ProductImage::class,'product_id','id');
    }

    public function product_variants(){
        return $this->hasMany(ProductVariant::class,'product_id', 'id');
    }
}
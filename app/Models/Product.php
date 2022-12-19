<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    public function productVariants() :HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function productVariantPrices() :HasOne
    {
        return $this->hasOne(ProductVariantPrice::class, 'product_id');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Variant extends Model
{
    protected $fillable = [
        'title', 'description'
    ];

    protected $appends = ['product_variant_names'];

    public function  productVariants() :HasMany
    {
        return $this->hasMany(ProductVariant::class, 'variant_id');
    }

    public function getProductVariantNamesAttribute()
    {
        return $this->productVariants->pluck('variant');
    }

}

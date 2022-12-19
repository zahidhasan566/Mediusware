<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    public function variant() :BelongsTo
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }
}

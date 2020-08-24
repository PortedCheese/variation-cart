<?php

namespace PortedCheese\VariationCart\Models;

use App\ProductVariation;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [];

    /**
     * Вариации.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function variations()
    {
        return $this->belongsToMany(ProductVariation::class)
            ->withPivot("quantity")
            ->withTimestamps();
    }
}

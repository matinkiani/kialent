<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'brand_name'])]
class Packaging extends Model
{
    use HasFactory;

    /**
     * @return HasMany<PackagingItem, $this>
     */
    public function packagingItems(): HasMany
    {
        return $this->hasMany(PackagingItem::class);
    }
}

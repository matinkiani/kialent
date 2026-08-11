<?php

namespace App\Models;

use App\Enums\BackPlateType;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $technical_code
 * @property string $name
 * @property string|null $description
 * @property BackPlateType $back_plate_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['technical_code', 'name', 'description', 'back_plate_type'])]
class Product extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'back_plate_type' => BackPlateType::class,
        ];
    }

    public function backPlateBreakdown(int $sets): string
    {
        return $this->back_plate_type->breakdownForSets($sets);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}

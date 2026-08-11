<?php

namespace App\Models;

use App\Enums\ChamferType;
use App\Enums\SlotType;
use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $quantity
 * @property string|null $backing_plate_thickness
 * @property bool $has_shim
 * @property string $color
 * @property SlotType $slot_type
 * @property ChamferType $chamfer_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order $order
 * @property-read Product $product
 */
#[Fillable([
    'order_id',
    'product_id',
    'quantity',
    'backing_plate_thickness',
    'has_shim',
    'color',
    'slot_type',
    'chamfer_type',
    'production_notes',
    'packaging_notes',
    'sort',
])]
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    protected $attributes = [
        'quantity' => 1,
        'has_shim' => false,
        'slot_type' => 'none',
        'chamfer_type' => 'none',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'quantity' => 'integer',
            'backing_plate_thickness' => 'decimal:1',
            'has_shim' => 'boolean',
            'color' => 'string',
            'slot_type' => SlotType::class,
            'chamfer_type' => ChamferType::class,
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

<?php

namespace App\Models;

use App\Enums\ChamferType;
use App\Enums\SlotType;
use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $quantity
 * @property int|null $box_quantity
 * @property string|null $backing_plate_thickness
 * @property bool $has_shim
 * @property string $color
 * @property SlotType $slot_type
 * @property ChamferType $chamfer_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order $order
 * @property-read Product $product
 * @property-read int|null $shipped_total
 * @property-read HasMany<ShipmentItem, $this> $shipmentItems
 */
#[Fillable([
    'order_id',
    'product_id',
    'quantity',
    'box_quantity',
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
            'box_quantity' => 'integer',
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

    public function shipmentItems(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    /**
     * مجموع تعداد ارسال‌شده برای این قلم سفارش.
     *
     * در صورت بارگذاری تجمعی `shipped_total` (با withSum) از همان استفاده می‌شود.
     */
    public function shippedQuantity(): int
    {
        return (int) ($this->shipped_total ?? $this->shipmentItems()->sum('shipped_quantity'));
    }
}

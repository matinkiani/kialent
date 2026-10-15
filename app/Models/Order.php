<?php

namespace App\Models;

use App\Enums\ChamferType;
use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Enums\SlotType;
use App\Observers\OrderObserver;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property int $customer_id
 * @property int|null $packaging_id
 * @property string $brand
 * @property string|null $color
 * @property SlotType $slot_type
 * @property ChamferType $chamfer_type
 * @property string $order_number
 * @property OrderPriority $priority
 * @property OrderStatus $status
 * @property string|null $payment_notes
 * @property Carbon $ordered_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Customer $customer
 * @property-read Packaging|null $packaging
 */
#[Fillable(['customer_id', 'packaging_id', 'brand', 'color', 'slot_type', 'chamfer_type', 'priority', 'status', 'payment_notes', 'ordered_at'])]
#[ObservedBy(OrderObserver::class)]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, LogsActivity;

    protected $attributes = [
        'priority' => 'normal',
        'status' => 'initial',
        'slot_type' => 'none',
        'chamfer_type' => 'none',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'packaging_id' => 'integer',
            'priority' => OrderPriority::class,
            'status' => OrderStatus::class,
            'slot_type' => SlotType::class,
            'chamfer_type' => ChamferType::class,
            'ordered_at' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Packaging, $this>
     */
    public function packaging(): BelongsTo
    {
        return $this->belongsTo(Packaging::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * اقلام سفارش برای چاپ فرم‌ها.
     *
     * در حالت «کم کردن ارسال شده» تعداد هر قلم به «سفارش منهای ارسال‌شده» کاهش می‌یابد
     * و اقلامِ کاملاً ارسال‌شده از خروجی حذف می‌شوند.
     *
     * @return Collection<int, OrderItem>
     */
    public function orderItemsForForms(bool $deductShipped = false): Collection
    {
        if (! $deductShipped) {
            return $this->orderItems;
        }

        return $this->orderItems
            ->map(function (OrderItem $item): OrderItem {
                $clone = clone $item;

                $clone->quantity = max(0, $item->quantity - $item->shippedQuantity());

                return $clone;
            })
            ->filter(fn (OrderItem $item): bool => $item->quantity > 0)
            ->values();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}

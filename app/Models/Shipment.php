<?php

namespace App\Models;

use Database\Factories\ShipmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Shipment extends Model
{
    /** @use HasFactory<ShipmentFactory> */
    use HasFactory, LogsActivity;

    protected $fillable = [
        'order_id',
        'shipment_number',
        'driver_name',
        'vehicle_plate',
        'destination',
        'total_master_boxes',
        'total_weight',
        'shipped_at',
        'notes',
    ];

    protected $casts = [
        'shipped_at' => 'date',
        'total_weight' => 'decimal:3',
    ];

    protected static function booted()
    {
        static::creating(function (Shipment $shipment): void {
            if ($shipment->shipment_number) {
                return;
            }

            $shipment->shipment_number = DB::transaction(function () use ($shipment): string {
                // قفل ردیف سفارش: دو حواله هم‌زمان برای یک سفارش، شماره تکراری نمی‌گیرند.
                $order = Order::query()->whereKey($shipment->order_id)->lockForUpdate()->first();

                // شماره حواله از بالاترین شماره «موجود» سفارش + ۱ ساخته می‌شود،
                // نه از تعداد ردیف‌ها — تا با حذف یک حواله، توالی تکرار نشود
                // (در غیر این صورت ردیف تازه با ردیف باقی‌مانده، UNIQUE تکراری می‌زند).
                $sequence = Shipment::query()
                    ->where('order_id', $shipment->order_id)
                    ->max('shipment_number');

                $sequence = $sequence
                    ? ((int) (string) str($sequence)->substr(-2)) + 1
                    : 1;

                return sprintf('%s-S%02d', $order->order_number, $sequence);
            });
        });
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return HasMany<ShipmentItem, $this>
     */
    public function shipmentItems(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}

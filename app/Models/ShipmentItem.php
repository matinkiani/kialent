<?php

namespace App\Models;

use Database\Factories\ShipmentItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class ShipmentItem extends Model
{
    /** @use HasFactory<ShipmentItemFactory> */
    use HasFactory, LogsActivity;

    protected $fillable = [
        'shipment_id',
        'order_item_id',
        'shipped_quantity',
        'box_quantity',
    ];

    protected function casts(): array
    {
        return [
            'shipped_quantity' => 'integer',
            'box_quantity' => 'integer',
        ];
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}

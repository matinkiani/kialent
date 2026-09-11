<?php

namespace App\Models;

use Database\Factories\PackagingItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

#[Fillable(['packaging_id', 'product_id', 'quantity'])]
class PackagingItem extends Model
{
    /** @use HasFactory<PackagingItemFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(function (PackagingItem $packagingItem): void {
            Cache::forget("packaging.{$packagingItem->packaging_id}.product-quantities");
        });

        static::deleted(function (PackagingItem $packagingItem): void {
            Cache::forget("packaging.{$packagingItem->packaging_id}.product-quantities");
        });
    }

    protected function casts(): array
    {
        return [
            'packaging_id' => 'integer',
            'product_id' => 'integer',
            'quantity' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Packaging, $this>
     */
    public function packaging(): BelongsTo
    {
        return $this->belongsTo(Packaging::class);
    }

    /**
     * تعداد دستِ هر کارتن برای یک محصول در یک بسته‌بندی.
     *
     * نتیجه بر اساس بسته‌بندی کش می‌شود و با تغییر اقلام بسته‌بندی باطل می‌گردد.
     */
    public static function quantityPerBox(?int $packagingId, ?int $productId): ?int
    {
        if ($packagingId === null || $productId === null) {
            return null;
        }

        $quantitiesPerBox = Cache::remember(
            "packaging.{$packagingId}.product-quantities",
            now()->addMinutes(10),
            fn (): array => static::query()
                ->where('packaging_id', $packagingId)
                ->pluck('quantity', 'product_id')
                ->map(fn (mixed $quantity): int => (int) $quantity)
                ->all(),
        );

        $quantityPerBox = $quantitiesPerBox[$productId] ?? null;

        return ($quantityPerBox === null || $quantityPerBox <= 0)
            ? null
            : $quantityPerBox;
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

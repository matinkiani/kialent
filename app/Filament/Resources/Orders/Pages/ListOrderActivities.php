<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\OrderItem;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use pxlrbt\FilamentActivityLog\Pages\ListActivitiesBySubject;
use Spatie\Activitylog\Models\Activity;

class ListOrderActivities extends ListActivitiesBySubject
{
    protected static string $resource = OrderResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return parent::canAccess($parameters)
            && (bool) auth()->user()?->can('View:ActivityHistory');
    }

    /**
     * علاوه بر فعالیت‌های خود سفارش، فعالیت‌های اقلام آن (OrderItem) را هم
     * نشان می‌دهد — از طریق تگ order_id در ویژگی‌های فعالیت.
     */
    public function getActivities()
    {
        return $this->paginateQuery(
            Activity::query()
                ->where(function (Builder $query) {
                    $query->whereMorphedTo('subject', $this->record)
                        ->orWhere(function (Builder $query) {
                            $query->where('subject_type', (new OrderItem)->getMorphClass())
                                ->where('properties->order_id', $this->record->getKey());
                        });
                })
                ->with('causer')
                ->latest()
                ->orderByDesc('id')
        );
    }

    /**
     * بازیابی باید روی subject واقعی فعالیت انجام شود (قلم یا سفارش)،
     * نه همیشه روی خود سفارش.
     */
    public function restoreActivity(int|string $key): void
    {
        if (! $this->canRestoreActivity()) {
            abort(403);
        }

        $activity = Activity::query()->whereKey($key)->first();

        $oldAttributes = data_get($activity, 'attribute_changes.old');

        if ($activity === null || $oldAttributes === null) {
            $this->sendRestoreFailureNotification();

            return;
        }

        // مقادیر null (مثل ستون‌های nullable) را حذف می‌کنیم تا قید NOT NULL دیتابیس نقض نشود.
        $oldAttributes = array_filter($oldAttributes, fn ($value): bool => $value !== null);

        $subject = $activity->subject;

        if (! $subject instanceof Model || ! $subject->exists) {
            $this->sendRestoreFailureNotification();

            return;
        }

        try {
            $subject->update($oldAttributes);

            $this->sendRestoreSuccessNotification();
        } catch (Exception $e) {
            $this->sendRestoreFailureNotification($e->getMessage());
        }
    }
}

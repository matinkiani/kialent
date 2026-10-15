<div class="space-y-1 text-sm">
    @forelse(data_get($activity->attribute_changes, 'attributes', []) as $field => $newValue)
        @php($oldValue = $activity->attribute_changes['old'][$field] ?? null)
        <div class="border-b border-gray-100 py-1.5">
            <div class="mb-0.5 font-medium">{{ $labels[$field] ?? $field }}</div>
            <div class="flex gap-4">
                <span class="text-gray-500">
                    قدیم:
                    <span class="text-gray-700">{{ is_bool($oldValue) ? ($oldValue ? 'بله' : 'خیر') : ($oldValue ?? '—') }}</span>
                </span>
                <span class="text-gray-500">
                    جدید:
                    <span class="font-medium">{{ is_bool($newValue) ? ($newValue ? 'بله' : 'خیر') : ($newValue ?? '—') }}</span>
                </span>
            </div>
        </div>
    @empty
        <p class="text-gray-500">تغییری ثبت نشده است.</p>
    @endforelse
</div>

<?php

namespace App\Models\Concerns;

use App\Realtime\RealtimeHub;
use Illuminate\Support\Str;

/**
 * Pushes created / updated / deleted events of the model to the realtime
 * channels (admin always, plus the clients / drivers from realtimeAudience()).
 */
trait BroadcastsRealtimeChanges
{
    public static function bootBroadcastsRealtimeChanges(): void
    {
        static::created(fn ($model) => $model->recordRealtimeChange('created'));

        static::updated(function ($model) {
            $changed = array_diff(array_keys($model->getChanges()), $model->realtimeIgnoredAttributes());

            if ($changed !== []) {
                $model->recordRealtimeChange('updated');
            }
        });

        static::deleted(fn ($model) => $model->recordRealtimeChange('deleted'));

        if (method_exists(static::class, 'restored')) {
            static::restored(fn ($model) => $model->recordRealtimeChange('updated'));
        }
    }

    /** Name sent to listeners, e.g. "order", "financial_ledger_entry". */
    public function realtimeType(): string
    {
        return Str::snake(class_basename($this));
    }

    /**
     * Who besides admins should hear about this change.
     *
     * @return array{clients?: array<int|null>, drivers?: array<int|null>}  drivers are user ids
     */
    public function realtimeAudience(): array
    {
        return [];
    }

    /** Updates that only touch these columns are not broadcast. */
    public function realtimeIgnoredAttributes(): array
    {
        return array_filter([$this->getUpdatedAtColumn()]);
    }

    public function recordRealtimeChange(string $action): void
    {
        try {
            $audience = $this->realtimeAudience();

            app(RealtimeHub::class)->record(
                $this->realtimeType(),
                $this->getKey(),
                $action,
                $audience['clients'] ?? [],
                $audience['drivers'] ?? [],
            );
        } catch (\Throwable $e) {
            logger()->warning('[Realtime] could not record change', [
                'model' => static::class,
                'id'    => $this->getKey(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /** Current value plus the previous one when it just changed (e.g. order re-assigned to another driver). */
    protected function realtimeCurrentAndPrevious(string $attribute): array
    {
        return array_values(array_unique(array_filter([
            $this->getAttribute($attribute),
            $this->getOriginal($attribute),
        ])));
    }
}

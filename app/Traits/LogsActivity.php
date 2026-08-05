<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->recordActivity('created');
        });

        static::updated(function ($model) {
            $changes = [
                'old' => array_intersect_key($model->getOriginal(), $model->getChanges()),
                'new' => $model->getChanges(),
            ];
            $model->recordActivity('updated', $changes);
        });

        static::deleted(function ($model) {
            $model->recordActivity('deleted');
        });
    }

    protected function recordActivity(string $action, ?array $changes = null): void
    {
        $this->activities()->create([
            'user_id' => auth()->id(),
            'action'  => $action,
            'changes' => $changes,
        ]);
    }

    public function activities()
    {
        return $this->morphMany(ActivityLog::class, 'loggable')->latest();
    }
}

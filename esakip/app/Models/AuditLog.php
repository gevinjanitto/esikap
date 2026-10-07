<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['before' => 'array', 'after' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, ?Model $model, ?array $before, ?array $after): void
    {
        $label = $model ? ($model->name ?? $model->title ?? $model->aktivitas ?? $model->uraian ?? null) : null;
        if ($model && method_exists($model, 'workflowTitle')) {
            $label = $model->workflowTitle();
        }
        static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $model ? class_basename($model) : null,
            'model_id' => $model?->getKey(),
            'label' => $label ? mb_substr(strip_tags((string) $label), 0, 250) : null,
            'before' => $before,
            'after' => $after,
            'ip' => app()->runningInConsole() ? 'console' : request()->ip(),
        ]);
    }
}

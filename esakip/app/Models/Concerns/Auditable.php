<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn ($m) => AuditLog::record('create', $m, null, $m->attributesToArray()));
        static::updated(function ($m) {
            $changes = collect($m->getChanges())->except(['updated_at', 'password', 'remember_token'])->all();
            if (! $changes) {
                return;
            }
            AuditLog::record('update', $m, array_intersect_key($m->getOriginal(), $changes), $changes);
        });
        static::deleted(fn ($m) => AuditLog::record('delete', $m, $m->attributesToArray(), null));
    }
}

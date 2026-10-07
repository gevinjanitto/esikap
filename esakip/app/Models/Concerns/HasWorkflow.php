<?php

namespace App\Models\Concerns;

use App\Models\ApprovalLog;
use App\Models\User;

trait HasWorkflow
{
    public function approvalLogs()
    {
        return $this->morphMany(ApprovalLog::class, 'approvable')->latest('id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'revisi', 'ditolak']);
    }

    public function scopeVisibleTo($q, $user)
    {
        return $user->isOpdScoped() ? $q->where($this->getTable().'.opd_id', $user->opd_id) : $q;
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class RencanaAksi extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    public function opd() { return $this->belongsTo(Opd::class)->withTrashed(); }
    public function indicator() { return $this->belongsTo(Indicator::class); }
    public function evidences() { return $this->morphMany(Evidence::class, 'evidenceable'); }
    public function scopeVisibleTo($q, $user) { return $user->isOpdScoped() ? $q->where('opd_id', $user->opd_id) : $q; }
}

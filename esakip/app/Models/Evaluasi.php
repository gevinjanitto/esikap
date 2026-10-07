<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Evaluasi extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    public function opd() { return $this->belongsTo(Opd::class)->withTrashed(); }
    public function evaluator() { return $this->belongsTo(User::class, 'evaluator_id'); }
    public function rekomendasis() { return $this->hasMany(Rekomendasi::class); }
    public function scopeVisibleTo($q, $user) { return $user->isOpdScoped() ? $q->where('opd_id', $user->opd_id) : $q; }
}

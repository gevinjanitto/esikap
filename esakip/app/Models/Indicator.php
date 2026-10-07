<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Indicator extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    public const LEVELS = ['sasaran_daerah' => 'Sasaran Daerah', 'sasaran_opd' => 'Sasaran OPD', 'program' => 'Program', 'kegiatan' => 'Kegiatan'];
    public function owner() { return $this->morphTo(); }
    public function satuan() { return $this->belongsTo(Satuan::class)->withTrashed(); }
    public function opd() { return $this->belongsTo(Opd::class)->withTrashed(); }
    public function targets() { return $this->hasMany(IndicatorTarget::class)->orderBy('year'); }
    public function realisasis() { return $this->hasMany(Realisasi::class); }
    public function targetFor($year) { return optional($this->targets->firstWhere('year', (int) $year))->value; }
    public function getLevelLabelAttribute(): string { return self::LEVELS[$this->owner_type] ?? $this->owner_type; }
    public function scopeVisibleTo($q, $user) { return $user->isOpdScoped() ? $q->where('opd_id', $user->opd_id) : $q; }
}

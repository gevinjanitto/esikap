<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class SasaranDaerah extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    public function tujuan() { return $this->belongsTo(TujuanDaerah::class, 'tujuan_daerah_id'); }
    public function opds() { return $this->belongsToMany(Opd::class, 'opd_sasaran_daerah')->withPivot('peran')->withTimestamps(); }
    public function indicators() { return $this->morphMany(Indicator::class, 'owner'); }
    public function tujuanOpds() { return $this->hasMany(TujuanOpd::class); }
}

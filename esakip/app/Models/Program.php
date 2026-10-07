<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    public function sasaranOpd() { return $this->belongsTo(SasaranOpd::class); }
    public function opd() { return $this->belongsTo(Opd::class)->withTrashed(); }
    public function kegiatans() { return $this->hasMany(Kegiatan::class)->orderBy('code'); }
    public function indicators() { return $this->morphMany(Indicator::class, 'owner'); }
}

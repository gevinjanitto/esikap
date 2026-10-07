<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class SasaranOpd extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    public function tujuan() { return $this->belongsTo(TujuanOpd::class, 'tujuan_opd_id'); }
    public function programs() { return $this->hasMany(Program::class)->orderBy('code'); }
    public function indicators() { return $this->morphMany(Indicator::class, 'owner'); }
}

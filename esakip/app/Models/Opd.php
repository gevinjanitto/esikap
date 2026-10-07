<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opd extends Model
{
    use Auditable, SoftDeletes;

    protected $guarded = ['id'];

    public function units() { return $this->hasMany(Unit::class); }
    public function users() { return $this->hasMany(User::class); }
    public function sasaranDaerahs() { return $this->belongsToMany(SasaranDaerah::class, 'opd_sasaran_daerah')->withPivot('peran')->withTimestamps(); }
    public function renstras() { return $this->hasMany(Renstra::class); }
    public function realisasis() { return $this->hasMany(Realisasi::class); }
    public function indicators() { return $this->hasMany(Indicator::class); }
}

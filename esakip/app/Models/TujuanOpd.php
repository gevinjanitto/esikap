<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class TujuanOpd extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    public function renstra() { return $this->belongsTo(Renstra::class); }
    public function sasaranDaerah() { return $this->belongsTo(SasaranDaerah::class); }
    public function sasarans() { return $this->hasMany(SasaranOpd::class)->orderBy('code'); }
}

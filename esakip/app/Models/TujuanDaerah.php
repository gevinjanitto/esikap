<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class TujuanDaerah extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    public function misi() { return $this->belongsTo(Misi::class); }
    public function sasarans() { return $this->hasMany(SasaranDaerah::class)->orderBy('code'); }
}

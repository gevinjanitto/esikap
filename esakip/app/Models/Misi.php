<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Misi extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    public function rpjmd() { return $this->belongsTo(Rpjmd::class); }
    public function tujuans() { return $this->hasMany(TujuanDaerah::class)->orderBy('code'); }
}

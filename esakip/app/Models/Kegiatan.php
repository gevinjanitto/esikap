<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    public function program() { return $this->belongsTo(Program::class); }
    public function indicators() { return $this->morphMany(Indicator::class, 'owner'); }
}

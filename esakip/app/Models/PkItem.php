<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class PkItem extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    public function perjanjianKinerja() { return $this->belongsTo(PerjanjianKinerja::class); }
    public function indicator() { return $this->belongsTo(Indicator::class); }
}

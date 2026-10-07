<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class RenjaItem extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    public function renja() { return $this->belongsTo(Renja::class); }
    public function indicator() { return $this->belongsTo(Indicator::class); }
}

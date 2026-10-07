<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class IndicatorTarget extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    public function indicator() { return $this->belongsTo(Indicator::class); }
}

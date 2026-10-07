<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    protected $casts = ['is_active' => 'boolean'];
    public function rpjmds() { return $this->hasMany(Rpjmd::class); }
    public function years(): array { return range($this->start_year, $this->end_year); }
}

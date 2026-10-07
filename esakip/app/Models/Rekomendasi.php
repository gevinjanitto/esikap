<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Rekomendasi extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    protected $casts = ['batas_waktu' => 'date'];
    public function evaluasi() { return $this->belongsTo(Evaluasi::class); }
    public function evidences() { return $this->morphMany(Evidence::class, 'evidenceable'); }
}

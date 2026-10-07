<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use Auditable;

    protected $guarded = ['id'];

    protected $casts = ['tanggal' => 'date', 'archived_at' => 'datetime'];
    public function opd() { return $this->belongsTo(Opd::class)->withTrashed(); }
    public function period() { return $this->belongsTo(Period::class); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
}

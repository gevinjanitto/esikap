<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Evidence extends Model
{
    use Auditable;

    protected $table = 'evidences';

    protected $guarded = ['id'];

    public function evidenceable() { return $this->morphTo(); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
}

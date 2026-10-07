<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasWorkflow;
use Illuminate\Database\Eloquent\Model;

class Rpjmd extends Model
{
    use Auditable, HasWorkflow;

    protected $guarded = ['id'];

    protected $casts = ['tanggal_dokumen' => 'date'];
    public function period() { return $this->belongsTo(Period::class); }
    public function misis() { return $this->hasMany(Misi::class)->orderBy('code'); }
    public function scopeVisibleTo($q, $user) { return $q; }
    public function workflowTitle(): string { return $this->name; }
    public function workflowUrl(): string { return route('rpjmd.index'); }
}

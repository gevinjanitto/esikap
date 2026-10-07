<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasWorkflow;
use Illuminate\Database\Eloquent\Model;

class Renstra extends Model
{
    use Auditable, HasWorkflow;

    protected $guarded = ['id'];

    public function opd() { return $this->belongsTo(Opd::class)->withTrashed(); }
    public function period() { return $this->belongsTo(Period::class); }
    public function tujuans() { return $this->hasMany(TujuanOpd::class)->orderBy('code'); }
    public function workflowTitle(): string { return 'Renstra '.$this->opd?->name.' '.$this->period?->name; }
    public function workflowUrl(): string { return route('renstra.show', $this); }
}

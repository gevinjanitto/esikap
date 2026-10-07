<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasWorkflow;
use Illuminate\Database\Eloquent\Model;

class Renja extends Model
{
    use Auditable, HasWorkflow;

    protected $guarded = ['id'];

    public function opd() { return $this->belongsTo(Opd::class)->withTrashed(); }
    public function items() { return $this->hasMany(RenjaItem::class); }
    public function workflowTitle(): string { return 'Renja '.$this->opd?->name.' '.$this->year; }
    public function workflowUrl(): string { return route('annual.show', ['renja', $this->id]); }
}

<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasWorkflow;
use Illuminate\Database\Eloquent\Model;

class Realisasi extends Model
{
    use Auditable, HasWorkflow;

    protected $guarded = ['id'];

    public function opd() { return $this->belongsTo(Opd::class)->withTrashed(); }
    public function indicator() { return $this->belongsTo(Indicator::class); }
    public function evidences() { return $this->morphMany(Evidence::class, 'evidenceable'); }
    public function workflowTitle(): string { return 'Realisasi '.$this->periode.' '.$this->year.' - '.\Illuminate\Support\Str::limit($this->indicator?->name, 50); }
    public function workflowUrl(): string { return route('realisasi.index', ['tahun' => $this->year, 'periode' => $this->periode]); }
}

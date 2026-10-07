<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\Rpjmd;
use App\Models\SasaranDaerah;
use App\Support\Esakip;
use App\Support\Workflow;
use Illuminate\Http\Request;

class RpjmdController extends Controller
{
    public function index(Request $r)
    {
        $rpjmd = $r->get('id') ? Rpjmd::findOrFail($r->get('id')) : Esakip::activeRpjmd();
        $rpjmd?->load(['period', 'misis.tujuans.sasarans.opds', 'misis.tujuans.sasarans.indicators.targets', 'misis.tujuans.sasarans.indicators.satuan', 'approvalLogs.user']);
        $all = Rpjmd::with('period')->latest('id')->get();
        $opds = Opd::where('status', 'aktif')->orderBy('name')->pluck('name', 'id');
        $editable = $rpjmd && Esakip::canEdit('rpjmd') && $rpjmd->isEditable();
        $actions = $rpjmd ? Workflow::available($r->user(), $rpjmd, 'rpjmd') : [];

        return view('rpjmd.index', compact('rpjmd', 'all', 'opds', 'editable', 'actions') + IndicatorController::formData());
    }

    private function validated(Request $r): array
    {
        return $r->validate([
            'period_id' => 'required|exists:periods,id',
            'name' => 'required|string|max:200',
            'visi' => 'nullable|string|max:2000',
            'nomor_dokumen' => 'nullable|string|max:120',
            'tanggal_dokumen' => 'nullable|date',
        ]);
    }

    public function store(Request $r)
    {
        abort_unless(Esakip::canEdit('rpjmd'), 403);
        $m = Rpjmd::create($this->validated($r) + ['created_by' => $r->user()->id, 'status' => 'draft']);

        return redirect()->route('rpjmd.index', ['id' => $m->id])->with('ok', 'RPJMD dibuat.');
    }

    public function update(Request $r, Rpjmd $rpjmd)
    {
        abort_unless(Esakip::canEdit('rpjmd') && $rpjmd->isEditable(), 403);
        $rpjmd->update($this->validated($r));

        return back()->with('ok', 'Informasi RPJMD diperbarui.');
    }

    public function assign(Request $r, SasaranDaerah $sasaran)
    {
        IndicatorController::authorizeRoot($sasaran->tujuan->misi->rpjmd);
        $data = $r->validate(['opd_id' => 'required|exists:opds,id', 'peran' => 'required|in:utama,pendukung']);
        $sasaran->opds()->syncWithoutDetaching([$data['opd_id'] => ['peran' => $data['peran']]]);
        \App\Models\AuditLog::record('assign', $sasaran, null, $data);

        return back()->with('ok', 'OPD berhasil ditugaskan pada sasaran daerah.');
    }

    public function unassign(SasaranDaerah $sasaran, Opd $opd)
    {
        IndicatorController::authorizeRoot($sasaran->tujuan->misi->rpjmd);
        $sasaran->opds()->detach($opd->id);
        \App\Models\AuditLog::record('unassign', $sasaran, ['opd_id' => $opd->id], null);

        return back()->with('ok', 'Penugasan OPD dilepas.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\Renstra;
use App\Support\Esakip;
use App\Support\Workflow;
use Illuminate\Http\Request;

class RenstraController extends Controller
{
    public function index(Request $r)
    {
        $u = $r->user();
        $period = Esakip::activePeriod();
        $rows = Renstra::with(['opd', 'period', 'tujuans.sasarans.programs.kegiatans'])->visibleTo($u)
            ->when($r->get('status'), fn ($q, $s) => $q->where('status', $s))
            ->latest('updated_at')->get();
        $opds = Opd::where('status', 'aktif')->when($u->isOpdScoped(), fn ($q) => $q->where('id', $u->opd_id))->orderBy('name')->pluck('name', 'id');

        return view('renstra.index', compact('rows', 'opds', 'period'));
    }

    public function store(Request $r)
    {
        $u = $r->user();
        $data = $r->validate(['opd_id' => 'required|exists:opds,id', 'nomor_dokumen' => 'nullable|string|max:120']);
        abort_unless(Esakip::canEdit('opd', $data['opd_id']), 403);
        $period = Esakip::activePeriod();
        if (Renstra::where('opd_id', $data['opd_id'])->where('period_id', $period->id)->where('status', '!=', 'diarsipkan')->exists()) {
            return back()->with('err', 'Renstra OPD untuk periode aktif sudah ada.');
        }
        $m = Renstra::create($data + ['period_id' => $period->id, 'created_by' => $u->id, 'status' => 'draft']);

        return redirect()->route('renstra.show', $m)->with('ok', 'Renstra OPD dibuat. Silakan susun tujuan, sasaran, indikator dan program.');
    }

    public function show(Request $r, Renstra $renstra)
    {
        abort_unless(Esakip::canViewOpd($renstra->opd_id), 403);
        $renstra->load([
            'opd.sasaranDaerahs.tujuan.misi', 'period', 'approvalLogs.user', 'creator',
            'tujuans.sasaranDaerah', 'tujuans.sasarans.indicators.targets', 'tujuans.sasarans.indicators.satuan',
            'tujuans.sasarans.programs.indicators.targets', 'tujuans.sasarans.programs.indicators.satuan',
            'tujuans.sasarans.programs.kegiatans.indicators.targets', 'tujuans.sasarans.programs.kegiatans.indicators.satuan',
        ]);
        $editable = Esakip::canEdit('opd', $renstra->opd_id) && $renstra->isEditable();
        $actions = Workflow::available($r->user(), $renstra, 'renstra');
        $assigned = $renstra->opd->sasaranDaerahs;
        $years = $renstra->period->years();

        return view('renstra.show', compact('renstra', 'editable', 'actions', 'assigned') + ['satuans' => IndicatorController::formData()['satuans'], 'years' => $years]);
    }
}

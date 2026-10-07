<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\Realisasi;
use App\Support\Esakip;
use Illuminate\Http\Request;

class CascadingController extends Controller
{
    public function index(Request $r)
    {
        $u = $r->user();
        $year = (int) $r->get('tahun', Esakip::currentYear());
        $opdFilter = $u->isOpdScoped() ? $u->opd_id : ($r->get('opd') ? (int) $r->get('opd') : null);
        $rpjmd = Esakip::activeRpjmd();
        $ind = ['indicators.targets', 'indicators.satuan'];
        $rpjmd?->load([
            'misis.tujuans.sasarans.opds',
            'misis.tujuans.sasarans.indicators.targets', 'misis.tujuans.sasarans.indicators.satuan',
            'misis.tujuans.sasarans.tujuanOpds' => fn ($q) => $q->whereHas('renstra', fn ($w) => $w->where('status', '!=', 'diarsipkan')->where('period_id', $rpjmd->period_id)),
            'misis.tujuans.sasarans.tujuanOpds.renstra.opd',
            'misis.tujuans.sasarans.tujuanOpds.sasarans.indicators.targets', 'misis.tujuans.sasarans.tujuanOpds.sasarans.indicators.satuan',
            'misis.tujuans.sasarans.tujuanOpds.sasarans.programs.indicators.targets', 'misis.tujuans.sasarans.tujuanOpds.sasarans.programs.indicators.satuan',
            'misis.tujuans.sasarans.tujuanOpds.sasarans.programs.kegiatans.indicators.targets', 'misis.tujuans.sasarans.tujuanOpds.sasarans.programs.kegiatans.indicators.satuan',
        ]);
        $latest = Realisasi::where('year', $year)->where('status', '!=', 'diarsipkan')->orderBy('periode')->get()->keyBy('indicator_id');
        $opds = Opd::orderBy('name')->pluck('name', 'id');

        return view('cascading.index', compact('rpjmd', 'year', 'latest', 'opdFilter', 'opds') + ['years' => Esakip::years()]);
    }
}

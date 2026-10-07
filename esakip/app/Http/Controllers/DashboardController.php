<?php

namespace App\Http\Controllers;

use App\Models\Indicator;
use App\Models\Opd;
use App\Models\Realisasi;
use App\Models\Rekomendasi;
use App\Models\RencanaAksi;
use App\Models\Renstra;
use App\Support\Esakip;
use App\Support\Workflow;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $r)
    {
        $u = $r->user();
        $year = (int) $r->get('tahun', Esakip::currentYear());
        $base = Realisasi::visibleTo($u)->where('year', $year)->where('status', '!=', 'diarsipkan');
        $tw = $r->get('periode', (clone $base)->max('periode') ?? 'TW1');
        $cur = (clone $base)->where('periode', $tw)->with(['opd', 'indicator'])->get();

        $kpi = [
            'indikator' => Indicator::visibleTo($u)->count(),
            'avg' => round((float) $cur->avg('capaian'), 1),
            'tercapai' => $cur->where('status_capaian', 'tercapai')->count(),
            'total' => $cur->count(),
            'opd' => $u->isOpdScoped() ? 1 : Opd::where('status', 'aktif')->count(),
            'renstra' => Renstra::visibleTo($u)->where('status', 'ditetapkan')->count(),
        ];
        $donut = collect(['tercapai', 'perlu_perhatian', 'tidak_tercapai'])->mapWithKeys(fn ($s) => [$s => $cur->where('status_capaian', $s)->count()]);

        $prev = Realisasi::visibleTo($u)->where('year', $year - 1)->where('status', '!=', 'diarsipkan');
        $trend = collect(array_keys(config('esakip.periode')))->map(function ($p) use ($base, $prev) {
            $rows = (clone $base)->where('periode', $p)->get(['capaian', 'status_capaian']);
            $pr = (clone $prev)->where('periode', $p)->avg('capaian');

            return [
                'p' => $p,
                'avg' => $rows->count() ? round($rows->avg('capaian'), 1) : null,
                'pct' => $rows->count() ? round($rows->where('status_capaian', 'tercapai')->count() / $rows->count() * 100, 1) : null,
                'prev' => $pr !== null ? round($pr, 1) : null,
            ];
        });

        $opdBars = $cur->groupBy('opd_id')->map(fn ($g) => ['opd' => $g->first()->opd, 'avg' => round($g->avg('capaian'), 1), 'n' => $g->count()])
            ->sortByDesc('avg')->values()->take(6);

        $tasks = RencanaAksi::with(['opd', 'indicator'])->visibleTo($u)->where('year', $year)->whereIn('status', ['berjalan', 'terlambat', 'belum_mulai'])
            ->orderByRaw("CASE status WHEN 'terlambat' THEN 0 WHEN 'berjalan' THEN 1 ELSE 2 END")->limit(6)->get();
        $pending = Workflow::pendingFor($u)->take(3);
        $reks = Rekomendasi::with('evaluasi.opd')->whereIn('status', ['belum', 'proses', 'selesai'])
            ->whereHas('evaluasi', fn ($q) => $u->isOpdScoped() ? $q->where('opd_id', $u->opd_id) : $q)->latest('id')->limit(3)->get();

        $activity = \App\Models\ApprovalLog::with(['user', 'approvable'])->latest('id')->limit(5)->get()
            ->filter(fn ($l) => $l->approvable && (! $u->isOpdScoped() || ! isset($l->approvable->opd_id) || (int) $l->approvable->opd_id === (int) $u->opd_id))->take(4);

        return view('dashboard.index', compact('year', 'tw', 'kpi', 'donut', 'trend', 'opdBars', 'tasks', 'pending', 'reks', 'activity') + ['years' => Esakip::years()]);
    }

    public function opd(Request $r, Opd $opd)
    {
        abort_unless(Esakip::canViewOpd($opd->id), 403);
        $year = (int) $r->get('tahun', Esakip::currentYear());
        $indicators = Indicator::with(['satuan', 'targets', 'owner', 'realisasis' => fn ($q) => $q->where('year', $year)->where('status', '!=', 'diarsipkan')->withCount('evidences')->orderBy('periode')])
            ->where('opd_id', $opd->id)->get()->groupBy('owner_type');
        $real = Realisasi::where('opd_id', $opd->id)->where('year', $year)->where('status', '!=', 'diarsipkan');
        $summary = ['avg' => round((float) (clone $real)->avg('capaian'), 1), 'n' => (clone $real)->count(), 'ok' => (clone $real)->where('status_capaian', 'tercapai')->count()];

        return view('dashboard.opd', compact('opd', 'year', 'indicators', 'summary') + ['years' => Esakip::years()]);
    }
}

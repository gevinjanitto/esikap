<?php

namespace App\Http\Controllers;

use App\Models\Indicator;
use App\Models\Opd;
use App\Models\Realisasi;
use App\Support\Esakip;
use App\Support\Workflow;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RealisasiController extends Controller
{
    public function index(Request $r)
    {
        $u = $r->user();
        $year = (int) $r->get('tahun', Esakip::currentYear());
        $q = Realisasi::with(['indicator.satuan', 'indicator.owner', 'opd', 'evidences', 'approvalLogs.user'])->visibleTo($u)->where('year', $year)
            ->when($r->get('periode'), fn ($q, $p) => $q->where('periode', $p))
            ->when($r->get('status_capaian'), fn ($q, $s) => $q->where('status_capaian', $s))
            ->when($r->get('status'), fn ($q, $s) => $q->where('status', $s))
            ->when(! $u->isOpdScoped() && $r->get('opd'), fn ($q) => $q->where('opd_id', $r->get('opd')))
            ->orderBy('opd_id')->orderBy('periode');
        $rows = $q->paginate(15)->withQueryString();
        $rows->getCollection()->each(fn ($x) => $x->wfActions = Workflow::available($u, $x, 'realisasi'));

        $indicators = Indicator::with(['targets', 'satuan', 'opd'])->visibleTo($u)->whereNotNull('opd_id')->orderBy('opd_id')->get()
            ->map(fn ($i) => [
                'id' => $i->id, 'label' => $i->name, 'opd' => $i->opd?->name, 'level' => $i->level_label, 'satuan' => $i->satuan?->name,
                'formula' => $i->formula, 'definition' => $i->definition, 'targets' => $i->targets->pluck('value', 'year'),
            ]);
        $opds = Opd::orderBy('name')->pluck('name', 'id');
        $canInput = Esakip::canEdit('opd');

        return view('realisasi.index', compact('rows', 'year', 'indicators', 'opds', 'canInput') + ['years' => Esakip::years()]);
    }

    private function validated(Request $r): array
    {
        return $r->validate([
            'indicator_id' => 'required|exists:indicators,id',
            'year' => 'required|integer',
            'periode' => ['required', Rule::in(array_keys(config('esakip.periode')))],
            'target' => 'required|numeric|not_in:0',
            'realisasi' => 'required|numeric',
            'analisis' => 'nullable|string|max:3000',
            'files' => 'array|max:5',
            'files.*' => 'file|max:'.config('esakip.upload.max_kb').'|mimes:'.config('esakip.upload.mimes'),
            'link' => 'nullable|url|max:255',
        ], ['target.not_in' => 'Target periode wajib diisi dan tidak boleh nol.']);
    }

    private function fill(array $d, Indicator $ind): array
    {
        $cap = Esakip::capaian($ind->formula, (float) $d['target'], (float) $d['realisasi']);

        return [
            'indicator_id' => $ind->id, 'opd_id' => $ind->opd_id, 'year' => $d['year'], 'periode' => $d['periode'],
            'target' => $d['target'], 'realisasi' => $d['realisasi'], 'analisis' => $d['analisis'] ?? null,
            'capaian' => $cap, 'deviasi' => (float) $d['realisasi'] - (float) $d['target'], 'status_capaian' => Esakip::statusCapaian($cap),
        ];
    }

    public function store(Request $r)
    {
        $d = $this->validated($r);
        $ind = Indicator::findOrFail($d['indicator_id']);
        abort_unless($ind->opd_id && Esakip::canEdit('opd', $ind->opd_id), 403, 'Anda tidak berwenang menginput realisasi indikator ini.');
        if (Realisasi::where('indicator_id', $ind->id)->where('year', $d['year'])->where('periode', $d['periode'])->exists()) {
            return back()->withInput()->with('err', 'Realisasi untuk indikator dan periode tersebut sudah ada. Silakan ubah data yang ada.');
        }
        $m = Realisasi::create($this->fill($d, $ind) + ['created_by' => $r->user()->id, 'status' => 'draft']);
        EvidenceController::saveFiles($m, $r->file('files', []), $d['link'] ?? null);

        return back()->with('ok', 'Realisasi tersimpan. Capaian: '.Esakip::num($m->capaian).'%');
    }

    public function update(Request $r, Realisasi $realisasi)
    {
        abort_unless(Esakip::canEdit('opd', $realisasi->opd_id) && $realisasi->isEditable(), 403);
        $d = $this->validated($r);
        $realisasi->update($this->fill(array_merge($d, ['periode' => $realisasi->periode, 'year' => $realisasi->year]), $realisasi->indicator));
        EvidenceController::saveFiles($realisasi, $r->file('files', []), $d['link'] ?? null);

        return back()->with('ok', 'Realisasi diperbarui. Capaian: '.Esakip::num($realisasi->capaian).'%');
    }
}

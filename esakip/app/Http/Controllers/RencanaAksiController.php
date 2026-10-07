<?php

namespace App\Http\Controllers;

use App\Models\Indicator;
use App\Models\Opd;
use App\Models\RencanaAksi;
use App\Support\Esakip;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RencanaAksiController extends Controller
{
    public function index(Request $r)
    {
        $u = $r->user();
        $year = (int) $r->get('tahun', Esakip::currentYear());
        $rows = RencanaAksi::with(['indicator.satuan', 'opd', 'evidences'])->visibleTo($u)->where('year', $year)
            ->when($r->get('triwulan'), fn ($q, $t) => $q->where('triwulan', $t))
            ->when($r->get('status'), fn ($q, $s) => $q->where('status', $s))
            ->when(! $u->isOpdScoped() && $r->get('opd'), fn ($q) => $q->where('opd_id', $r->get('opd')))
            ->orderBy('triwulan')->paginate(15)->withQueryString();
        $indicators = Indicator::with('opd')->visibleTo($u)->whereNotNull('opd_id')->get()
            ->mapWithKeys(fn ($i) => [$i->id => ($u->isOpdScoped() ? '' : '['.($i->opd?->singkatan ?: $i->opd?->name).'] ').$i->name]);
        $opds = Opd::orderBy('name')->pluck('name', 'id');
        $stats = RencanaAksi::visibleTo($u)->where('year', $year)->selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');

        return view('renaksi.index', compact('rows', 'year', 'indicators', 'opds', 'stats') + ['years' => Esakip::years(), 'canInput' => Esakip::canEdit('opd')]);
    }

    private function validated(Request $r): array
    {
        return $r->validate([
            'indicator_id' => 'required|exists:indicators,id',
            'year' => 'required|integer',
            'aktivitas' => 'required|string|max:1000',
            'pic' => 'required|string|max:200',
            'triwulan' => ['required', Rule::in(array_keys(config('esakip.periode')))],
            'target' => 'required|string|max:200',
            'status' => ['required', Rule::in(array_keys(Esakip::RENAKSI_STATUS))],
            'files' => 'array|max:5',
            'files.*' => 'file|max:'.config('esakip.upload.max_kb').'|mimes:'.config('esakip.upload.mimes'),
        ]);
    }

    public function store(Request $r)
    {
        $d = $this->validated($r);
        $ind = Indicator::findOrFail($d['indicator_id']);
        abort_unless($ind->opd_id && Esakip::canEdit('opd', $ind->opd_id), 403);
        $m = RencanaAksi::create(collect($d)->except('files')->all() + ['opd_id' => $ind->opd_id, 'created_by' => $r->user()->id]);
        EvidenceController::saveFiles($m, $r->file('files', []));

        return back()->with('ok', 'Rencana aksi ditambahkan.');
    }

    public function update(Request $r, RencanaAksi $renaksi)
    {
        abort_unless(Esakip::canEdit('opd', $renaksi->opd_id), 403);
        $d = $this->validated($r);
        $renaksi->update(collect($d)->except('files')->all());
        EvidenceController::saveFiles($renaksi, $r->file('files', []));

        return back()->with('ok', 'Rencana aksi diperbarui.');
    }

    public function destroy(RencanaAksi $renaksi)
    {
        abort_unless(Esakip::canEdit('opd', $renaksi->opd_id), 403);
        $renaksi->delete();

        return back()->with('ok', 'Rencana aksi dihapus.');
    }
}

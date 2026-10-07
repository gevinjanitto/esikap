<?php

namespace App\Http\Controllers;

use App\Models\Indicator;
use App\Models\Opd;
use App\Models\PerjanjianKinerja;
use App\Models\Renja;
use App\Support\Esakip;
use App\Support\Workflow;
use Illuminate\Http\Request;

class AnnualDocController extends Controller
{
    private function cfg(string $kind): array
    {
        return [
            'renja' => ['class' => Renja::class, 'label' => 'Renja Tahunan', 'title' => 'Rencana Kerja Tahunan', 'menu' => 'renja'],
            'pk' => ['class' => PerjanjianKinerja::class, 'label' => 'Perjanjian Kinerja', 'title' => 'Perjanjian Kinerja', 'menu' => 'pk'],
        ][$kind];
    }

    private function find(string $kind, int $id)
    {
        $cfg = $this->cfg($kind);
        abort_unless(Esakip::canMenu($cfg['menu']), 403);
        $doc = $cfg['class']::with(['opd', 'items.indicator.satuan', 'approvalLogs.user', 'creator'])->findOrFail($id);
        abort_unless(Esakip::canViewOpd($doc->opd_id), 403);

        return [$cfg, $doc];
    }

    public function index(Request $r, string $kind)
    {
        $cfg = $this->cfg($kind);
        abort_unless(Esakip::canMenu($cfg['menu']), 403);
        $u = $r->user();
        $year = (int) $r->get('tahun', Esakip::currentYear());
        $rows = $cfg['class']::with(['opd', 'items'])->withCount('items')->visibleTo($u)->where('year', $year)->latest('updated_at')->get();
        $opds = Opd::where('status', 'aktif')->when($u->isOpdScoped(), fn ($q) => $q->where('id', $u->opd_id))->orderBy('name')->pluck('name', 'id');

        return view('annual.index', compact('cfg', 'kind', 'rows', 'opds', 'year') + ['years' => Esakip::years()]);
    }

    private function headerRules(string $kind): array
    {
        return $kind === 'pk'
            ? ['pihak_pertama' => 'required|string|max:150', 'jabatan_pertama' => 'required|string|max:150', 'pihak_kedua' => 'required|string|max:150', 'jabatan_kedua' => 'required|string|max:150', 'catatan' => 'nullable|string|max:2000']
            : ['catatan' => 'nullable|string|max:2000'];
    }

    public function store(Request $r, string $kind)
    {
        $cfg = $this->cfg($kind);
        $data = $r->validate(['opd_id' => 'required|exists:opds,id', 'year' => 'required|integer'] + $this->headerRules($kind));
        abort_unless(Esakip::canEdit('opd', $data['opd_id']), 403);
        if ($cfg['class']::where('opd_id', $data['opd_id'])->where('year', $data['year'])->where('status', '!=', 'diarsipkan')->exists()) {
            return back()->with('err', $cfg['label'].' untuk OPD dan tahun tersebut sudah ada.');
        }
        $doc = $cfg['class']::create($data + ['created_by' => $r->user()->id, 'status' => 'draft']);

        return redirect()->route('annual.show', [$kind, $doc->id])->with('ok', $cfg['label'].' dibuat.');
    }

    public function show(Request $r, string $kind, int $id)
    {
        [$cfg, $doc] = $this->find($kind, $id);
        $editable = Esakip::canEdit('opd', $doc->opd_id) && $doc->isEditable();
        $actions = Workflow::available($r->user(), $doc, $kind);
        $used = $doc->items->pluck('indicator_id')->all();
        $indicators = Indicator::with(['targets', 'satuan'])->where('opd_id', $doc->opd_id)->whereNotIn('id', $used)->get()
            ->map(fn ($i) => ['id' => $i->id, 'label' => '['.$i->level_label.'] '.$i->name, 'target' => $i->targetFor($doc->year), 'satuan' => $i->satuan?->name]);

        return view('annual.show', compact('cfg', 'kind', 'doc', 'editable', 'actions', 'indicators'));
    }

    public function update(Request $r, string $kind, int $id)
    {
        [, $doc] = $this->find($kind, $id);
        abort_unless(Esakip::canEdit('opd', $doc->opd_id) && $doc->isEditable(), 403);
        $doc->update($r->validate($this->headerRules($kind)));

        return back()->with('ok', 'Informasi dokumen diperbarui.');
    }

    public function addItem(Request $r, string $kind, int $id)
    {
        [$cfg, $doc] = $this->find($kind, $id);
        abort_unless(Esakip::canEdit('opd', $doc->opd_id) && $doc->isEditable(), 403);
        $data = $r->validate(['indicator_id' => 'required|exists:indicators,id', 'target' => 'required|numeric', 'pagu' => 'nullable|numeric|min:0', 'keterangan' => 'nullable|string|max:1000']);
        $ind = Indicator::findOrFail($data['indicator_id']);
        abort_unless((int) $ind->opd_id === (int) $doc->opd_id, 422, 'Indikator bukan milik OPD ini.');
        $doc->items()->create($data);

        return back()->with('ok', 'Indikator ditambahkan ke '.$cfg['label'].'.');
    }

    public function removeItem(string $kind, int $id, int $item)
    {
        [, $doc] = $this->find($kind, $id);
        abort_unless(Esakip::canEdit('opd', $doc->opd_id) && $doc->isEditable(), 403);
        $doc->items()->findOrFail($item)->delete();

        return back()->with('ok', 'Item dihapus.');
    }

    public function print(string $kind, int $id)
    {
        [$cfg, $doc] = $this->find($kind, $id);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('annual.print', compact('cfg', 'kind', 'doc'))->setPaper('a4', 'portrait')->stream($kind.'-'.\Illuminate\Support\Str::slug($doc->opd->singkatan ?? $doc->opd->name).'-'.$doc->year.'.pdf');
    }
}

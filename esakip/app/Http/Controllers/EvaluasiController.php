<?php

namespace App\Http\Controllers;

use App\Models\Evaluasi;
use App\Models\Opd;
use App\Models\Realisasi;
use App\Models\Rekomendasi;
use App\Support\Esakip;
use Illuminate\Http\Request;

class EvaluasiController extends Controller
{
    public function index(Request $r)
    {
        $u = $r->user();
        $rows = Evaluasi::with(['opd', 'evaluator', 'rekomendasis'])->visibleTo($u)
            ->when($r->get('tahun'), fn ($q, $y) => $q->where('year', $y))->latest('year')->latest('id')->get();
        $opds = Opd::where('status', 'aktif')->orderBy('name')->pluck('name', 'id');

        return view('evaluasi.index', compact('rows', 'opds') + ['years' => range(Esakip::currentYear() - 2, Esakip::currentYear()), 'canEval' => Esakip::canEdit('evaluasi')]);
    }

    private function validated(Request $r): array
    {
        return $r->validate(['nilai' => 'required|numeric|min:0|max:100', 'catatan' => 'nullable|string|max:5000', 'status' => 'required|in:draft,final']);
    }

    public function store(Request $r)
    {
        abort_unless(Esakip::canEdit('evaluasi'), 403);
        $d = $r->validate(['opd_id' => 'required|exists:opds,id', 'year' => 'required|integer']) + $this->validated($r);
        $m = Evaluasi::create($d + ['predikat' => Esakip::predikat($d['nilai']), 'evaluator_id' => $r->user()->id]);

        return redirect()->route('evaluasi.show', $m)->with('ok', 'Evaluasi dibuat.');
    }

    public function show(Evaluasi $evaluasi)
    {
        abort_unless(Esakip::canViewOpd($evaluasi->opd_id), 403);
        $evaluasi->load(['opd', 'evaluator', 'rekomendasis.evidences']);
        $real = Realisasi::where('opd_id', $evaluasi->opd_id)->where('year', $evaluasi->year)->where('status', '!=', 'diarsipkan');
        $summary = ['avg' => (clone $real)->avg('capaian'), 'count' => (clone $real)->count(), 'tercapai' => (clone $real)->where('status_capaian', 'tercapai')->count()];

        return view('evaluasi.show', compact('evaluasi', 'summary') + [
            'canEval' => Esakip::canEdit('evaluasi'),
            'canTl' => Esakip::canEdit('tindak_lanjut', $evaluasi->opd_id),
        ]);
    }

    public function update(Request $r, Evaluasi $evaluasi)
    {
        abort_unless(Esakip::canEdit('evaluasi'), 403);
        $d = $this->validated($r);
        $evaluasi->update($d + ['predikat' => Esakip::predikat($d['nilai'])]);

        return back()->with('ok', 'Evaluasi diperbarui.');
    }

    public function addRekomendasi(Request $r, Evaluasi $evaluasi)
    {
        abort_unless(Esakip::canEdit('evaluasi'), 403);
        $evaluasi->rekomendasis()->create($r->validate(['uraian' => 'required|string|max:3000', 'batas_waktu' => 'nullable|date']));

        return back()->with('ok', 'Rekomendasi ditambahkan.');
    }

    public function tindakLanjut(Request $r, Rekomendasi $rekomendasi)
    {
        abort_unless(Esakip::canEdit('tindak_lanjut', $rekomendasi->evaluasi->opd_id) && $rekomendasi->status !== 'terverifikasi', 403);
        $d = $r->validate([
            'tindak_lanjut' => 'required|string|max:5000',
            'status' => 'required|in:proses,selesai',
            'files' => 'array|max:5',
            'files.*' => 'file|max:'.config('esakip.upload.max_kb').'|mimes:'.config('esakip.upload.mimes'),
        ]);
        $rekomendasi->update(['tindak_lanjut' => $d['tindak_lanjut'], 'status' => $d['status']]);
        EvidenceController::saveFiles($rekomendasi, $r->file('files', []));

        return back()->with('ok', 'Tindak lanjut disimpan.');
    }

    public function verifikasi(Request $r, Rekomendasi $rekomendasi)
    {
        abort_unless(Esakip::canEdit('evaluasi'), 403);
        $d = $r->validate(['status' => 'required|in:terverifikasi,proses', 'catatan_verifikasi' => 'nullable|string|max:2000']);
        $rekomendasi->update($d);

        return back()->with('ok', $d['status'] === 'terverifikasi' ? 'Tindak lanjut terverifikasi.' : 'Tindak lanjut dikembalikan ke OPD.');
    }
}

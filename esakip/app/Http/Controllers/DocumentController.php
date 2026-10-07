<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Opd;
use App\Support\Esakip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function index(Request $r)
    {
        $u = $r->user();
        $rows = Document::with(['opd', 'uploader', 'period'])
            ->when($u->isOpdScoped(), fn ($q) => $q->where(fn ($w) => $w->where('opd_id', $u->opd_id)->orWhereNull('opd_id')))
            ->when($r->boolean('arsip'), fn ($q) => $q->whereNotNull('archived_at'), fn ($q) => $q->whereNull('archived_at'))
            ->when($r->get('jenis'), fn ($q, $j) => $q->where('jenis', $j))
            ->when($r->get('q'), fn ($q, $s) => $q->where('title', 'like', "%$s%"))
            ->latest('id')->paginate(12)->withQueryString();
        $opds = Opd::orderBy('name')->when($u->isOpdScoped(), fn ($q) => $q->where('id', $u->opd_id))->pluck('name', 'id');

        return view('dokumen.index', compact('rows', 'opds') + ['canUpload' => Esakip::canEdit('dokumen')]);
    }

    public function store(Request $r)
    {
        $u = $r->user();
        $d = $r->validate([
            'title' => 'required|string|max:200',
            'jenis' => ['required', Rule::in(array_keys(config('esakip.doc_types')))],
            'nomor' => 'nullable|string|max:120',
            'tanggal' => 'nullable|date',
            'opd_id' => 'nullable|exists:opds,id',
            'file' => 'required|file|max:'.config('esakip.upload.max_kb').'|mimes:'.config('esakip.upload.mimes'),
        ]);
        if ($u->isOpdScoped()) {
            $d['opd_id'] = $u->opd_id;
        }
        abort_unless(Esakip::canEdit('dokumen', $d['opd_id'] ?? null), 403);
        $f = $r->file('file');
        $version = (int) Document::where('title', $d['title'])->where('jenis', $d['jenis'])->where('opd_id', $d['opd_id'] ?? null)->max('version') + 1;
        Document::create(collect($d)->except('file')->all() + [
            'version' => $version, 'period_id' => Esakip::activePeriod()?->id,
            'file_path' => $f->store('documents', 'local'), 'original_name' => $f->getClientOriginalName(),
            'size' => $f->getSize(), 'uploaded_by' => $u->id,
        ]);

        return back()->with('ok', "Dokumen diunggah (versi {$version}).");
    }

    public function download(Document $document)
    {
        abort_unless(Esakip::canViewOpd($document->opd_id), 403);
        if (! Storage::disk('local')->exists($document->file_path) && $document->original_name === basename($document->file_path)) {
            Storage::disk('local')->put($document->file_path, \Database\Seeders\DatabaseSeeder::pdf($document->title));
        }
        abort_unless(Storage::disk('local')->exists($document->file_path), 404, 'Berkas tidak ditemukan.');

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }

    public function archive(Request $r, Document $document)
    {
        abort_unless(Esakip::canEdit('dokumen', $document->opd_id), 403);
        $d = $r->validate(['archive_reason' => 'required|string|max:255']);
        $document->update($d + ['archived_at' => now()]);

        return back()->with('ok', 'Dokumen diarsipkan.');
    }
}

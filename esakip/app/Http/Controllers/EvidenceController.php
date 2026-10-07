<?php

namespace App\Http\Controllers;

use App\Models\Evidence;
use App\Models\Realisasi;
use App\Models\Rekomendasi;
use App\Models\RencanaAksi;
use App\Support\Esakip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EvidenceController extends Controller
{
    private const TYPES = ['realisasi' => Realisasi::class, 'rencana_aksi' => RencanaAksi::class, 'rekomendasi' => Rekomendasi::class];

    public static function saveFiles($model, array $files, ?string $link = null): void
    {
        foreach ($files as $f) {
            $model->evidences()->create([
                'file_path' => $f->store('evidences/'.date('Y/m'), 'local'),
                'original_name' => $f->getClientOriginalName(),
                'mime' => $f->getClientMimeType(),
                'size' => $f->getSize(),
                'uploaded_by' => auth()->id(),
            ]);
        }
        if ($link) {
            $model->evidences()->create(['link' => $link, 'original_name' => $link, 'uploaded_by' => auth()->id()]);
        }
    }

    private static function opdOf($parent)
    {
        return $parent instanceof Rekomendasi ? $parent->evaluasi->opd_id : $parent->opd_id;
    }

    private static function canModify($parent): bool
    {
        if ($parent instanceof Rekomendasi) {
            return Esakip::canEdit('tindak_lanjut', $parent->evaluasi->opd_id) && $parent->status !== 'terverifikasi';
        }
        if ($parent instanceof Realisasi && ! $parent->isEditable()) {
            return false;
        }

        return Esakip::canEdit('opd', $parent->opd_id);
    }

    public function store(Request $r, string $type, int $id)
    {
        abort_unless(isset(self::TYPES[$type]), 404);
        $parent = self::TYPES[$type]::findOrFail($id);
        abort_unless(self::canModify($parent), 403, 'Anda tidak dapat menambah bukti dukung pada data ini.');
        $r->validate([
            'files' => 'array|max:5',
            'files.*' => 'file|max:'.config('esakip.upload.max_kb').'|mimes:'.config('esakip.upload.mimes'),
            'link' => 'nullable|url|max:255',
        ]);
        self::saveFiles($parent, $r->file('files', []), $r->input('link'));

        return back()->with('ok', 'Bukti dukung diunggah.');
    }

    public function download(Evidence $evidence)
    {
        abort_unless(Esakip::canViewOpd(self::opdOf($evidence->evidenceable)), 403);
        if ($evidence->link) {
            return redirect()->away($evidence->link);
        }
        abort_unless(Storage::disk('local')->exists($evidence->file_path), 404, 'Berkas tidak ditemukan.');

        return Storage::disk('local')->download($evidence->file_path, $evidence->original_name);
    }

    public function destroy(Evidence $evidence)
    {
        abort_unless(self::canModify($evidence->evidenceable), 403);
        $evidence->delete();

        return back()->with('ok', 'Bukti dukung dihapus.');
    }
}

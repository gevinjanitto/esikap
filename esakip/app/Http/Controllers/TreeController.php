<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\Misi;
use App\Models\Program;
use App\Models\Renstra;
use App\Models\Rpjmd;
use App\Models\SasaranDaerah;
use App\Models\SasaranOpd;
use App\Models\TujuanDaerah;
use App\Models\TujuanOpd;
use Illuminate\Http\Request;

class TreeController extends Controller
{
    private const MAP = [
        'misi' => [Misi::class, 'rpjmd_id', Rpjmd::class, 'Misi'],
        'tujuan_daerah' => [TujuanDaerah::class, 'misi_id', Misi::class, 'Tujuan Daerah'],
        'sasaran_daerah' => [SasaranDaerah::class, 'tujuan_daerah_id', TujuanDaerah::class, 'Sasaran Daerah'],
        'tujuan_opd' => [TujuanOpd::class, 'renstra_id', Renstra::class, 'Tujuan OPD'],
        'sasaran_opd' => [SasaranOpd::class, 'tujuan_opd_id', TujuanOpd::class, 'Sasaran OPD'],
        'program' => [Program::class, 'sasaran_opd_id', SasaranOpd::class, 'Program'],
        'kegiatan' => [Kegiatan::class, 'program_id', Program::class, 'Kegiatan'],
    ];

    private function root($m)
    {
        return match (true) {
            $m instanceof Rpjmd, $m instanceof Renstra => $m,
            $m instanceof Misi => $m->rpjmd,
            $m instanceof TujuanDaerah => $m->misi->rpjmd,
            $m instanceof SasaranDaerah => $m->tujuan->misi->rpjmd,
            $m instanceof TujuanOpd => $m->renstra,
            $m instanceof SasaranOpd => $m->tujuan->renstra,
            $m instanceof Program => $m->sasaranOpd->tujuan->renstra,
            $m instanceof Kegiatan => $m->program->sasaranOpd->tujuan->renstra,
        };
    }

    private function cfg(string $type): array
    {
        abort_unless(isset(self::MAP[$type]), 404);

        return self::MAP[$type];
    }

    private function data(Request $r, string $type, $root): array
    {
        $rules = ['code' => 'required|string|max:50', 'name' => 'required|string|max:2000'];
        if ($type === 'tujuan_opd') {
            $rules['sasaran_daerah_id'] = 'nullable|exists:sasaran_daerahs,id';
        }
        $data = $r->validate($rules);
        if ($type === 'tujuan_opd' && ! empty($data['sasaran_daerah_id'])) {
            $assigned = $root->opd->sasaranDaerahs()->where('sasaran_daerahs.id', $data['sasaran_daerah_id'])->exists();
            abort_unless($assigned, 422, 'Sasaran daerah tersebut tidak ditugaskan kepada OPD ini.');
        }

        return $data;
    }

    public function store(Request $r, string $type)
    {
        [$class, $fk, $parentClass, $label] = $this->cfg($type);
        $parent = $parentClass::findOrFail($r->input('parent_id'));
        $root = $this->root($parent);
        IndicatorController::authorizeRoot($root);
        $data = $this->data($r, $type, $root);
        $data[$fk] = $parent->id;
        if ($type === 'program') {
            $data['opd_id'] = $root->opd_id;
        }
        $class::create($data);

        return back()->with('ok', "$label berhasil ditambahkan.");
    }

    public function update(Request $r, string $type, int $id)
    {
        [$class, , , $label] = $this->cfg($type);
        $m = $class::findOrFail($id);
        $root = $this->root($m);
        IndicatorController::authorizeRoot($root);
        $m->update($this->data($r, $type, $root));

        return back()->with('ok', "$label berhasil diperbarui.");
    }

    public function destroy(string $type, int $id)
    {
        [$class, , , $label] = $this->cfg($type);
        $m = $class::findOrFail($id);
        IndicatorController::authorizeRoot($this->root($m));
        try {
            $m->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('err', "$label tidak dapat dihapus karena sudah dipakai data lain (realisasi/PK/Renja).");
        }

        return back()->with('ok', "$label dihapus.");
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\Period;
use App\Models\Satuan;
use App\Models\Unit;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    private function res(string $res): array
    {
        $map = [
            'periode' => ['model' => Period::class, 'title' => 'Periode RPJMD', 'sub' => 'Kelola periode perencanaan jangka menengah', 'fields' => [
                'name' => ['Nama Periode', 'text', 'required|string|max:120'],
                'start_year' => ['Tahun Awal', 'number', 'required|integer|min:2000|max:2100'],
                'end_year' => ['Tahun Akhir', 'number', 'required|integer|gte:start_year|max:2100'],
                'is_active' => ['Status', 'select', 'required|boolean', ['1' => 'Aktif', '0' => 'Nonaktif']],
            ]],
            'opd' => ['model' => Opd::class, 'title' => 'Organisasi Perangkat Daerah', 'sub' => 'Daftar OPD beserta tipe dan statusnya', 'fields' => [
                'code' => ['Kode', 'text', 'required|string|max:30'],
                'name' => ['Nama OPD', 'text', 'required|string|max:200'],
                'singkatan' => ['Singkatan', 'text', 'nullable|string|max:30'],
                'type' => ['Tipe', 'select', 'required|string', config('esakip.opd_types')],
                'status' => ['Status', 'select', 'required|in:aktif,nonaktif', ['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']],
            ]],
            'unit' => ['model' => Unit::class, 'title' => 'Unit Kerja', 'sub' => 'Bidang / sub bagian / UPT di bawah OPD', 'fields' => [
                'opd_id' => ['OPD Induk', 'select', 'required|exists:opds,id', 'opd'],
                'code' => ['Kode', 'text', 'required|string|max:30'],
                'name' => ['Nama Unit', 'text', 'required|string|max:200'],
            ]],
            'satuan' => ['model' => Satuan::class, 'title' => 'Satuan Ukur', 'sub' => 'Satuan untuk indikator kinerja', 'fields' => [
                'code' => ['Kode', 'text', 'required|string|max:20'],
                'name' => ['Nama Satuan', 'text', 'required|string|max:100'],
            ]],
        ];
        abort_unless(isset($map[$res]), 404);
        foreach ($map[$res]['fields'] as $k => $f) {
            if (($f[3] ?? null) === 'opd') {
                $map[$res]['fields'][$k][3] = Opd::orderBy('name')->pluck('name', 'id')->all();
            }
        }

        return $map[$res];
    }

    public function index(Request $r, string $res)
    {
        $cfg = $this->res($res);
        $q = $cfg['model']::query()->latest('id');
        if ($s = $r->get('q')) {
            $q->where('name', 'like', "%{$s}%");
        }
        if ($res === 'unit') {
            $q->with('opd');
        }
        $rows = $q->paginate(15)->withQueryString();

        return view('master.index', compact('cfg', 'res', 'rows'));
    }

    public function store(Request $r, string $res)
    {
        $cfg = $this->res($res);
        $data = $r->validate(collect($cfg['fields'])->map(fn ($f) => $f[2])->all());
        $this->beforeSave($res, $data);
        $cfg['model']::create($data);

        return back()->with('ok', $cfg['title'].' berhasil ditambahkan.');
    }

    public function update(Request $r, string $res, int $id)
    {
        $cfg = $this->res($res);
        $data = $r->validate(collect($cfg['fields'])->map(fn ($f) => $f[2])->all());
        $this->beforeSave($res, $data, $id);
        $cfg['model']::findOrFail($id)->update($data);

        return back()->with('ok', $cfg['title'].' berhasil diperbarui.');
    }

    public function destroy(string $res, int $id)
    {
        $cfg = $this->res($res);
        $m = $cfg['model']::findOrFail($id);
        if ($res === 'periode' && $m->rpjmds()->exists()) {
            return back()->with('err', 'Periode sudah memiliki RPJMD sehingga tidak dapat dihapus.');
        }
        $m->delete();

        return back()->with('ok', $cfg['title'].' berhasil diarsipkan.');
    }

    private function beforeSave(string $res, array $data, ?int $id = null): void
    {
        if ($res === 'periode' && ! empty($data['is_active'])) {
            Period::where('id', '!=', $id)->update(['is_active' => false]);
        }
    }
}

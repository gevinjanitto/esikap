<?php

namespace App\Http\Controllers;

use App\Models\Indicator;
use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Program;
use App\Models\SasaranDaerah;
use App\Models\SasaranOpd;
use App\Models\Satuan;
use App\Support\Esakip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class IndicatorController extends Controller
{
    private const OWNERS = ['sasaran_daerah' => SasaranDaerah::class, 'sasaran_opd' => SasaranOpd::class, 'program' => Program::class, 'kegiatan' => Kegiatan::class];

    public function index(Request $r)
    {
        $u = $r->user();
        $q = Indicator::with(['satuan', 'opd', 'targets', 'owner'])->visibleTo($u)->latest('id');
        if ($s = $r->get('q')) {
            $q->where('name', 'like', "%$s%");
        }
        if ($lvl = $r->get('level')) {
            $q->where('owner_type', $lvl);
        }
        if (($opd = $r->get('opd')) && ! $u->isOpdScoped()) {
            $q->where('opd_id', $opd);
        }
        $rows = $q->paginate(15)->withQueryString();
        $opds = Opd::orderBy('name')->pluck('name', 'id');

        return view('master.indikator', compact('rows', 'opds'));
    }

    public static function root($owner)
    {
        return match (true) {
            $owner instanceof SasaranDaerah => $owner->tujuan->misi->rpjmd,
            $owner instanceof SasaranOpd => $owner->tujuan->renstra,
            $owner instanceof Program => $owner->sasaranOpd->tujuan->renstra,
            $owner instanceof Kegiatan => $owner->program->sasaranOpd->tujuan->renstra,
        };
    }

    public static function authorizeRoot($root): void
    {
        $area = $root instanceof \App\Models\Rpjmd ? 'rpjmd' : 'opd';
        abort_unless(Esakip::canEdit($area, $root->opd_id ?? null), 403, 'Anda tidak berwenang mengubah data ini.');
        abort_unless($root->isEditable(), 422, 'Dokumen sudah dikunci / sedang direview. Ajukan revisi terlebih dahulu.');
    }

    private function validated(Request $r): array
    {
        return $r->validate([
            'name' => 'required|string|max:500',
            'code' => 'nullable|string|max:30',
            'type' => ['required', Rule::in(array_keys(config('esakip.indicator_types')))],
            'satuan_id' => 'required|exists:satuans,id',
            'definition' => 'required|string|max:2000',
            'formula' => ['required', Rule::in(array_keys(config('esakip.formulas')))],
            'baseline' => 'nullable|numeric',
            'data_source' => 'nullable|string|max:200',
            'targets' => 'array',
            'targets.*' => 'nullable|numeric',
        ], ['definition.required' => 'Definisi operasional wajib diisi sebelum indikator digunakan.']);
    }

    public function store(Request $r)
    {
        $r->validate(['owner_type' => ['required', Rule::in(array_keys(self::OWNERS))], 'owner_id' => 'required|integer']);
        $owner = self::OWNERS[$r->owner_type]::findOrFail($r->owner_id);
        $root = self::root($owner);
        self::authorizeRoot($root);
        $data = $this->validated($r);

        DB::transaction(function () use ($data, $owner, $r, $root) {
            $ind = Indicator::create(collect($data)->except('targets')->all() + [
                'owner_type' => $r->owner_type, 'owner_id' => $owner->id, 'opd_id' => $root->opd_id ?? null,
            ]);
            $this->syncTargets($ind, $data['targets'] ?? []);
        });

        return back()->with('ok', 'Indikator berhasil ditambahkan.');
    }

    public function update(Request $r, Indicator $indicator)
    {
        self::authorizeRoot(self::root($indicator->owner));
        $data = $this->validated($r);
        DB::transaction(function () use ($indicator, $data) {
            $indicator->update(collect($data)->except('targets')->all());
            $this->syncTargets($indicator, $data['targets'] ?? []);
        });

        return back()->with('ok', 'Indikator berhasil diperbarui.');
    }

    public function destroy(Indicator $indicator)
    {
        self::authorizeRoot(self::root($indicator->owner));
        if ($indicator->realisasis()->exists()) {
            return back()->with('err', 'Indikator sudah memiliki realisasi sehingga tidak dapat dihapus.');
        }
        $indicator->delete();

        return back()->with('ok', 'Indikator dihapus.');
    }

    private function syncTargets(Indicator $ind, array $targets): void
    {
        foreach ($targets as $year => $val) {
            if ($val === null || $val === '') {
                $ind->targets()->where('year', $year)->delete();
            } else {
                $ind->targets()->updateOrCreate(['year' => (int) $year], ['value' => $val]);
            }
        }
    }

    public static function formData(): array
    {
        return ['satuans' => Satuan::orderBy('name')->pluck('name', 'id'), 'years' => Esakip::years()];
    }
}

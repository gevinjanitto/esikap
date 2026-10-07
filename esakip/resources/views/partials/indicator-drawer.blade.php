@php
    $defaults = ['owner_type' => '', 'owner_id' => '', 'name' => '', 'code' => '', 'type' => 'outcome', 'satuan_id' => '', 'definition' => '', 'formula' => 'positif', 'baseline' => '', 'data_source' => '', 'targets' => (object) collect($years)->mapWithKeys(fn ($y) => [$y => ''])->all()];
@endphp
<x-drawer name="indikator" title="Tambah Indikator" sub="Indikator Kinerja" :action="route('indikator.store')" :defaults="$defaults" wide>
    <input type="hidden" name="owner_type" :value="form.owner_type">
    <input type="hidden" name="owner_id" :value="form.owner_id">
    <div x-show="form.context" class="rounded-2xl bg-brand-50 text-brand-700 text-xs px-4 py-3"><b>Konteks induk:</b> <span x-text="form.context"></span></div>
    <div class="grid sm:grid-cols-[1fr_140px] gap-3">
        <div><label class="lbl">Nama Indikator *</label><input name="name" x-model="form.name" class="inp" required data-testid="indicator-name-input"></div>
        <div><label class="lbl">Kode</label><input name="code" x-model="form.code" class="inp"></div>
    </div>
    <div class="grid sm:grid-cols-3 gap-3">
        <div><label class="lbl">Jenis *</label><select name="type" x-model="form.type" class="inp">@foreach (config('esakip.indicator_types') as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
        <div><label class="lbl">Satuan *</label><select name="satuan_id" x-model="form.satuan_id" class="inp" required data-testid="indicator-satuan-select"><option value="">Pilih</option>@foreach ($satuans as $id => $n)<option value="{{ $id }}">{{ $n }}</option>@endforeach</select></div>
        <div><label class="lbl">Baseline</label><input name="baseline" x-model="form.baseline" type="number" step="any" class="inp"></div>
    </div>
    <div><label class="lbl">Definisi Operasional * <span class="font-normal text-slate-400">(wajib sebelum dipakai untuk target/realisasi)</span></label><textarea name="definition" x-model="form.definition" class="inp" required data-testid="indicator-definition-input"></textarea></div>
    <div><label class="lbl">Formula Capaian *</label>
        <div class="grid gap-2">
            @foreach (config('esakip.formulas') as $k => $v)
                <label class="flex items-start gap-3 rounded-2xl border p-3 cursor-pointer transition-colors" :class="form.formula === '{{ $k }}' ? 'border-brand-400 bg-brand-50/60' : 'border-slate-200'">
                    <input type="radio" name="formula" value="{{ $k }}" x-model="form.formula" class="mt-0.5 text-brand-600 focus:ring-brand-200"><span class="text-xs text-slate-600">{{ $v }}</span>
                </label>
            @endforeach
        </div>
    </div>
    <div><label class="lbl">Sumber Data</label><input name="data_source" x-model="form.data_source" class="inp" placeholder="mis. BPS, SIPD, Laporan Bidang"></div>
    <div>
        <label class="lbl">Target per Tahun</label>
        <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
            @foreach ($years as $y)
                <div><span class="text-[11px] font-semibold text-slate-500">{{ $y }}</span><input name="targets[{{ $y }}]" x-model="form.targets[{{ $y }}]" type="number" step="any" class="inp !h-10 mt-1" data-testid="indicator-target-{{ $y }}"></div>
            @endforeach
        </div>
    </div>
</x-drawer>

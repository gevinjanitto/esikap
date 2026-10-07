@php $assigned = $assigned ?? collect(); @endphp
<x-drawer name="tree" title="Tambah Data" sub="Struktur Kinerja" :action="''" :defaults="['parent_id' => '', 'type' => '', 'code' => '', 'name' => '', 'sasaran_daerah_id' => '', 'label' => '']">
    <input type="hidden" name="parent_id" :value="form.parent_id">
    <div x-show="form.context" class="rounded-2xl bg-brand-50 text-brand-700 text-xs px-4 py-3"><b>Induk:</b> <span x-text="form.context"></span></div>
    <div><label class="lbl">Kode *</label><input name="code" x-model="form.code" class="inp" required data-testid="tree-code-input"></div>
    <div><label class="lbl">Uraian / Nomenklatur *</label><textarea name="name" x-model="form.name" class="inp" required data-testid="tree-name-input"></textarea></div>
    <template x-if="form.type === 'tujuan_opd'">
        <div>
            <label class="lbl">Mendukung Sasaran Daerah (RPJMD) <span class="font-normal text-slate-400">— hanya sasaran yang ditugaskan ke OPD</span></label>
            <select name="sasaran_daerah_id" x-model="form.sasaran_daerah_id" class="inp" data-testid="tree-sasaran-daerah-select">
                <option value="">— Pilih sasaran daerah —</option>
                @foreach ($assigned as $s)<option value="{{ $s->id }}">{{ $s->code }} · {{ \Illuminate\Support\Str::limit($s->name, 70) }} ({{ $s->pivot->peran }})</option>@endforeach
            </select>
        </div>
    </template>
</x-drawer>

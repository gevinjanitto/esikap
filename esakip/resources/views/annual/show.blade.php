@extends('layouts.app')
@section('title', $cfg['label'].' '.$doc->opd->name)
@section('subtitle')
    <span class="inline-flex items-center gap-1.5 text-sm">{{ $cfg['label'] }} <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i> {{ $doc->opd->name }} <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i> {{ $doc->year }}</span>
@endsection
@section('heading', $cfg['label'].' '.$doc->year)

@section('actions')
    <a href="{{ route('annual.index', [$kind, 'tahun' => $doc->year]) }}" class="btn-ghost"><i data-lucide="arrow-left" class="w-4 h-4"></i>Kembali</a>
    <a href="{{ route('annual.print', [$kind, $doc->id]) }}" target="_blank" class="btn-ghost" data-no-transition data-testid="annual-print-button"><i data-lucide="printer" class="w-4 h-4"></i>Cetak / PDF</a>
    <x-workflow :model="$doc" :type="$kind" :actions="$actions" />
@endsection

@section('content')
<div class="grid xl:grid-cols-[1fr_340px] gap-5">
    <div class="card reveal" style="--i:1">
        <div class="flex flex-wrap items-center gap-3 mb-5">
            <h3 class="card-title">Indikator & Target</h3>
            <x-badge :s="$doc->status" tid="annual-status-badge" />
            @if ($editable)
                <button class="btn-primary btn-sm ml-auto" @click="$dispatch('open-drawer', { name: 'item' })" data-testid="annual-add-item-button"><i data-lucide="plus" class="w-4 h-4"></i>Tambah Indikator</button>
            @endif
        </div>
        <div class="overflow-x-auto thin-scroll">
            <table class="tbl" data-testid="annual-items-table">
                <thead><tr><th>#</th><th>Indikator</th><th>Level</th><th class="text-right">Target</th><th>Satuan</th><th class="text-right">Pagu (Rp)</th><th>Keterangan</th><th></th></tr></thead>
                <tbody>
                @forelse ($doc->items as $i => $it)
                    <tr data-testid="annual-item-{{ $it->id }}">
                        <td class="text-slate-400">{{ $i + 1 }}</td>
                        <td class="font-medium max-w-[340px]">{{ $it->indicator->name }}</td>
                        <td><span class="chip">{{ $it->indicator->level_label }}</span></td>
                        <td class="text-right num font-semibold">{{ \App\Support\Esakip::num($it->target) }}</td>
                        <td class="text-slate-500">{{ $it->indicator->satuan?->name }}</td>
                        <td class="text-right num">{{ \App\Support\Esakip::num($it->pagu, 0) }}</td>
                        <td class="text-xs text-slate-500">{{ $it->keterangan }}</td>
                        <td>@if ($editable)<form method="POST" action="{{ route('annual.item.destroy', [$kind, $doc->id, $it->id]) }}" data-confirm="Hapus item ini?">@csrf @method('DELETE')<button class="icon-btn-sm"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button></form>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="8"><x-empty icon="target" title="Belum ada indikator" text="Tambahkan indikator dari Renstra OPD beserta target tahun ini." /></td></tr>
                @endforelse
                </tbody>
                @if ($doc->items->count())
                    <tfoot><tr><td colspan="5" class="text-right font-semibold">Total Pagu</td><td class="text-right num font-bold">{{ \App\Support\Esakip::num($doc->items->sum('pagu'), 0) }}</td><td colspan="2"></td></tr></tfoot>
                @endif
            </table>
        </div>
    </div>
    <div class="space-y-5">
        <div class="card reveal" style="--i:2">
            <h3 class="card-title mb-3">Informasi Dokumen</h3>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between gap-3"><dt class="text-slate-500">OPD</dt><dd class="font-medium text-right">{{ $doc->opd->name }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Tahun</dt><dd class="font-medium">{{ $doc->year }}</dd></div>
                @if ($kind === 'pk')
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Pihak Pertama</dt><dd class="font-medium text-right">{{ $doc->pihak_pertama }}<br><span class="text-xs text-slate-400">{{ $doc->jabatan_pertama }}</span></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Pihak Kedua</dt><dd class="font-medium text-right">{{ $doc->pihak_kedua }}<br><span class="text-xs text-slate-400">{{ $doc->jabatan_kedua }}</span></dd></div>
                @endif
                <div class="flex justify-between gap-3"><dt class="text-slate-500">Disusun</dt><dd class="font-medium text-right">{{ $doc->creator?->name }}</dd></div>
            </dl>
            @if ($doc->catatan)<p class="text-xs text-slate-600 bg-slate-50 rounded-2xl p-3 mt-3">{{ $doc->catatan }}</p>@endif
        </div>
        <div class="card reveal" style="--i:3"><h3 class="card-title mb-4">Riwayat Persetujuan</h3><x-timeline :logs="$doc->approvalLogs" /></div>
    </div>
</div>

@if ($editable)
<x-drawer name="item" title="Tambah Indikator" :sub="$cfg['label']" :action="route('annual.item.store', [$kind, $doc->id])" :defaults="['indicator_id' => '', 'target' => '', 'pagu' => '', 'keterangan' => '']">
    <div x-data="{ opts: @js($indicators) }">
        <label class="lbl">Indikator OPD *</label>
        <select name="indicator_id" x-model="form.indicator_id" @change="const o = opts.find(x => x.id == form.indicator_id); if (o && o.target !== null) form.target = o.target" class="inp" required data-testid="annual-item-indicator-select">
            <option value="">Pilih indikator</option>
            <template x-for="o in opts" :key="o.id"><option :value="o.id" x-text="o.label"></option></template>
        </select>
        <p class="text-[11px] text-slate-400 mt-1">Target otomatis diambil dari target Renstra tahun {{ $doc->year }}.</p>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div><label class="lbl">Target *</label><input name="target" x-model="form.target" type="number" step="any" class="inp" required data-testid="annual-item-target-input"></div>
        <div><label class="lbl">Pagu (Rp)</label><input name="pagu" x-model="form.pagu" type="number" step="any" class="inp"></div>
    </div>
    <div><label class="lbl">Keterangan / Rencana Pelaksanaan</label><textarea name="keterangan" x-model="form.keterangan" class="inp"></textarea></div>
</x-drawer>
@endif
@endsection

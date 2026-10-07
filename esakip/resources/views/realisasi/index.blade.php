@extends('layouts.app')
@section('title', 'Monitoring & Realisasi')
@section('subtitle', 'Input realisasi periodik, capaian otomatis berdasarkan formula indikator')
@section('heading', 'Monitoring & Realisasi')
@php use App\Support\Esakip; @endphp

@section('tabs')
    <div class="pill-tabs">
        <a href="{{ route('realisasi.index', ['tahun' => $year]) }}" class="{{ ! request('periode') ? 'pill-tab-active' : 'pill-tab' }}">Semua</a>
        @foreach (config('esakip.periode') as $k => $l)
            <a href="{{ route('realisasi.index', ['tahun' => $year, 'periode' => $k]) }}" class="{{ request('periode') === $k ? 'pill-tab-active' : 'pill-tab' }}" data-testid="realisasi-tab-{{ strtolower($k) }}">{{ $l }}</a>
        @endforeach
    </div>
@endsection

@section('actions')
    @if ($canInput)
        <button class="btn-primary" @click="$dispatch('open-drawer', { name: 'realisasi', title: 'Input Realisasi' })" data-testid="realisasi-create-button"><i data-lucide="plus" class="w-4 h-4"></i>Input Realisasi</button>
    @endif
@endsection

@section('content')
<div class="card reveal" style="--i:1">
    <form class="flex flex-wrap gap-2 mb-4">
        <input type="hidden" name="periode" value="{{ request('periode') }}">
        <select name="tahun" class="inp !w-32 !h-10" data-testid="realisasi-year-select">@foreach ($years as $y)<option @selected($y == $year)>{{ $y }}</option>@endforeach</select>
        <select name="status_capaian" class="inp !w-44 !h-10"><option value="">Semua capaian</option>@foreach (['tercapai', 'perlu_perhatian', 'tidak_tercapai'] as $s)<option value="{{ $s }}" @selected(request('status_capaian') === $s)>{{ Esakip::label($s) }}</option>@endforeach</select>
        <select name="status" class="inp !w-44 !h-10"><option value="">Semua status</option>@foreach (['draft', 'diajukan', 'revisi', 'disetujui', 'ditetapkan', 'ditolak'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ Esakip::label($s) }}</option>@endforeach</select>
        @unless (auth()->user()->isOpdScoped())<select name="opd" class="inp !w-56 !h-10"><option value="">Semua OPD</option>@foreach ($opds as $id => $n)<option value="{{ $id }}" @selected(request('opd') == $id)>{{ $n }}</option>@endforeach</select>@endunless
        <button class="btn-dark btn-sm !h-10" data-testid="realisasi-filter-button"><i data-lucide="filter" class="w-4 h-4"></i>Filter</button>
    </form>
    <div class="overflow-x-auto thin-scroll">
        <table class="tbl" data-testid="realisasi-table">
            <thead><tr><th>Indikator</th><th>Periode</th><th class="text-right">Target</th><th class="text-right">Realisasi</th><th>Capaian</th><th class="text-right">Deviasi</th><th>Status</th><th>Bukti Dukung</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse ($rows as $r)
                @php $can = $canInput && Esakip::canEdit('opd', $r->opd_id) && $r->isEditable(); @endphp
                <tr data-testid="realisasi-row-{{ $r->id }}">
                    <td class="max-w-[300px]">
                        <p class="font-medium leading-snug">{{ $r->indicator->name }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $r->opd->singkatan ?? $r->opd->name }} · {{ $r->indicator->level_label }} · {{ $r->indicator->satuan?->name }}</p>
                        @if ($r->analisis)<p class="text-[11px] text-slate-500 mt-1 line-clamp-2" title="{{ $r->analisis }}">{{ $r->analisis }}</p>@endif
                    </td>
                    <td><span class="chip">{{ $r->periode }} {{ $r->year }}</span></td>
                    <td class="text-right num">{{ Esakip::num($r->target) }}</td>
                    <td class="text-right num font-semibold">{{ Esakip::num($r->realisasi) }}</td>
                    <td class="min-w-[170px]"><x-capbar :v="$r->capaian" sm /><x-badge :s="$r->status_capaian" /></td>
                    <td class="text-right num text-xs {{ $r->deviasi < 0 && $r->indicator->formula === 'positif' ? 'text-brand-600' : 'text-slate-500' }}">{{ $r->deviasi > 0 ? '+' : '' }}{{ Esakip::num($r->deviasi) }}</td>
                    <td><x-badge :s="$r->status" tid="realisasi-status-{{ $r->id }}" /></td>
                    <td class="min-w-[170px]"><x-evidence :items="$r->evidences" type="realisasi" :id="$r->id" :can="$can" /></td>
                    <td class="min-w-[160px]">
                        <div class="flex flex-wrap gap-1.5">
                            @if ($can)
                                <button class="icon-btn-sm" @click="$dispatch('open-drawer', { name: 'realisasi', data: @js(['indicator_id' => (string) $r->indicator_id, 'year' => (string) $r->year, 'periode' => $r->periode, 'target' => (float) $r->target, 'realisasi' => (float) $r->realisasi, 'analisis' => $r->analisis, 'lock' => true]), action: @js(route('realisasi.update', $r)), method: 'PUT', title: 'Ubah Realisasi' })" data-testid="realisasi-edit-{{ $r->id }}"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></button>
                            @endif
                            <x-workflow :model="$r" type="realisasi" :actions="$r->wfActions" />
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9"><x-empty icon="activity" title="Belum ada realisasi" text="Input realisasi per periode beserta bukti dukung." /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $rows->links('partials.pagination') }}
</div>

<x-drawer name="realisasi" title="Input Realisasi" sub="Monitoring & Realisasi" :action="route('realisasi.store')" :defaults="['indicator_id' => '', 'year' => (string) $year, 'periode' => request('periode', 'TW1'), 'target' => '', 'realisasi' => '', 'analisis' => '', 'lock' => false]" files wide>
    <div x-data="{ opts: @js($indicators), get sel() { return this.opts.find(o => o.id == form.indicator_id) }, fill() { if (this.sel && this.sel.targets[form.year] !== undefined) form.target = parseFloat(this.sel.targets[form.year]) }, get cap() { const t = parseFloat(form.target), r = parseFloat(form.realisasi); if (!t || isNaN(r)) return null; const c = this.sel && this.sel.formula === 'negatif' ? (2 * t - r) / t * 100 : r / t * 100; return Math.max(c, 0) } }" class="space-y-4">
        <div>
            <label class="lbl">Indikator *</label>
            <select name="indicator_id" x-model="form.indicator_id" @change="fill()" :disabled="form.lock" class="inp" required data-testid="realisasi-indicator-select">
                <option value="">Pilih indikator</option>
                <template x-for="o in opts" :key="o.id"><option :value="o.id" x-text="(o.opd ? '[' + o.opd + '] ' : '') + o.label + ' (' + o.level + ')'" :selected="o.id == form.indicator_id"></option></template>
            </select>
            <template x-if="form.lock"><input type="hidden" name="indicator_id" :value="form.indicator_id"></template>
        </div>
        <template x-if="sel">
            <div class="rounded-2xl bg-slate-50 p-4 text-xs space-y-1">
                <p><b>Definisi operasional:</b> <span x-text="sel.definition"></span></p>
                <p><b>Formula:</b> <span x-text="sel.formula === 'negatif' ? 'Negatif — (2 × Target − Realisasi) / Target × 100%' : 'Positif — Realisasi / Target × 100%'"></span> · <b>Satuan:</b> <span x-text="sel.satuan"></span></p>
            </div>
        </template>
        <div class="grid grid-cols-2 gap-3">
            <div><label class="lbl">Tahun *</label><select name="year" x-model="form.year" @change="fill()" class="inp" :disabled="form.lock">@foreach ($years as $y)<option value="{{ $y }}">{{ $y }}</option>@endforeach</select><template x-if="form.lock"><input type="hidden" name="year" :value="form.year"></template></div>
            <div><label class="lbl">Periode *</label><select name="periode" x-model="form.periode" class="inp" :disabled="form.lock" data-testid="realisasi-periode-select">@foreach (config('esakip.periode') as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select><template x-if="form.lock"><input type="hidden" name="periode" :value="form.periode"></template></div>
            <div><label class="lbl">Target Periode *</label><input name="target" x-model="form.target" type="number" step="any" class="inp" required data-testid="realisasi-target-input"></div>
            <div><label class="lbl">Realisasi *</label><input name="realisasi" x-model="form.realisasi" type="number" step="any" class="inp" required data-testid="realisasi-value-input"></div>
        </div>
        <div x-show="cap !== null" class="rounded-2xl border border-slate-200 p-4" data-testid="realisasi-capaian-preview">
            <div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold text-slate-500">Capaian otomatis</span><span class="font-display text-xl font-bold" :class="cap >= 90 ? 'text-emerald-600' : (cap >= 75 ? 'text-amber-500' : 'text-brand-600')" x-text="cap !== null ? cap.toFixed(2) + '%' : ''"></span></div>
            <div class="bar sm"><div class="h-full rounded-full transition-all duration-700" :class="cap >= 90 ? 'bg-emerald-500' : (cap >= 75 ? 'bg-amber-400' : 'bg-brand-600')" :style="'width:' + Math.min(cap || 0, 100) + '%'"></div></div>
        </div>
        <div><label class="lbl">Analisis penyebab & kondisi</label><textarea name="analisis" x-model="form.analisis" class="inp" data-testid="realisasi-analisis-input"></textarea></div>
        <div class="grid sm:grid-cols-2 gap-3">
            <div><label class="lbl">Bukti dukung (file)</label><input type="file" name="files[]" multiple class="text-sm" data-testid="realisasi-files-input"></div>
            <div><label class="lbl">atau tautan data</label><input type="url" name="link" class="inp" placeholder="https://"></div>
        </div>
    </div>
</x-drawer>
@endsection

@extends('layouts.app')
@section('title', 'Evaluasi Kinerja')
@section('subtitle', 'Penilaian SAKIP, rekomendasi perbaikan & tindak lanjut')
@section('heading', 'Evaluasi Kinerja')

@section('actions')
    @if ($canEval)
        <button class="btn-primary" @click="$dispatch('open-drawer', { name: 'evaluasi' })" data-testid="evaluasi-create-button"><i data-lucide="plus" class="w-4 h-4"></i>Evaluasi Baru</button>
    @endif
@endsection

@section('content')
<div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
    @forelse ($rows as $k => $e)
        @php $done = $e->rekomendasis->where('status', 'terverifikasi')->count(); $tot = $e->rekomendasis->count(); @endphp
        <a href="{{ route('evaluasi.show', $e) }}" class="card lift group reveal block" style="--i:{{ $k + 1 }}" data-testid="evaluasi-card-{{ $e->id }}">
            <div class="flex items-start justify-between">
                <div><p class="eyebrow">Evaluasi {{ $e->year }}</p><p class="font-semibold text-[17px] mt-1 group-hover:text-brand-600 transition-colors">{{ $e->opd->name }}</p></div>
                <span class="w-16 h-16 rounded-3xl bg-ink text-white grid place-items-center font-display text-xl font-bold shrink-0" data-testid="evaluasi-predikat-{{ $e->id }}">{{ $e->predikat }}</span>
            </div>
            <div class="flex items-end gap-2 mt-4"><p class="font-display text-4xl font-bold num">{{ \App\Support\Esakip::num($e->nilai) }}</p><p class="text-xs text-slate-400 pb-1.5">/ 100</p><span class="ml-auto"><x-badge :s="$e->status" /></span></div>
            <div class="mt-4">
                <div class="flex justify-between text-xs mb-1.5"><span class="text-slate-500">Tindak lanjut terverifikasi</span><span class="font-semibold">{{ $done }}/{{ $tot }}</span></div>
                <div class="bar sm"><div class="bar-fill bg-brand-600" style="width: {{ $tot ? $done / $tot * 100 : 0 }}%"></div></div>
            </div>
            <p class="text-[11px] text-slate-400 mt-4">Evaluator: {{ $e->evaluator?->name ?? '-' }}</p>
        </a>
    @empty
        <div class="card sm:col-span-2 xl:col-span-3"><x-empty icon="clipboard-check" title="Belum ada evaluasi" /></div>
    @endforelse
</div>

@if ($canEval)
<x-drawer name="evaluasi" title="Evaluasi Baru" sub="Evaluasi SAKIP" :action="route('evaluasi.store')" :defaults="['opd_id' => '', 'year' => (string) end($years), 'nilai' => '', 'catatan' => '', 'status' => 'draft']">
    <div><label class="lbl">OPD *</label><select name="opd_id" x-model="form.opd_id" class="inp" required data-testid="evaluasi-opd-select"><option value="">Pilih OPD</option>@foreach ($opds as $id => $n)<option value="{{ $id }}">{{ $n }}</option>@endforeach</select></div>
    <div class="grid grid-cols-2 gap-3">
        <div><label class="lbl">Tahun dievaluasi *</label><select name="year" x-model="form.year" class="inp">@foreach ($years as $y)<option value="{{ $y }}">{{ $y }}</option>@endforeach</select></div>
        <div><label class="lbl">Nilai (0-100) *</label><input name="nilai" x-model="form.nilai" type="number" step="0.01" min="0" max="100" class="inp" required data-testid="evaluasi-nilai-input"></div>
    </div>
    <div><label class="lbl">Catatan Evaluator</label><textarea name="catatan" x-model="form.catatan" class="inp"></textarea></div>
    <div><label class="lbl">Status</label><select name="status" x-model="form.status" class="inp"><option value="draft">Draft</option><option value="final">Final</option></select></div>
    <p class="text-[11px] text-slate-400">Predikat dihitung otomatis: AA (&gt;90), A (&gt;80), BB (&gt;70), B (&gt;60), CC (&gt;50), C (&gt;30), D.</p>
</x-drawer>
@endif
@endsection

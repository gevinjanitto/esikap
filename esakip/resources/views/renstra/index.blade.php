@extends('layouts.app')
@section('title', 'Renstra OPD')
@section('subtitle', 'Rencana Strategis Organisasi Perangkat Daerah · '.($period?->name ?? ''))
@section('heading', 'Renstra OPD')

@section('actions')
    <form class="pill-tabs">
        @foreach (['' => 'Semua', 'draft' => 'Draft', 'diajukan' => 'Diajukan', 'disetujui' => 'Disetujui', 'ditetapkan' => 'Ditetapkan'] as $k => $l)
            <a href="{{ route('renstra.index', array_filter(['status' => $k])) }}" class="{{ request('status', '') === $k ? 'pill-tab-active' : 'pill-tab' }}" data-testid="renstra-filter-{{ $k ?: 'all' }}">{{ $l }}</a>
        @endforeach
    </form>
    @if (\App\Support\Esakip::canEdit('opd'))
        <button class="btn-primary" @click="$dispatch('open-drawer', { name: 'renstra' })" data-testid="renstra-create-button"><i data-lucide="plus" class="w-4 h-4"></i>Renstra Baru</button>
    @endif
@endsection

@section('content')
<div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
    @forelse ($rows as $k => $r)
        @php
            $sas = $r->tujuans->flatMap->sasarans; $prog = $sas->flatMap->programs;
        @endphp
        <a href="{{ route('renstra.show', $r) }}" class="card lift group reveal block" style="--i:{{ $k + 1 }}" data-testid="renstra-card-{{ $r->id }}">
            <div class="flex items-start justify-between mb-5">
                <span class="w-12 h-12 rounded-2xl bg-ink text-white grid place-items-center font-display text-[11px] font-bold">{{ \Illuminate\Support\Str::limit($r->opd->singkatan ?? 'OPD', 6, '') }}</span>
                <span class="icon-btn arrow-btn"><i data-lucide="arrow-up-right" class="w-4 h-4"></i></span>
            </div>
            <p class="font-semibold text-[17px] leading-snug group-hover:text-brand-600 transition-colors">{{ $r->opd->name }}</p>
            <p class="text-xs text-slate-500 mt-1">{{ $r->period->name }} · v{{ $r->version }}</p>
            <div class="grid grid-cols-4 gap-2 mt-5 text-center">
                @foreach ([['Tujuan', $r->tujuans->count()], ['Sasaran', $sas->count()], ['Program', $prog->count()], ['Kegiatan', $prog->flatMap->kegiatans->count()]] as [$l, $v])
                    <div class="rounded-xl bg-slate-50 py-2"><p class="font-display font-bold">{{ $v }}</p><p class="text-[10px] text-slate-500">{{ $l }}</p></div>
                @endforeach
            </div>
            <div class="flex items-center justify-between mt-5"><x-badge :s="$r->status" /><span class="text-[11px] text-slate-400">Diperbarui {{ $r->updated_at->diffForHumans() }}</span></div>
        </a>
    @empty
        <div class="card sm:col-span-2 xl:col-span-3"><x-empty icon="compass" title="Belum ada Renstra" text="Buat Renstra OPD untuk periode aktif dan hubungkan dengan sasaran RPJMD yang ditugaskan." /></div>
    @endforelse
</div>

<x-drawer name="renstra" title="Renstra OPD Baru" sub="Renstra" :action="route('renstra.store')" :defaults="['opd_id' => (string) (auth()->user()->opd_id ?? ''), 'nomor_dokumen' => '']">
    <div><label class="lbl">OPD *</label><select name="opd_id" x-model="form.opd_id" class="inp" required data-testid="renstra-opd-select"><option value="">Pilih OPD</option>@foreach ($opds as $id => $n)<option value="{{ $id }}">{{ $n }}</option>@endforeach</select></div>
    <div><label class="lbl">Nomor Dokumen</label><input name="nomor_dokumen" x-model="form.nomor_dokumen" class="inp"></div>
    <p class="text-xs text-slate-500 rounded-2xl bg-slate-50 p-3">Renstra dibuat untuk periode aktif <b>{{ $period?->name }}</b>. Setelah dibuat, susun tujuan, sasaran, indikator, program dan kegiatan.</p>
</x-drawer>
@endsection

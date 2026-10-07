@extends('layouts.app')
@section('title', 'Renstra '.$renstra->opd->name)
@section('subtitle')
    <span class="inline-flex items-center gap-1.5 text-sm" data-testid="breadcrumb">{{ $renstra->period->name }} <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i> {{ $renstra->opd->name }} <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i> Renstra</span>
@endsection
@section('heading', 'Renstra '.($renstra->opd->singkatan ?? 'OPD'))

@section('actions')
    <a href="{{ route('renstra.index') }}" class="btn-ghost"><i data-lucide="arrow-left" class="w-4 h-4"></i>Kembali</a>
    <x-workflow :model="$renstra" type="renstra" :actions="$actions" />
@endsection

@section('content')
<div class="grid xl:grid-cols-[1fr_340px] gap-5">
    <div class="space-y-5">
        <div class="card reveal flex flex-wrap items-center gap-3" style="--i:1">
            <x-badge :s="$renstra->status" tid="renstra-status-badge" />
            <span class="chip">v{{ $renstra->version }}</span>
            <span class="chip">{{ $renstra->nomor_dokumen ?: 'Tanpa nomor' }}</span>
            <span class="text-xs text-slate-500">Disusun oleh {{ $renstra->creator?->name ?? '-' }}</span>
            @if ($editable)
                <button class="btn-primary btn-sm ml-auto" @click="$dispatch('open-drawer', { name: 'tree', data: { parent_id: {{ $renstra->id }}, type: 'tujuan_opd' }, action: @js(route('tree.store', 'tujuan_opd')), title: 'Tambah Tujuan OPD' })" data-testid="add-tujuan-opd-button"><i data-lucide="plus" class="w-4 h-4"></i>Tujuan OPD</button>
            @else
                <span class="ml-auto text-xs text-slate-500 inline-flex items-center gap-1.5"><i data-lucide="lock" class="w-3.5 h-3.5 text-brand-600"></i>Read-only pada status ini</span>
            @endif
        </div>

        @forelse ($renstra->tujuans as $ti => $t)
            <div class="card reveal" style="--i:{{ $ti + 2 }}" data-testid="tujuan-opd-{{ $t->id }}">
                @if ($t->sasaranDaerah)
                    <div class="rounded-2xl bg-ink text-white px-4 py-3 mb-4 flex items-start gap-3">
                        <i data-lucide="landmark" class="w-4 h-4 text-brand-400 mt-0.5 shrink-0"></i>
                        <p class="text-xs"><span class="text-white/60">Mendukung sasaran RPJMD</span> <b>{{ $t->sasaranDaerah->code }}</b> — {{ $t->sasaranDaerah->name }}</p>
                    </div>
                @endif
                <div class="flex items-start gap-3">
                    <span class="w-11 h-11 rounded-2xl bg-brand-600 text-white grid place-items-center font-display text-[11px] font-bold shrink-0">{{ $t->code }}</span>
                    <div class="flex-1 min-w-0"><p class="eyebrow mb-0.5">Tujuan OPD</p><p class="font-semibold">{{ $t->name }}</p></div>
                    <x-node-actions type="tujuan_opd" :item="$t" :parent-id="$renstra->id" child-type="sasaran_opd" child-label="Sasaran" :editable="$editable" />
                </div>

                <div class="mt-4 ml-5 pl-6 border-l-2 border-dashed border-brand-100 space-y-5">
                    @foreach ($t->sasarans as $s)
                        <div data-testid="sasaran-opd-{{ $s->id }}">
                            <div class="flex items-start gap-3 mb-2">
                                <span class="node-dot mt-1.5 -ml-[31px]"></span>
                                <div class="flex-1"><p class="text-[11px] font-semibold text-slate-400">SASARAN {{ $s->code }}</p><p class="text-sm font-semibold">{{ $s->name }}</p></div>
                                <x-node-actions type="sasaran_opd" :item="$s" :parent-id="$t->id" child-type="program" child-label="Program" indicator-owner="sasaran_opd" :editable="$editable" />
                            </div>
                            <div class="space-y-2">@foreach ($s->indicators as $ind) @include('partials.indicator', ['ind' => $ind, 'editable' => $editable]) @endforeach</div>

                            @foreach ($s->programs as $p)
                                <div class="mt-3 rounded-[22px] border border-slate-200/80 bg-white p-4" data-testid="program-{{ $p->id }}" x-data="{ o: true }">
                                    <div class="flex items-start gap-3">
                                        <button @click="o = !o" class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 grid place-items-center shrink-0"><i data-lucide="folder-kanban" class="w-4 h-4"></i></button>
                                        <div class="flex-1 min-w-0"><p class="text-[11px] font-semibold text-sky-600">PROGRAM {{ $p->code }}</p><p class="text-sm font-semibold">{{ $p->name }}</p></div>
                                        <x-node-actions type="program" :item="$p" :parent-id="$s->id" child-type="kegiatan" child-label="Kegiatan" indicator-owner="program" :editable="$editable" />
                                    </div>
                                    <div x-show="o" x-transition class="mt-3 space-y-2">
                                        @foreach ($p->indicators as $ind) @include('partials.indicator', ['ind' => $ind, 'editable' => $editable]) @endforeach
                                        @foreach ($p->kegiatans as $k)
                                            <div class="ml-4 rounded-2xl border border-dashed border-slate-200 p-3" data-testid="kegiatan-{{ $k->id }}">
                                                <div class="flex items-start gap-3 mb-2">
                                                    <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 grid place-items-center shrink-0"><i data-lucide="square-check" class="w-3.5 h-3.5"></i></span>
                                                    <div class="flex-1 min-w-0"><p class="text-[11px] font-semibold text-emerald-600">KEGIATAN {{ $k->code }}</p><p class="text-sm font-medium">{{ $k->name }}</p></div>
                                                    <x-node-actions type="kegiatan" :item="$k" :parent-id="$p->id" indicator-owner="kegiatan" :editable="$editable" />
                                                </div>
                                                <div class="space-y-2">@foreach ($k->indicators as $ind) @include('partials.indicator', ['ind' => $ind, 'editable' => $editable]) @endforeach</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="card"><x-empty icon="compass" title="Renstra masih kosong" text="Mulai dengan menambahkan Tujuan OPD yang terhubung ke sasaran RPJMD yang ditugaskan." /></div>
        @endforelse
    </div>

    <div class="space-y-5">
        <div class="card reveal" style="--i:2" data-testid="assigned-sasaran-card">
            <h3 class="card-title mb-1">Sasaran RPJMD Ditugaskan</h3>
            <p class="text-xs text-slate-400 mb-4">Konteks induk yang menjadi acuan Renstra OPD ini</p>
            <div class="space-y-2">
                @forelse ($assigned as $s)
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <div class="flex items-center gap-2 mb-1"><span class="text-[11px] font-bold text-brand-600">{{ $s->code }}</span><span class="badge {{ $s->pivot->peran === 'utama' ? 'bg-ink text-white ring-ink' : 'bg-white text-slate-600 ring-slate-200' }}">{{ ucfirst($s->pivot->peran) }}</span></div>
                        <p class="text-xs font-medium">{{ $s->name }}</p>
                        <p class="text-[10px] text-slate-400 mt-1">Misi {{ $s->tujuan->misi->code }} · Tujuan {{ $s->tujuan->code }}</p>
                    </div>
                @empty
                    <p class="text-xs text-slate-400">Belum ada sasaran RPJMD yang ditugaskan ke OPD ini.</p>
                @endforelse
            </div>
        </div>
        <div class="card reveal" style="--i:3"><h3 class="card-title mb-4">Riwayat Persetujuan</h3><x-timeline :logs="$renstra->approvalLogs" /></div>
    </div>
</div>
@include('partials.indicator-drawer')
@include('partials.tree-drawer', ['assigned' => $assigned])
@endsection

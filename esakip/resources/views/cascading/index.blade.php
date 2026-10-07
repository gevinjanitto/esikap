@extends('layouts.app')
@section('title', 'Cascading Kinerja')
@section('subtitle', 'Pohon kinerja: RPJMD → OPD → Program → Kegiatan')
@section('heading', 'Cascading Kinerja')
@php use App\Support\Esakip; @endphp

@section('actions')
    <form class="flex flex-wrap gap-2">
        <select name="tahun" onchange="this.form.submit()" class="inp !rounded-full !w-40">@foreach ($years as $y)<option value="{{ $y }}" @selected($y == $year)>Tahun {{ $y }}</option>@endforeach</select>
        @unless (auth()->user()->isOpdScoped())
            <select name="opd" onchange="this.form.submit()" class="inp !rounded-full !w-64" data-testid="cascading-opd-filter"><option value="">Seluruh OPD</option>@foreach ($opds as $id => $n)<option value="{{ $id }}" @selected($opdFilter == $id)>{{ $n }}</option>@endforeach</select>
        @endunless
    </form>
@endsection

@section('content')
@if (! $rpjmd)
    <div class="card"><x-empty icon="network" title="RPJMD belum tersedia" /></div>
@else
<div class="card !p-6 mb-5 reveal" style="--i:1">
    <div class="flex flex-wrap items-center gap-3 text-xs">
        @foreach ([['bg-ink', 'Misi / Tujuan'], ['bg-brand-600', 'Sasaran Daerah'], ['bg-violet-500', 'OPD'], ['bg-amber-400', 'Sasaran OPD'], ['bg-sky-500', 'Program'], ['bg-emerald-500', 'Kegiatan']] as [$c, $l])
            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full {{ $c }}"></span>{{ $l }}</span>
        @endforeach
        <span class="ml-auto text-slate-400">Capaian = realisasi periode terakhir tahun {{ $year }}</span>
    </div>
</div>

<div class="space-y-5" data-testid="cascading-tree">
    @foreach ($rpjmd->misis as $mi => $misi)
        @foreach ($misi->tujuans as $tujuan)
            @foreach ($tujuan->sasarans as $s)
                @php
                    $links = $s->tujuanOpds->filter(fn ($t) => ! $opdFilter || $t->renstra->opd_id == $opdFilter);
                    if ($opdFilter && $links->isEmpty() && ! $s->opds->contains('id', $opdFilter)) continue;
                @endphp
                <div class="card reveal" style="--i:{{ $mi + 2 }}" x-data="{ o: true }" data-testid="cascade-sasaran-{{ $s->id }}">
                    <div class="flex flex-wrap items-start gap-3">
                        <span class="badge bg-ink text-white ring-ink">Misi {{ $misi->code }}</span>
                        <span class="badge bg-slate-100 text-slate-600 ring-slate-200">Tujuan {{ $tujuan->code }}</span>
                        <button class="ml-auto icon-btn-sm" @click="o = !o"><i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="o || 'rotate-180'"></i></button>
                    </div>
                    <div class="flex items-start gap-3 mt-3">
                        <span class="w-10 h-10 rounded-2xl bg-brand-600 text-white grid place-items-center shrink-0"><i data-lucide="flag" class="w-4 h-4"></i></span>
                        <div class="flex-1">
                            <p class="text-[11px] font-semibold text-brand-600">SASARAN DAERAH {{ $s->code }}</p>
                            <p class="font-semibold">{{ $s->name }}</p>
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach ($s->indicators as $i)
                                    <span class="chip !bg-brand-50 !text-brand-700"><i data-lucide="target" class="w-3 h-3"></i>{{ $i->name }} · Target {{ $year }}: <b>{{ Esakip::num($i->targetFor($year)) }}</b> {{ $i->satuan?->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div x-show="o" x-transition class="mt-5 ml-5 pl-6 border-l-2 border-dashed border-brand-100 space-y-4">
                        @forelse ($links as $t)
                            <div>
                                <div class="flex items-center gap-2 -ml-[31px] mb-2">
                                    <span class="node-dot"></span>
                                    <span class="badge bg-violet-50 text-violet-700 ring-violet-200">{{ $t->renstra->opd->name }}</span>
                                    <span class="badge {{ $s->opds->firstWhere('id', $t->renstra->opd_id)?->pivot->peran === 'utama' ? 'bg-ink text-white ring-ink' : 'bg-white text-slate-500 ring-slate-200' }}">{{ ucfirst($s->opds->firstWhere('id', $t->renstra->opd_id)?->pivot->peran ?? '-') }}</span>
                                    <x-badge :s="$t->renstra->status" />
                                    <a href="{{ route('renstra.show', $t->renstra) }}" class="text-[11px] font-semibold hover:text-brand-600 inline-flex items-center gap-0.5">Renstra <i data-lucide="arrow-up-right" class="w-3 h-3"></i></a>
                                </div>
                                <p class="text-sm"><span class="text-slate-400 text-xs">Tujuan OPD {{ $t->code }}:</span> {{ $t->name }}</p>
                                @foreach ($t->sasarans as $so)
                                    <div class="mt-3 rounded-[22px] bg-amber-50/50 border border-amber-100 p-4">
                                        <p class="text-[11px] font-semibold text-amber-700">SASARAN OPD {{ $so->code }}</p>
                                        <p class="text-sm font-semibold mb-2">{{ $so->name }}</p>
                                        <div class="grid md:grid-cols-2 gap-2">@foreach ($so->indicators as $ind) @include('partials.indicator', ['ind' => $ind, 'latest' => $latest]) @endforeach</div>
                                        @foreach ($so->programs as $p)
                                            <div class="mt-3 ml-3 pl-4 border-l-2 border-sky-200">
                                                <p class="text-[11px] font-semibold text-sky-600">PROGRAM {{ $p->code }}</p>
                                                <p class="text-sm font-medium mb-2">{{ $p->name }}</p>
                                                <div class="grid md:grid-cols-2 gap-2">@foreach ($p->indicators as $ind) @include('partials.indicator', ['ind' => $ind, 'latest' => $latest]) @endforeach</div>
                                                @foreach ($p->kegiatans as $k)
                                                    <div class="mt-2 ml-3 pl-4 border-l-2 border-emerald-200">
                                                        <p class="text-[11px] font-semibold text-emerald-600">KEGIATAN {{ $k->code }}</p>
                                                        <p class="text-sm mb-2">{{ $k->name }}</p>
                                                        <div class="grid md:grid-cols-2 gap-2">@foreach ($k->indicators as $ind) @include('partials.indicator', ['ind' => $ind, 'latest' => $latest]) @endforeach</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @empty
                            <p class="text-xs text-slate-400">Belum ada Renstra OPD yang terhubung ke sasaran ini. OPD ditugaskan: {{ $s->opds->pluck('name')->implode(', ') ?: '-' }}</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        @endforeach
    @endforeach
</div>
@endif
@endsection

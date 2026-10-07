@extends('layouts.app')
@section('title', 'Pustaka Indikator')
@section('subtitle', 'Seluruh indikator kinerja beserta definisi operasional & target')
@section('heading', 'Pustaka Indikator')

@section('actions')
    <form class="flex flex-wrap gap-2">
        <select name="level" class="inp !rounded-full !w-44 !h-12"><option value="">Semua level</option>@foreach (\App\Models\Indicator::LEVELS as $k => $l)<option value="{{ $k }}" @selected(request('level') === $k)>{{ $l }}</option>@endforeach</select>
        @unless (auth()->user()->isOpdScoped())<select name="opd" class="inp !rounded-full !w-56 !h-12"><option value="">Semua OPD</option>@foreach ($opds as $id => $n)<option value="{{ $id }}" @selected(request('opd') == $id)>{{ $n }}</option>@endforeach</select>@endunless
        <div class="relative"><i data-lucide="search" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i><input name="q" value="{{ request('q') }}" class="inp !rounded-full !h-12 pl-11 !w-64" placeholder="Cari indikator..." data-testid="indikator-search-input"></div>
        <button class="btn-dark !h-12">Cari</button>
    </form>
@endsection

@section('content')
<div class="grid md:grid-cols-2 gap-4">
    @forelse ($rows as $k => $ind)
        <div class="reveal" style="--i:{{ $k }}">
            <p class="text-[11px] font-semibold text-slate-400 mb-1.5 ml-1">{{ $ind->level_label }} · {{ $ind->opd?->name ?? 'Pemerintah Daerah' }} · {{ \Illuminate\Support\Str::limit($ind->owner?->name, 60) }}</p>
            @include('partials.indicator', ['ind' => $ind])
        </div>
    @empty
        <div class="card md:col-span-2"><x-empty icon="search" title="Indikator tidak ditemukan" /></div>
    @endforelse
</div>
{{ $rows->links('partials.pagination') }}
@endsection

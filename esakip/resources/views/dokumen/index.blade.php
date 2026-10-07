@extends('layouts.app')
@section('title', 'Dokumen')
@section('subtitle', 'Repositori dokumen kinerja dengan versi & metadata')
@section('heading', 'Dokumen')

@section('actions')
    <form class="relative w-full sm:w-72"><i data-lucide="search" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i><input name="q" value="{{ request('q') }}" class="inp !rounded-full !h-12 pl-11" placeholder="Cari judul dokumen..." data-testid="dokumen-search-input"></form>
    @if ($canUpload)<button class="btn-primary" @click="$dispatch('open-drawer', { name: 'dokumen' })" data-testid="dokumen-upload-button"><i data-lucide="upload" class="w-4 h-4"></i>Unggah</button>@endif
@endsection

@section('content')
<div class="flex flex-wrap gap-2 mb-5 reveal" style="--i:1">
    <a href="{{ route('dokumen.index') }}" class="{{ ! request('jenis') && ! request('arsip') ? 'pill-tab-active' : 'pill-tab bg-white/70' }}">Semua</a>
    @foreach (config('esakip.doc_types') as $k => $l)<a href="{{ route('dokumen.index', ['jenis' => $k]) }}" class="{{ request('jenis') === $k ? 'pill-tab-active' : 'pill-tab bg-white/70' }}" data-testid="dokumen-filter-{{ strtolower($k) }}">{{ $l }}</a>@endforeach
    <a href="{{ route('dokumen.index', ['arsip' => 1]) }}" class="{{ request('arsip') ? 'pill-tab-active' : 'pill-tab bg-white/70' }}"><i data-lucide="archive" class="w-3.5 h-3.5 mr-1"></i>Arsip</a>
</div>
<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5">
    @forelse ($rows as $k => $d)
        <div class="card lift reveal flex flex-col" style="--i:{{ $k + 2 }}" data-testid="dokumen-card-{{ $d->id }}">
            <div class="flex items-start justify-between mb-4">
                <span class="w-12 h-14 rounded-xl bg-brand-50 text-brand-600 grid place-items-center relative"><i data-lucide="file-text" class="w-6 h-6"></i><span class="absolute -bottom-1.5 -right-1.5 text-[9px] font-bold bg-ink text-white rounded-md px-1">{{ strtoupper(pathinfo($d->original_name, PATHINFO_EXTENSION)) }}</span></span>
                <span class="chip">v{{ $d->version }}</span>
            </div>
            <p class="font-semibold leading-snug">{{ $d->title }}</p>
            <p class="text-xs text-slate-500 mt-1">{{ config('esakip.doc_types')[$d->jenis] ?? $d->jenis }} · {{ $d->nomor ?: 'Tanpa nomor' }}</p>
            <p class="text-[11px] text-slate-400 mt-1">{{ $d->opd?->name ?? 'Pemerintah Daerah' }} · {{ $d->tanggal?->format('d/m/Y') }} · {{ number_format($d->size / 1024, 0) }} KB</p>
            @if ($d->archived_at)<p class="text-[11px] text-brand-600 mt-2">Diarsipkan: {{ $d->archive_reason }}</p>@endif
            <div class="flex gap-2 mt-auto pt-4">
                <a href="{{ route('dokumen.download', $d) }}" data-no-transition class="btn-dark btn-sm flex-1" data-testid="dokumen-download-{{ $d->id }}"><i data-lucide="download" class="w-3.5 h-3.5"></i>Unduh</a>
                @if (! $d->archived_at && \App\Support\Esakip::canEdit('dokumen', $d->opd_id))
                    <form method="POST" action="{{ route('dokumen.archive', $d) }}" x-data="{ o: false }" class="relative">@csrf @method('PUT')
                        <button type="button" class="icon-btn !w-9 !h-9" @click="o = !o" title="Arsipkan" data-testid="dokumen-archive-toggle-{{ $d->id }}"><i data-lucide="archive" class="w-4 h-4"></i></button>
                        <div x-show="o" x-cloak x-transition class="absolute right-0 bottom-11 card !p-3 w-64 z-20"><input name="archive_reason" class="inp !h-9 !text-xs" placeholder="Alasan arsip *" required><button class="btn-primary btn-xs w-full mt-2">Arsipkan</button></div>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="card sm:col-span-2 xl:col-span-4"><x-empty icon="folder-open" title="Belum ada dokumen" /></div>
    @endforelse
</div>
{{ $rows->links('partials.pagination') }}

<x-drawer name="dokumen" title="Unggah Dokumen" sub="Dokumen" :action="route('dokumen.store')" :defaults="['title' => '', 'jenis' => 'LAINNYA', 'nomor' => '', 'tanggal' => '', 'opd_id' => '']" files>
    <div><label class="lbl">Judul *</label><input name="title" x-model="form.title" class="inp" required data-testid="dokumen-title-input"></div>
    <div class="grid grid-cols-2 gap-3">
        <div><label class="lbl">Jenis *</label><select name="jenis" x-model="form.jenis" class="inp">@foreach (config('esakip.doc_types') as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
        <div><label class="lbl">Tanggal</label><input type="date" name="tanggal" x-model="form.tanggal" class="inp"></div>
    </div>
    <div><label class="lbl">Nomor Dokumen</label><input name="nomor" x-model="form.nomor" class="inp"></div>
    @unless (auth()->user()->isOpdScoped())<div><label class="lbl">OPD</label><select name="opd_id" x-model="form.opd_id" class="inp"><option value="">Pemerintah Daerah (umum)</option>@foreach ($opds as $id => $n)<option value="{{ $id }}">{{ $n }}</option>@endforeach</select></div>@endunless
    <div><label class="lbl">Berkas * (pdf/doc/xls/ppt/gambar/zip, maks 10MB)</label><input type="file" name="file" required class="text-sm" data-testid="dokumen-file-input"></div>
    <p class="text-[11px] text-slate-400">Unggahan dengan judul & jenis yang sama otomatis menjadi versi baru.</p>
</x-drawer>
@endsection

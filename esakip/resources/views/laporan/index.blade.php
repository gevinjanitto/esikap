@extends('layouts.app')
@section('title', 'Pelaporan')
@section('subtitle', 'Ekspor Excel (CSV) atau cetak PDF sesuai cakupan akses Anda')
@section('heading', 'Pelaporan')

@section('content')
<form x-data="{ tahun: '{{ \App\Support\Esakip::currentYear() }}', opd: '' }" class="space-y-5">
    <div class="card reveal flex flex-wrap items-end gap-3" style="--i:1">
        <div><label class="lbl">Tahun</label><select x-model="tahun" class="inp !w-40" data-testid="laporan-year-select">@foreach ($years as $y)<option value="{{ $y }}">{{ $y }}</option>@endforeach</select></div>
        @unless (auth()->user()->isOpdScoped())<div><label class="lbl">OPD</label><select x-model="opd" class="inp !w-72" data-testid="laporan-opd-select"><option value="">Seluruh OPD</option>@foreach ($opds as $id => $n)<option value="{{ $id }}">{{ $n }}</option>@endforeach</select></div>@endunless
        <p class="text-xs text-slate-400 pb-3">Data laporan dihitung langsung dari data transaksional (sumber kebenaran tunggal).</p>
    </div>
    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach ($types as $key => [$title, $desc, $icon])
            <div class="card lift group reveal" style="--i:{{ $loop->index + 2 }}" data-testid="laporan-card-{{ $key }}">
                <span class="w-12 h-12 rounded-2xl {{ $loop->first ? 'bg-brand-600 text-white' : 'bg-brand-50 text-brand-600' }} grid place-items-center mb-5 transition-transform duration-500 group-hover:rotate-6"><i data-lucide="{{ $icon }}" class="w-5 h-5"></i></span>
                <p class="font-semibold text-[17px]">{{ $title }}</p>
                <p class="text-xs text-slate-500 mt-1 mb-5">{{ $desc }}</p>
                <div class="flex gap-2">
                    <a :href="'{{ route('laporan.export', $key) }}?tahun=' + tahun + '&opd=' + opd" data-no-transition class="btn-dark btn-sm flex-1" data-testid="laporan-export-{{ $key }}"><i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>Excel</a>
                    <a :href="'{{ route('laporan.print', $key) }}?tahun=' + tahun + '&opd=' + opd" target="_blank" data-no-transition class="btn-ghost btn-sm flex-1" data-testid="laporan-print-{{ $key }}"><i data-lucide="printer" class="w-3.5 h-3.5"></i>PDF</a>
                </div>
            </div>
        @endforeach
    </div>
</form>
@endsection

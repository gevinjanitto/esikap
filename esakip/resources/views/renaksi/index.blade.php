@extends('layouts.app')
@section('title', 'Rencana Aksi')
@section('subtitle', 'Aktivitas, PIC, jadwal & bukti dukung pencapaian target')
@section('heading', 'Rencana Aksi')
@php use App\Support\Esakip; $defaults = ['indicator_id' => '', 'year' => (string) $year, 'aktivitas' => '', 'pic' => '', 'triwulan' => 'TW1', 'target' => '', 'status' => 'belum_mulai']; @endphp

@section('actions')
    @if ($canInput)
        <button class="btn-primary" @click="$dispatch('open-drawer', { name: 'renaksi', title: 'Tambah Rencana Aksi' })" data-testid="renaksi-create-button"><i data-lucide="plus" class="w-4 h-4"></i>Rencana Aksi</button>
    @endif
@endsection

@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    @foreach (Esakip::RENAKSI_STATUS as $k => $l)
        <a href="{{ route('renaksi.index', ['tahun' => $year, 'status' => $k]) }}" class="card lift reveal !p-4 flex items-center gap-3 {{ request('status') === $k ? 'ring-2 ring-brand-500' : '' }}" style="--i:{{ $loop->index + 1 }}" data-testid="renaksi-stat-{{ $k }}">
            <span class="w-11 h-11 rounded-2xl grid place-items-center {{ Esakip::badge($k) }} ring-1"><i data-lucide="{{ ['belum_mulai' => 'circle-dashed', 'berjalan' => 'loader', 'selesai' => 'check', 'terlambat' => 'alarm-clock'][$k] }}" class="w-5 h-5"></i></span>
            <div><p class="font-display text-2xl font-bold">{{ $stats[$k] ?? 0 }}</p><p class="text-xs text-slate-500">{{ $l }}</p></div>
        </a>
    @endforeach
</div>

<div class="card reveal" style="--i:5">
    <form class="flex flex-wrap gap-2 mb-4">
        <select name="tahun" class="inp !w-32 !h-10">@foreach ($years as $y)<option @selected($y == $year)>{{ $y }}</option>@endforeach</select>
        <select name="triwulan" class="inp !w-40 !h-10"><option value="">Semua triwulan</option>@foreach (config('esakip.periode') as $k => $l)<option value="{{ $k }}" @selected(request('triwulan') === $k)>{{ $l }}</option>@endforeach</select>
        <select name="status" class="inp !w-40 !h-10"><option value="">Semua status</option>@foreach (Esakip::RENAKSI_STATUS as $k => $l)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>@endforeach</select>
        @unless (auth()->user()->isOpdScoped())<select name="opd" class="inp !w-56 !h-10"><option value="">Semua OPD</option>@foreach ($opds as $id => $n)<option value="{{ $id }}" @selected(request('opd') == $id)>{{ $n }}</option>@endforeach</select>@endunless
        <button class="btn-dark btn-sm !h-10" data-testid="renaksi-filter-button"><i data-lucide="filter" class="w-4 h-4"></i>Filter</button>
    </form>
    <div class="overflow-x-auto thin-scroll">
        <table class="tbl" data-testid="renaksi-table">
            <thead><tr><th>Aktivitas</th><th>Indikator</th><th>PIC</th><th>Jadwal</th><th>Target</th><th>Status</th><th>Bukti Dukung</th><th></th></tr></thead>
            <tbody>
            @forelse ($rows as $r)
                @php $can = $canInput && Esakip::canEdit('opd', $r->opd_id); @endphp
                <tr data-testid="renaksi-row-{{ $r->id }}">
                    <td class="font-medium max-w-[260px]">{{ $r->aktivitas }}<p class="text-[11px] text-slate-400">{{ $r->opd->singkatan ?? $r->opd->name }}</p></td>
                    <td class="text-xs text-slate-600 max-w-[220px]">{{ $r->indicator?->name }}</td>
                    <td class="text-xs">{{ $r->pic }}</td>
                    <td><span class="chip">{{ $r->triwulan }}</span></td>
                    <td class="text-xs font-semibold">{{ $r->target }}</td>
                    <td><x-badge :s="$r->status" /></td>
                    <td class="min-w-[180px]"><x-evidence :items="$r->evidences" type="rencana_aksi" :id="$r->id" :can="$can" /></td>
                    <td class="whitespace-nowrap">
                        @if ($can)
                            <button class="icon-btn-sm" @click="$dispatch('open-drawer', { name: 'renaksi', data: @js(['indicator_id' => (string) $r->indicator_id, 'year' => (string) $r->year, 'aktivitas' => $r->aktivitas, 'pic' => $r->pic, 'triwulan' => $r->triwulan, 'target' => $r->target, 'status' => $r->status]), action: @js(route('renaksi.update', $r)), method: 'PUT', title: 'Ubah Rencana Aksi' })" data-testid="renaksi-edit-{{ $r->id }}"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></button>
                            <form method="POST" action="{{ route('renaksi.destroy', $r) }}" class="inline" data-confirm="Hapus rencana aksi ini?">@csrf @method('DELETE')<button class="icon-btn-sm"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8"><x-empty icon="list-checks" title="Belum ada rencana aksi" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $rows->links('partials.pagination') }}
</div>

<x-drawer name="renaksi" title="Rencana Aksi" sub="Rencana Aksi" :action="route('renaksi.store')" :defaults="$defaults" files>
    <div><label class="lbl">Indikator *</label><select name="indicator_id" x-model="form.indicator_id" class="inp" required data-testid="renaksi-indicator-select"><option value="">Pilih indikator</option>@foreach ($indicators as $id => $n)<option value="{{ $id }}">{{ \Illuminate\Support\Str::limit($n, 90) }}</option>@endforeach</select></div>
    <div><label class="lbl">Aktivitas *</label><textarea name="aktivitas" x-model="form.aktivitas" class="inp" required data-testid="renaksi-aktivitas-input"></textarea></div>
    <div class="grid grid-cols-2 gap-3">
        <div><label class="lbl">PIC (nama/jabatan/unit) *</label><input name="pic" x-model="form.pic" class="inp" required data-testid="renaksi-pic-input"></div>
        <div><label class="lbl">Target *</label><input name="target" x-model="form.target" class="inp" required placeholder="mis. 50 fasilitas" data-testid="renaksi-target-input"></div>
        <div><label class="lbl">Tahun</label><select name="year" x-model="form.year" class="inp">@foreach ($years as $y)<option value="{{ $y }}">{{ $y }}</option>@endforeach</select></div>
        <div><label class="lbl">Jadwal *</label><select name="triwulan" x-model="form.triwulan" class="inp">@foreach (config('esakip.periode') as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
    </div>
    <div><label class="lbl">Status *</label>
        <div class="grid grid-cols-2 gap-2">@foreach (Esakip::RENAKSI_STATUS as $k => $l)<label class="flex items-center gap-2 rounded-2xl border px-3 h-11 text-sm cursor-pointer" :class="form.status === '{{ $k }}' ? 'border-brand-400 bg-brand-50/60' : 'border-slate-200'"><input type="radio" name="status" value="{{ $k }}" x-model="form.status" class="text-brand-600">{{ $l }}</label>@endforeach</div>
    </div>
    <div><label class="lbl">Bukti Dukung (dokumen/foto/laporan, maks 10MB)</label><input type="file" name="files[]" multiple class="text-sm" data-testid="renaksi-files-input"></div>
</x-drawer>
@endsection

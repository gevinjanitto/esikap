@extends('layouts.app')
@section('title', $cfg['label'])
@section('subtitle', $kind === 'pk' ? 'Kesepakatan target kinerja tahunan pimpinan OPD' : 'Penjabaran tahunan Renstra OPD: target, program, kegiatan & pagu')
@section('heading', $cfg['title'])

@section('actions')
    <form><select name="tahun" onchange="this.form.submit()" class="inp !rounded-full !w-40" data-testid="annual-year-select">@foreach ($years as $y)<option value="{{ $y }}" @selected($y == $year)>Tahun {{ $y }}</option>@endforeach</select></form>
    @if (\App\Support\Esakip::canEdit('opd'))
        <button class="btn-primary" @click="$dispatch('open-drawer', { name: 'annual' })" data-testid="annual-create-button"><i data-lucide="plus" class="w-4 h-4"></i>{{ $cfg['label'] }} Baru</button>
    @endif
@endsection

@section('content')
<div class="card reveal" style="--i:1">
    <div class="overflow-x-auto thin-scroll">
        <table class="tbl" data-testid="annual-table">
            <thead><tr><th>OPD</th><th>Tahun</th>@if ($kind === 'pk')<th>Pihak Pertama</th><th>Pihak Kedua</th>@endif<th>Jumlah Indikator</th><th>Total Pagu</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($rows as $r)
                <tr data-testid="annual-row-{{ $r->id }}">
                    <td class="font-semibold">{{ $r->opd->name }}</td>
                    <td>{{ $r->year }}</td>
                    @if ($kind === 'pk')<td>{{ $r->pihak_pertama }}<p class="text-[11px] text-slate-400">{{ $r->jabatan_pertama }}</p></td><td>{{ $r->pihak_kedua }}<p class="text-[11px] text-slate-400">{{ $r->jabatan_kedua }}</p></td>@endif
                    <td>{{ $r->items_count }}</td>
                    <td class="num">Rp {{ \App\Support\Esakip::num($r->items->sum('pagu'), 0) }}</td>
                    <td><x-badge :s="$r->status" /></td>
                    <td class="text-right"><a href="{{ route('annual.show', [$kind, $r->id]) }}" class="btn-ghost btn-sm" data-testid="annual-open-{{ $r->id }}">Buka <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="8"><x-empty :icon="$kind === 'pk' ? 'handshake' : 'calendar-range'" title="Belum ada {{ $cfg['label'] }} tahun {{ $year }}" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<x-drawer name="annual" :title="$cfg['label'].' Baru'" :sub="$cfg['label']" :action="route('annual.store', $kind)" :defaults="['opd_id' => (string) (auth()->user()->opd_id ?? ''), 'year' => (string) $year]">
    <div><label class="lbl">OPD *</label><select name="opd_id" x-model="form.opd_id" class="inp" required data-testid="annual-opd-select"><option value="">Pilih OPD</option>@foreach ($opds as $id => $n)<option value="{{ $id }}">{{ $n }}</option>@endforeach</select></div>
    <div><label class="lbl">Tahun *</label><select name="year" x-model="form.year" class="inp">@foreach ($years as $y)<option value="{{ $y }}">{{ $y }}</option>@endforeach</select></div>
    @if ($kind === 'pk')
        <div class="grid grid-cols-2 gap-3">
            <div><label class="lbl">Pihak Pertama *</label><input name="pihak_pertama" class="inp" required data-testid="pk-pihak-pertama-input"></div>
            <div><label class="lbl">Jabatan *</label><input name="jabatan_pertama" class="inp" required placeholder="Kepala Dinas ..."></div>
            <div><label class="lbl">Pihak Kedua *</label><input name="pihak_kedua" class="inp" required></div>
            <div><label class="lbl">Jabatan *</label><input name="jabatan_kedua" class="inp" required placeholder="Bupati"></div>
        </div>
    @endif
    <div><label class="lbl">Catatan</label><textarea name="catatan" class="inp"></textarea></div>
</x-drawer>
@endsection

@extends('layouts.app')
@section('title', $opd->name)
@section('subtitle')
    <span class="inline-flex items-center gap-1.5 text-sm"><a href="{{ route('dashboard', ['tahun' => $year]) }}" class="hover:text-brand-600">Dashboard</a> <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i> {{ $opd->name }} <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i> {{ $year }}</span>
@endsection
@section('heading', $opd->name)
@php use App\Support\Esakip; @endphp

@section('actions')
    <form><select name="tahun" onchange="this.form.submit()" class="inp !rounded-full !w-40">@foreach ($years as $y)<option value="{{ $y }}" @selected($y == $year)>Tahun {{ $y }}</option>@endforeach</select></form>
@endsection

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
    <div class="card !bg-ink text-white reveal" style="--i:1"><p class="text-xs text-white/60">Rata-rata capaian</p><p class="font-display text-4xl font-bold mt-2 num" x-data="countUp({{ $summary['avg'] }}, 1)"><span x-text="v"></span>%</p></div>
    <div class="card reveal" style="--i:2"><p class="text-xs text-slate-500">Realisasi tercatat</p><p class="font-display text-4xl font-bold mt-2">{{ $summary['n'] }}</p></div>
    <div class="card reveal" style="--i:3"><p class="text-xs text-slate-500">Tercapai</p><p class="font-display text-4xl font-bold mt-2 text-emerald-600">{{ $summary['ok'] }}</p></div>
</div>
@foreach (\App\Models\Indicator::LEVELS as $lvl => $lbl)
    @continue(! isset($indicators[$lvl]))
    <div class="card mb-5 reveal" style="--i:{{ $loop->index + 4 }}" data-testid="drill-level-{{ $lvl }}">
        <h3 class="card-title mb-4">Indikator {{ $lbl }}</h3>
        <div class="overflow-x-auto thin-scroll">
            <table class="tbl">
                <thead><tr><th>Indikator</th><th>Induk</th><th>Target {{ $year }}</th>@foreach (config('esakip.periode') as $k => $l)<th>{{ $k }}</th>@endforeach<th>Bukti</th></tr></thead>
                <tbody>
                @foreach ($indicators[$lvl] as $ind)
                    <tr>
                        <td class="font-medium max-w-[280px]">{{ $ind->name }}<p class="text-[11px] text-slate-400">{{ $ind->satuan?->name }} · {{ ucfirst($ind->formula) }}</p></td>
                        <td class="text-xs text-slate-500 max-w-[220px]">{{ \Illuminate\Support\Str::limit($ind->owner?->name, 70) }}</td>
                        <td class="num font-semibold">{{ Esakip::num($ind->targetFor($year)) }}</td>
                        @foreach (config('esakip.periode') as $k => $l)
                            @php $re = $ind->realisasis->firstWhere('periode', $k); @endphp
                            <td class="min-w-[120px]">@if ($re)<x-capbar :v="$re->capaian" sm /><p class="text-[10px] text-slate-400">{{ Esakip::num($re->realisasi) }} / {{ Esakip::num($re->target) }}</p>@else<span class="text-slate-300">—</span>@endif</td>
                        @endforeach
                        <td><a href="{{ route('realisasi.index', ['tahun' => $year, 'opd' => $opd->id]) }}" class="chip hover:text-brand-600"><i data-lucide="paperclip" class="w-3 h-3"></i>{{ $ind->realisasis->sum('evidences_count') }}</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach
@if ($indicators->isEmpty())<div class="card"><x-empty icon="target" title="OPD ini belum memiliki indikator" /></div>@endif
@endsection

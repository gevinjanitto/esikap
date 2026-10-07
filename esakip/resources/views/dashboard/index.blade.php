@extends('layouts.app')
@section('title', 'Dashboard')
@section('subtitle', 'Kelola dan pantau kinerja '.(auth()->user()->opd?->name ?? 'pemerintah daerah'))
@section('heading', 'Dashboard Kinerja')
@php use App\Support\Esakip; @endphp

@section('tabs')
    <div class="pill-tabs" data-testid="dashboard-period-tabs">
        @foreach (config('esakip.periode') as $k => $lbl)
            <a href="{{ route('dashboard', ['tahun' => $year, 'periode' => $k]) }}" class="{{ $tw === $k ? 'pill-tab-active' : 'pill-tab' }}" data-testid="tab-{{ strtolower($k) }}">{{ $lbl }}</a>
        @endforeach
        <a href="{{ \App\Support\Esakip::canMenu('laporan') ? route('laporan.index') : '#' }}" class="pill-tab hidden xl:inline-flex">Laporan</a>
    </div>
@endsection

@section('actions')
    <form class="flex items-center gap-2">
        <input type="hidden" name="periode" value="{{ $tw }}">
        <select name="tahun" onchange="this.form.submit()" class="inp !rounded-full !h-12 !w-40 bg-white/80" data-testid="dashboard-year-select">
            @foreach ($years as $y)<option value="{{ $y }}" @selected($y == $year)>Tahun {{ $y }}</option>@endforeach
        </select>
    </form>
    <form action="{{ route('indikator.index') }}" class="relative w-full sm:w-72">
        <i data-lucide="search" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input name="q" class="inp !rounded-full !h-12 pl-11 bg-white/80" placeholder="Cari indikator, sasaran..." data-testid="global-search-input">
    </form>
@endsection

@section('content')
<div class="md:hidden mb-4 overflow-x-auto thin-scroll">
    <div class="pill-tabs">
        @foreach (config('esakip.periode') as $k => $lbl)
            <a href="{{ route('dashboard', ['tahun' => $year, 'periode' => $k]) }}" class="{{ $tw === $k ? 'pill-tab-active' : 'pill-tab' }}">{{ $k }}</a>
        @endforeach
    </div>
</div>

{{-- KPI strip --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    @foreach ([
        ['Rata-rata Capaian', $kpi['avg'], '%', 'gauge', 1, 'kpi-avg'],
        ['Indikator Tercapai', $kpi['tercapai'], '/'.$kpi['total'], 'target', 0, 'kpi-tercapai'],
        ['Indikator Kinerja', $kpi['indikator'], '', 'layers', 0, 'kpi-indikator'],
        [auth()->user()->isOpdScoped() ? 'Renstra Ditetapkan' : 'OPD Aktif', auth()->user()->isOpdScoped() ? $kpi['renstra'] : $kpi['opd'], '', 'building-2', 0, 'kpi-opd'],
    ] as $k => [$lbl, $val, $suf, $ic, $dec, $tid])
        <div class="card lift reveal {{ $k === 0 ? '!bg-ink text-white' : '' }}" style="--i:{{ $k + 1 }}" data-testid="{{ $tid }}">
            <div class="flex items-center justify-between mb-5">
                <span class="w-10 h-10 rounded-2xl grid place-items-center {{ $k === 0 ? 'bg-brand-600' : 'bg-brand-50 text-brand-600' }}"><i data-lucide="{{ $ic }}" class="w-[18px] h-[18px]"></i></span>
                <span class="text-[11px] {{ $k === 0 ? 'text-white/60' : 'text-slate-400' }}">{{ $tw }} · {{ $year }}</span>
            </div>
            <p class="font-display text-3xl lg:text-[34px] font-bold num" x-data="countUp({{ (float) $val }}, {{ $dec }})"><span x-text="v">0</span><span class="text-base font-semibold {{ $k === 0 ? 'text-white/60' : 'text-slate-400' }}">{{ $suf }}</span></p>
            <p class="text-sm mt-1 {{ $k === 0 ? 'text-white/70' : 'text-slate-500' }}">{{ $lbl }}</p>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-[280px_1fr_300px] gap-5">
    {{-- left: tasks --}}
    <div class="card reveal" style="--i:5" data-testid="dashboard-tasks-card" x-data="{ t: 'aktif' }">
        <div class="flex items-center justify-between mb-4">
            <h3 class="card-title">Rencana Aksi</h3>
            <a href="{{ \App\Support\Esakip::canMenu('renaksi') ? route('renaksi.index') : '#' }}" class="icon-btn arrow-btn" data-testid="tasks-open-link"><i data-lucide="arrow-up-right" class="w-4 h-4"></i></a>
        </div>
        <div class="flex gap-2 mb-4">
            <button @click="t = 'aktif'" :class="t === 'aktif' ? 'bg-ink text-white' : 'bg-white border border-slate-200 text-slate-600'" class="h-8 px-4 rounded-full text-xs font-semibold transition-colors" data-testid="tasks-tab-active">Berjalan</button>
            <button @click="t = 'telat'" :class="t === 'telat' ? 'bg-ink text-white' : 'bg-white border border-slate-200 text-slate-600'" class="h-8 px-4 rounded-full text-xs font-semibold transition-colors" data-testid="tasks-tab-late">Terlambat</button>
        </div>
        <div class="flex items-center justify-between rounded-2xl border border-slate-200 px-3 h-11 mb-4 text-sm">
            <span class="flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-ink text-white text-[11px] grid place-items-center font-bold">{{ $tasks->count() }}</span> Aktivitas tahun {{ $year }}</span>
        </div>
        <div class="space-y-3">
            @php $tones = ['bg-orange-50', 'bg-sky-50', 'bg-rose-50', 'bg-emerald-50', 'bg-violet-50', 'bg-amber-50']; @endphp
            @forelse ($tasks as $k => $t)
                <a href="{{ \App\Support\Esakip::canMenu('renaksi') ? route('renaksi.index', ['tahun' => $year]) : '#' }}" x-show="t === 'aktif' ? @js($t->status !== 'terlambat') : @js($t->status === 'terlambat')" class="block rounded-2xl p-4 {{ $tones[$k % 6] }} lift group" data-testid="task-item-{{ $t->id }}">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <span class="w-8 h-8 rounded-xl bg-white grid place-items-center text-brand-600 shadow-sm"><i data-lucide="list-checks" class="w-4 h-4"></i></span>
                        <x-badge :s="$t->status" />
                    </div>
                    <p class="text-sm font-semibold leading-snug">{{ \Illuminate\Support\Str::limit($t->aktivitas, 60) }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ $t->pic }} · {{ $t->triwulan }} · {{ $t->target }}</p>
                </a>
            @empty
                <x-empty icon="list-checks" title="Belum ada rencana aksi" />
            @endforelse
        </div>
    </div>

    {{-- center --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 content-start">
        <div class="card reveal" style="--i:6" data-testid="dashboard-donut-card">
            <div class="flex items-center justify-between">
                <h3 class="card-title">Status Capaian</h3>
                <a href="{{ \App\Support\Esakip::canMenu('realisasi') ? route('realisasi.index', ['tahun' => $year, 'periode' => $tw]) : '#' }}" class="icon-btn arrow-btn"><i data-lucide="arrow-up-right" class="w-4 h-4"></i></a>
            </div>
            <div class="relative h-48 mt-3"><canvas id="donut"></canvas>
                <div class="absolute inset-0 grid place-items-center pointer-events-none"><div class="text-center"><p class="font-display text-2xl font-bold num">{{ $kpi['total'] }}</p><p class="text-[11px] text-slate-400">indikator</p></div></div>
            </div>
            <div class="flex flex-wrap justify-center gap-x-4 gap-y-1 mt-3 text-xs text-slate-600">
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>Tercapai: {{ $donut['tercapai'] }}</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>Perhatian: {{ $donut['perlu_perhatian'] }}</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-brand-600"></span>Tidak: {{ $donut['tidak_tercapai'] }}</span>
            </div>
        </div>
        <div class="card reveal" style="--i:7" data-testid="dashboard-trend-card">
            <div class="flex items-center justify-between">
                <h3 class="card-title">Tren Capaian {{ $year }}</h3>
                <a href="{{ \App\Support\Esakip::canMenu('laporan') ? route('laporan.index') : '#' }}" class="icon-btn arrow-btn"><i data-lucide="arrow-up-right" class="w-4 h-4"></i></a>
            </div>
            <div class="h-56 mt-3"><canvas id="trend"></canvas></div>
        </div>
        <div class="card lg:col-span-2 reveal" style="--i:8" data-testid="dashboard-opd-bars-card">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="card-title">Capaian per OPD</h3>
                    <p class="text-xs text-slate-400">Klik OPD untuk drill-down ke sasaran → indikator → bukti dukung</p>
                </div>
                <a href="{{ route('cascading') }}" class="icon-btn arrow-btn"><i data-lucide="sliders-horizontal" class="w-4 h-4"></i></a>
            </div>
            <div class="space-y-5">
                @php $barColors = ['bg-ink', 'bg-brand-600', 'bg-sky-500', 'bg-emerald-500', 'bg-amber-400', 'bg-violet-500']; @endphp
                @forelse ($opdBars as $k => $b)
                    <a href="{{ route('dashboard.opd', [$b['opd'], 'tahun' => $year]) }}" class="block group" data-testid="opd-bar-{{ $b['opd']->id }}">
                        <div class="flex items-center justify-between text-sm mb-2">
                            <span class="font-medium group-hover:text-brand-600 transition-colors">{{ $b['opd']->name }}</span>
                            <span class="text-slate-500 num text-xs">{{ $b['n'] }} indikator &nbsp;|&nbsp; <b class="text-ink">{{ Esakip::num($b['avg'], 1) }}%</b></span>
                        </div>
                        <div class="bar"><div class="bar-fill moving {{ $barColors[$k % 6] }}" style="width: {{ min($b['avg'], 100) }}%; animation-delay: {{ 0.3 + $k * 0.12 }}s"></div></div>
                    </a>
                @empty
                    <x-empty icon="bar-chart-3" title="Belum ada realisasi pada periode ini" />
                @endforelse
            </div>
        </div>
        <div class="card lg:col-span-2 reveal" style="--i:9" data-testid="dashboard-activity-card">
            <div class="flex items-center justify-between mb-4"><h3 class="card-title">Aktivitas Alur Kerja Terbaru</h3><span class="chip"><i data-lucide="history" class="w-3 h-3"></i>Real-time</span></div>
            <div class="grid sm:grid-cols-2 gap-3">
                @forelse ($activity as $a)
                    <div class="flex gap-3 rounded-2xl bg-slate-50 p-3">
                        <span class="w-9 h-9 rounded-full bg-white grid place-items-center text-[11px] font-bold shrink-0 shadow-sm">{{ $a->user?->initials }}</span>
                        <div class="min-w-0"><p class="text-sm"><b>{{ $a->user?->name }}</b> <span class="text-slate-500">{{ strtolower(\App\Support\Workflow::ACTIONS[$a->action]['label'] ?? $a->action) }}</span></p><p class="text-xs text-slate-500 truncate">{{ $a->approvable->workflowTitle() }}</p><p class="text-[10px] text-slate-400">{{ $a->created_at->diffForHumans() }}</p></div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Belum ada aktivitas.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- right --}}
    <div class="space-y-5">
        <div class="card reveal" style="--i:9" data-testid="dashboard-pending-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="card-title">Menunggu Persetujuan</h3>
                <a href="{{ \App\Support\Esakip::canMenu('approval') ? route('approval.index') : '#' }}" class="icon-btn"><i data-lucide="stamp" class="w-4 h-4"></i></a>
            </div>
            <div class="space-y-3">
                @forelse ($pending as $p)
                    <a href="{{ $p['model']->workflowUrl() }}" class="block rounded-2xl border border-slate-200/80 p-3.5 hover:border-brand-200 lift group" data-testid="pending-item-{{ $p['type'] }}-{{ $p['model']->id }}">
                        <div class="flex items-start gap-3">
                            <div class="text-[11px] text-slate-400 w-16 shrink-0">{{ \App\Support\Workflow::LABELS[$p['type']] }}<p class="text-ink font-semibold text-xs mt-1">{{ $p['model']->updated_at->format('d M') }}</p></div>
                            <div class="flex-1 min-w-0"><p class="text-sm font-semibold line-clamp-2">{{ $p['model']->workflowTitle() }}</p><x-badge :s="$p['model']->status" /></div>
                            <span class="arrow-btn text-slate-400"><i data-lucide="arrow-up-right" class="w-4 h-4"></i></span>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-slate-400 py-4 text-center">Tidak ada antrian persetujuan</p>
                @endforelse
            </div>
            <a href="{{ \App\Support\Esakip::canMenu('approval') ? route('approval.index') : '#' }}" class="inline-flex items-center gap-1 text-sm font-medium mt-4 hover:text-brand-600">Lihat semua <i data-lucide="chevron-right" class="w-4 h-4"></i></a>
        </div>

        <div class="card reveal" style="--i:10" data-testid="dashboard-rekomendasi-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="card-title">Rekomendasi Terbuka</h3>
                <a href="{{ \App\Support\Esakip::canMenu('evaluasi') ? route('evaluasi.index') : '#' }}" class="icon-btn"><i data-lucide="sliders-horizontal" class="w-4 h-4"></i></a>
            </div>
            <div class="space-y-3">
                @forelse ($reks as $k => $rk)
                    <div class="rounded-2xl border border-slate-200/80 p-3.5" data-testid="rekomendasi-item-{{ $rk->id }}">
                        <div class="flex gap-3">
                            <span class="w-9 h-9 rounded-full grid place-items-center text-[11px] font-bold shrink-0 {{ ['bg-rose-100 text-rose-700', 'bg-emerald-100 text-emerald-700', 'bg-sky-100 text-sky-700'][$k % 3] }}">{{ $rk->evaluasi->opd->singkatan ? mb_substr($rk->evaluasi->opd->singkatan, 0, 2) : 'OP' }}</span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold">{{ $rk->evaluasi->opd->name }}</p>
                                <p class="text-xs text-slate-500 line-clamp-2">{{ $rk->uraian }}</p>
                                <a href="{{ \App\Support\Esakip::canMenu('evaluasi') ? route('evaluasi.show', $rk->evaluasi_id) : '#' }}" class="inline-flex items-center gap-1 mt-2 text-[11px] font-semibold bg-slate-100 rounded-full px-3 py-1 hover:bg-brand-50 hover:text-brand-700">Tindak lanjut <i data-lucide="chevron-right" class="w-3 h-3"></i></a>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 py-4 text-center">Tidak ada rekomendasi terbuka</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    Chart.defaults.font.family = 'Onest';
    Chart.defaults.color = '#94A3B8';
    new Chart(document.getElementById('donut'), {
        type: 'doughnut',
        data: { labels: ['Tercapai', 'Perlu Perhatian', 'Tidak Tercapai'], datasets: [{ data: @js(array_values($donut->all())), backgroundColor: ['#10B981', '#FBBF24', '#E11D2E'], borderWidth: 0, borderRadius: 8, spacing: 4 }] },
        options: { cutout: '72%', plugins: { legend: { display: false } }, animation: { animateRotate: true, duration: 1600, easing: 'easeOutQuart' }, maintainAspectRatio: false },
    });
    const ctx = document.getElementById('trend').getContext('2d');
    const g1 = ctx.createLinearGradient(0, 0, 0, 220); g1.addColorStop(0, 'rgba(225,29,46,.22)'); g1.addColorStop(1, 'rgba(225,29,46,0)');
    const g2 = ctx.createLinearGradient(0, 0, 0, 220); g2.addColorStop(0, 'rgba(59,130,246,.18)'); g2.addColorStop(1, 'rgba(59,130,246,0)');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['TW I', 'TW II', 'TW III', 'TW IV'],
            datasets: [
                { label: 'Rata-rata capaian (%)', data: @js($trend->pluck('avg')), borderColor: '#E11D2E', backgroundColor: g1, fill: true, tension: .45, pointRadius: 4, pointBackgroundColor: '#fff', pointBorderWidth: 2, borderWidth: 2.5, spanGaps: true },
                { label: 'Tahun {{ $year - 1 }} (%)', data: @js($trend->pluck('prev')), borderColor: '#94A3B8', borderDash: [5, 5], fill: false, tension: .45, pointRadius: 3, borderWidth: 1.5, spanGaps: true },
                { label: '% indikator tercapai', data: @js($trend->pluck('pct')), borderColor: '#3B82F6', backgroundColor: g2, fill: true, tension: .45, pointRadius: 4, pointBackgroundColor: '#fff', pointBorderWidth: 2, borderWidth: 2, spanGaps: true },
            ],
        },
        options: {
            maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 8, usePointStyle: true, font: { size: 11 } } }, tooltip: { backgroundColor: '#0B0F19', padding: 10, cornerRadius: 12 } },
            scales: { y: { suggestedMin: 0, suggestedMax: 110, grid: { color: '#F1F5F9' }, border: { display: false } }, x: { grid: { display: false }, border: { display: false } } },
            animation: { duration: 1800, easing: 'easeOutQuart' },
        },
    });
});
</script>
@endpush

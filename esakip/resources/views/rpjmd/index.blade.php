@extends('layouts.app')
@section('title', 'RPJMD')
@section('subtitle', 'Rencana Pembangunan Jangka Menengah Daerah')
@section('heading', 'RPJMD')
@php use App\Support\Esakip; @endphp

@section('actions')
    @if ($all->count() > 1)
        <form><select name="id" onchange="this.form.submit()" class="inp !rounded-full !w-64">@foreach ($all as $a)<option value="{{ $a->id }}" @selected($rpjmd?->id === $a->id)>{{ $a->name }}</option>@endforeach</select></form>
    @endif
    @if (Esakip::canEdit('rpjmd'))
        <button class="btn-dark" @click="$dispatch('open-drawer', { name: 'rpjmd', title: 'RPJMD Baru' })" data-testid="rpjmd-create-button"><i data-lucide="plus" class="w-4 h-4"></i>RPJMD Baru</button>
    @endif
@endsection

@section('content')
@if (! $rpjmd)
    <div class="card"><x-empty icon="landmark" title="Belum ada RPJMD" text="Buat dokumen RPJMD untuk periode aktif terlebih dahulu." /></div>
@else
<div class="grid xl:grid-cols-[1fr_340px] gap-5">
    <div class="space-y-5">
        <div class="card !p-6 reveal relative overflow-hidden" style="--i:1" data-testid="rpjmd-header-card">
            <span class="absolute -right-16 -top-16 w-56 h-56 rounded-full bg-brand-50"></span>
            <div class="relative flex flex-wrap items-start gap-4">
                <div class="flex-1 min-w-[260px]">
                    <div class="flex items-center gap-2 mb-2"><x-badge :s="$rpjmd->status" tid="rpjmd-status-badge" /><span class="chip">{{ $rpjmd->period->name }}</span><span class="chip">v{{ $rpjmd->version }}</span></div>
                    <h2 class="text-2xl font-semibold tracking-tight">{{ $rpjmd->name }}</h2>
                    <p class="text-xs text-slate-500 mt-1">{{ $rpjmd->nomor_dokumen }} @if ($rpjmd->tanggal_dokumen)· {{ $rpjmd->tanggal_dokumen->translatedFormat('d F Y') }}@endif</p>
                    <div class="mt-4 rounded-2xl bg-ink text-white p-4">
                        <p class="eyebrow !text-brand-400 mb-1">Visi</p>
                        <p class="text-sm leading-relaxed">{{ $rpjmd->visi ?: '-' }}</p>
                    </div>
                </div>
                <div class="flex flex-col gap-2 items-end">
                    <x-workflow :model="$rpjmd" type="rpjmd" :actions="$actions" />
                    @if ($editable)
                        <button class="btn-ghost btn-sm" @click="$dispatch('open-drawer', { name: 'rpjmd', data: @js(['period_id' => (string) $rpjmd->period_id, 'name' => $rpjmd->name, 'visi' => $rpjmd->visi, 'nomor_dokumen' => $rpjmd->nomor_dokumen, 'tanggal_dokumen' => $rpjmd->tanggal_dokumen?->format('Y-m-d')]), action: @js(route('rpjmd.update', $rpjmd)), method: 'PUT', title: 'Ubah RPJMD' })" data-testid="rpjmd-edit-button"><i data-lucide="pencil" class="w-3.5 h-3.5"></i>Ubah Info</button>
                        <button class="btn-primary btn-sm" @click="$dispatch('open-drawer', { name: 'tree', data: { parent_id: {{ $rpjmd->id }}, type: 'misi' }, action: @js(route('tree.store', 'misi')), title: 'Tambah Misi' })" data-testid="add-misi-button"><i data-lucide="plus" class="w-3.5 h-3.5"></i>Misi</button>
                    @endif
                </div>
            </div>
            @if (! $rpjmd->isEditable())
                <p class="relative mt-4 text-xs text-slate-500 flex items-center gap-2"><i data-lucide="lock" class="w-3.5 h-3.5 text-brand-600"></i>Dokumen terkunci (read-only). Perubahan memerlukan “Buka Kunci” oleh peran berwenang dan tercatat di audit trail.</p>
            @endif
        </div>

        @forelse ($rpjmd->misis as $mi => $misi)
            <div class="card reveal" style="--i:{{ $mi + 2 }}" x-data="{ o: true }" data-testid="misi-{{ $misi->id }}">
                <div class="flex items-start gap-3">
                    <button @click="o = !o" class="w-11 h-11 rounded-2xl bg-ink text-white font-display text-xs font-bold grid place-items-center shrink-0 press">{{ $misi->code }}</button>
                    <div class="flex-1 min-w-0"><p class="eyebrow mb-0.5">Misi</p><p class="font-semibold leading-snug">{{ $misi->name }}</p></div>
                    <x-node-actions type="misi" :item="$misi" :parent-id="$rpjmd->id" child-type="tujuan_daerah" child-label="Tujuan" :editable="$editable" />
                    <button class="icon-btn-sm" @click="o = !o"><i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="o || 'rotate-180'"></i></button>
                </div>
                <div x-show="o" x-transition class="mt-4 ml-5 pl-6 border-l-2 border-dashed border-brand-100 space-y-4">
                    @foreach ($misi->tujuans as $tujuan)
                        <div data-testid="tujuan-daerah-{{ $tujuan->id }}">
                            <div class="flex items-start gap-3">
                                <span class="node-dot mt-1.5 -ml-[31px]"></span>
                                <div class="flex-1 min-w-0"><p class="text-[11px] font-semibold text-slate-400">TUJUAN {{ $tujuan->code }}</p><p class="text-sm font-semibold">{{ $tujuan->name }}</p></div>
                                <x-node-actions type="tujuan_daerah" :item="$tujuan" :parent-id="$misi->id" child-type="sasaran_daerah" child-label="Sasaran" :editable="$editable" />
                            </div>
                            <div class="mt-3 space-y-3">
                                @foreach ($tujuan->sasarans as $s)
                                    <div class="rounded-[22px] border border-slate-200/80 p-4 bg-white" data-testid="sasaran-daerah-{{ $s->id }}">
                                        <div class="flex items-start gap-3 mb-3">
                                            <span class="w-8 h-8 rounded-xl bg-brand-600 text-white grid place-items-center shrink-0"><i data-lucide="flag" class="w-4 h-4"></i></span>
                                            <div class="flex-1 min-w-0"><p class="text-[11px] font-semibold text-brand-600">SASARAN {{ $s->code }}</p><p class="text-sm font-semibold">{{ $s->name }}</p></div>
                                            <x-node-actions type="sasaran_daerah" :item="$s" :parent-id="$tujuan->id" indicator-owner="sasaran_daerah" :editable="$editable" />
                                        </div>
                                        <div class="space-y-2">
                                            @foreach ($s->indicators as $ind)
                                                @include('partials.indicator', ['ind' => $ind, 'editable' => $editable])
                                            @endforeach
                                        </div>
                                        <div class="mt-3 flex flex-wrap items-center gap-2">
                                            <span class="text-[11px] font-semibold text-slate-400">OPD TERKAIT:</span>
                                            @foreach ($s->opds as $o)
                                                <span class="badge {{ $o->pivot->peran === 'utama' ? 'bg-ink text-white ring-ink' : 'bg-white text-slate-600 ring-slate-200' }}" data-testid="assigned-opd-{{ $s->id }}-{{ $o->id }}">
                                                    {{ $o->singkatan ?? $o->name }} · {{ ucfirst($o->pivot->peran) }}
                                                    @if ($editable)
                                                        <form method="POST" action="{{ route('rpjmd.unassign', [$s, $o]) }}" class="inline" data-confirm="Lepas penugasan OPD ini?">@csrf @method('DELETE')<button class="ml-1 opacity-60 hover:opacity-100"><i data-lucide="x" class="w-3 h-3"></i></button></form>
                                                    @endif
                                                </span>
                                            @endforeach
                                            @if ($editable)
                                                <form method="POST" action="{{ route('rpjmd.assign', $s) }}" class="flex items-center gap-1.5" data-testid="assign-opd-form-{{ $s->id }}">
                                                    @csrf
                                                    <select name="opd_id" class="inp !h-8 !text-xs !w-44 !rounded-full" required><option value="">+ Tugaskan OPD</option>@foreach ($opds as $id => $n)<option value="{{ $id }}">{{ $n }}</option>@endforeach</select>
                                                    <select name="peran" class="inp !h-8 !text-xs !w-32 !rounded-full"><option value="utama">Utama</option><option value="pendukung">Pendukung</option></select>
                                                    <button class="btn-dark btn-xs">Simpan</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="card"><x-empty icon="landmark" title="Belum ada misi" text="Tambahkan misi untuk mulai menyusun struktur RPJMD." /></div>
        @endforelse
    </div>

    <div class="space-y-5">
        <div class="card reveal" style="--i:2">
            <h3 class="card-title mb-4">Ringkasan</h3>
            @php $sas = $rpjmd->misis->flatMap->tujuans->flatMap->sasarans; @endphp
            <div class="grid grid-cols-2 gap-3">
                @foreach ([['Misi', $rpjmd->misis->count()], ['Tujuan', $rpjmd->misis->flatMap->tujuans->count()], ['Sasaran', $sas->count()], ['Indikator', $sas->flatMap->indicators->count()]] as [$l, $v])
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="font-display text-2xl font-bold">{{ $v }}</p><p class="text-xs text-slate-500">{{ $l }}</p></div>
                @endforeach
            </div>
        </div>
        <div class="card reveal" style="--i:3"><h3 class="card-title mb-4">Riwayat Persetujuan</h3><x-timeline :logs="$rpjmd->approvalLogs" /></div>
    </div>
</div>
@include('partials.indicator-drawer')
@include('partials.tree-drawer')
@endif

<x-drawer name="rpjmd" title="RPJMD" sub="Dokumen RPJMD" :action="route('rpjmd.store')" :defaults="['period_id' => (string) Esakip::activePeriod()?->id, 'name' => '', 'visi' => '', 'nomor_dokumen' => '', 'tanggal_dokumen' => '']">
    <div><label class="lbl">Periode *</label><select name="period_id" x-model="form.period_id" class="inp">@foreach (\App\Models\Period::orderByDesc('start_year')->get() as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
    <div><label class="lbl">Nama Dokumen *</label><input name="name" x-model="form.name" class="inp" required data-testid="rpjmd-name-input"></div>
    <div><label class="lbl">Visi</label><textarea name="visi" x-model="form.visi" class="inp"></textarea></div>
    <div class="grid grid-cols-2 gap-3">
        <div><label class="lbl">Nomor Dokumen</label><input name="nomor_dokumen" x-model="form.nomor_dokumen" class="inp"></div>
        <div><label class="lbl">Tanggal</label><input type="date" name="tanggal_dokumen" x-model="form.tanggal_dokumen" class="inp"></div>
    </div>
</x-drawer>
@endsection

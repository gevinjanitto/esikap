@extends('layouts.app')
@section('title', 'Evaluasi '.$evaluasi->opd->name)
@section('subtitle', 'Evaluasi Kinerja · Tahun '.$evaluasi->year)
@section('heading', $evaluasi->opd->name)
@php use App\Support\Esakip; @endphp

@section('actions')
    <a href="{{ route('evaluasi.index') }}" class="btn-ghost"><i data-lucide="arrow-left" class="w-4 h-4"></i>Kembali</a>
    @if ($canEval)
        <button class="btn-dark" @click="$dispatch('open-drawer', { name: 'eval-edit' })" data-testid="evaluasi-edit-button"><i data-lucide="pencil" class="w-4 h-4"></i>Ubah Penilaian</button>
        <button class="btn-primary" @click="$dispatch('open-drawer', { name: 'rek' })" data-testid="rekomendasi-create-button"><i data-lucide="plus" class="w-4 h-4"></i>Rekomendasi</button>
    @endif
@endsection

@section('content')
<div class="grid xl:grid-cols-[340px_1fr] gap-5">
    <div class="space-y-5">
        <div class="card !bg-ink text-white reveal relative overflow-hidden" style="--i:1">
            <span class="absolute -right-10 -bottom-10 w-40 h-40 rounded-full bg-brand-600/40 blur-2xl"></span>
            <p class="text-xs text-white/60">Predikat SAKIP</p>
            <p class="font-display text-6xl font-bold mt-2" data-testid="evaluasi-predikat">{{ $evaluasi->predikat }}</p>
            <p class="text-sm text-white/70 mt-2">Nilai <b class="text-white" data-testid="evaluasi-nilai">{{ Esakip::num($evaluasi->nilai) }}</b> / 100</p>
            <div class="bar sm mt-3 !bg-white/10"><div class="bar-fill bg-brand-600" style="width: {{ $evaluasi->nilai }}%"></div></div>
            <p class="text-[11px] text-white/50 mt-4">Evaluator: {{ $evaluasi->evaluator?->name }} · <x-badge :s="$evaluasi->status" /></p>
        </div>
        <div class="card reveal" style="--i:2">
            <h3 class="card-title mb-3">Capaian Realisasi {{ $evaluasi->year }}</h3>
            <x-capbar :v="$summary['avg'] ?? 0" />
            <p class="text-xs text-slate-500 mt-2">{{ $summary['tercapai'] }} dari {{ $summary['count'] }} realisasi berstatus tercapai</p>
        </div>
        <div class="card reveal" style="--i:3"><h3 class="card-title mb-2">Catatan Evaluator</h3><p class="text-sm text-slate-600 leading-relaxed">{{ $evaluasi->catatan ?: '-' }}</p></div>
    </div>

    <div class="card reveal" style="--i:2">
        <h3 class="card-title mb-4">Rekomendasi & Tindak Lanjut</h3>
        <div class="space-y-4">
            @forelse ($evaluasi->rekomendasis as $i => $r)
                <div class="rounded-[22px] border border-slate-200/80 p-5" x-data="{ tl: false, vf: false }" data-testid="rekomendasi-{{ $r->id }}">
                    <div class="flex items-start gap-3">
                        <span class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 grid place-items-center font-bold text-sm shrink-0">{{ $i + 1 }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium">{{ $r->uraian }}</p>
                            <p class="text-[11px] text-slate-400 mt-1">Batas waktu: {{ $r->batas_waktu?->translatedFormat('d M Y') ?? '-' }}</p>
                        </div>
                        <x-badge :s="$r->status" tid="rekomendasi-status-{{ $r->id }}" />
                    </div>
                    <div class="mt-4 ml-12 space-y-3">
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <p class="text-[11px] font-semibold text-slate-500 mb-1">TINDAK LANJUT OPD</p>
                            <p class="text-sm text-slate-700">{{ $r->tindak_lanjut ?: 'Belum ada tindak lanjut.' }}</p>
                            <div class="mt-2"><x-evidence :items="$r->evidences" type="rekomendasi" :id="$r->id" :can="$canTl && $r->status !== 'terverifikasi'" /></div>
                        </div>
                        @if ($r->catatan_verifikasi)<p class="text-xs text-slate-600"><b>Catatan verifikasi:</b> {{ $r->catatan_verifikasi }}</p>@endif
                        <div class="flex flex-wrap gap-2">
                            @if ($canTl && $r->status !== 'terverifikasi')<button class="btn-dark btn-xs" @click="tl = !tl" data-testid="tindak-lanjut-toggle-{{ $r->id }}"><i data-lucide="send" class="w-3 h-3"></i>Isi Tindak Lanjut</button>@endif
                            @if ($canEval && in_array($r->status, ['selesai', 'proses']))<button class="btn-ghost btn-xs" @click="vf = !vf" data-testid="verifikasi-toggle-{{ $r->id }}"><i data-lucide="shield-check" class="w-3 h-3"></i>Verifikasi</button>@endif
                        </div>
                        <form x-show="tl" x-cloak x-transition method="POST" action="{{ route('rekomendasi.tl', $r) }}" enctype="multipart/form-data" class="space-y-2 rounded-2xl border border-slate-200 p-3">
                            @csrf @method('PUT')
                            <textarea name="tindak_lanjut" class="inp" required placeholder="Uraikan tindak lanjut yang telah dilakukan" data-testid="tindak-lanjut-input-{{ $r->id }}">{{ $r->tindak_lanjut }}</textarea>
                            <div class="flex flex-wrap items-center gap-2">
                                <select name="status" class="inp !h-9 !w-40 !text-xs"><option value="selesai">Selesai</option><option value="proses">Dalam Proses</option></select>
                                <input type="file" name="files[]" multiple class="text-xs">
                                <button class="btn-primary btn-xs ml-auto" data-testid="tindak-lanjut-submit-{{ $r->id }}">Simpan</button>
                            </div>
                        </form>
                        <form x-show="vf" x-cloak x-transition method="POST" action="{{ route('rekomendasi.verify', $r) }}" class="flex flex-wrap gap-2 rounded-2xl border border-slate-200 p-3">
                            @csrf @method('PUT')
                            <input name="catatan_verifikasi" class="inp !h-9 !text-xs flex-1 min-w-[200px]" placeholder="Catatan verifikasi">
                            <button name="status" value="terverifikasi" class="btn-dark btn-xs" data-testid="verify-accept-{{ $r->id }}">Terverifikasi</button>
                            <button name="status" value="proses" class="btn-ghost btn-xs" data-testid="verify-return-{{ $r->id }}">Kembalikan</button>
                        </form>
                    </div>
                </div>
            @empty
                <x-empty icon="message-square" title="Belum ada rekomendasi" />
            @endforelse
        </div>
    </div>
</div>

@if ($canEval)
<x-drawer name="rek" title="Tambah Rekomendasi" sub="Rekomendasi" :action="route('rekomendasi.store', $evaluasi)" :defaults="['uraian' => '', 'batas_waktu' => '']">
    <div><label class="lbl">Uraian Rekomendasi *</label><textarea name="uraian" x-model="form.uraian" class="inp" required data-testid="rekomendasi-uraian-input"></textarea></div>
    <div><label class="lbl">Batas Waktu</label><input type="date" name="batas_waktu" x-model="form.batas_waktu" class="inp"></div>
</x-drawer>
<x-drawer name="eval-edit" title="Ubah Penilaian" sub="Evaluasi" :action="route('evaluasi.update', $evaluasi)" method="PUT" :defaults="['nilai' => (float) $evaluasi->nilai, 'catatan' => $evaluasi->catatan, 'status' => $evaluasi->status]">
    <div><label class="lbl">Nilai *</label><input name="nilai" x-model="form.nilai" type="number" step="0.01" class="inp" required></div>
    <div><label class="lbl">Catatan</label><textarea name="catatan" x-model="form.catatan" class="inp"></textarea></div>
    <div><label class="lbl">Status</label><select name="status" x-model="form.status" class="inp"><option value="draft">Draft</option><option value="final">Final</option></select></div>
</x-drawer>
@endif
@endsection

@extends('layouts.app')
@section('title', 'Persetujuan')
@section('subtitle', 'Antrian review, persetujuan & penetapan sesuai peran Anda')
@section('heading', 'Persetujuan')

@section('content')
<div class="grid xl:grid-cols-[1fr_380px] gap-5">
    <div class="card reveal" style="--i:1">
        <div class="flex items-center gap-2 mb-4"><h3 class="card-title">Menunggu Tindakan</h3><span class="chip" data-testid="approval-pending-count">{{ $pending->count() }}</span></div>
        <div class="space-y-3">
            @forelse ($pending as $p)
                <div class="rounded-[22px] border border-slate-200/80 p-4 flex flex-wrap items-center gap-4 hover:border-brand-200 transition-colors" data-testid="approval-item-{{ $p['type'] }}-{{ $p['model']->id }}">
                    <span class="w-11 h-11 rounded-2xl bg-brand-50 text-brand-600 grid place-items-center"><i data-lucide="stamp" class="w-5 h-5"></i></span>
                    <div class="flex-1 min-w-[220px]">
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">{{ \App\Support\Workflow::LABELS[$p['type']] }}</p>
                        <a href="{{ $p['model']->workflowUrl() }}" class="font-semibold hover:text-brand-600">{{ $p['model']->workflowTitle() }}</a>
                        <p class="text-[11px] text-slate-400 mt-0.5">Diperbarui {{ $p['model']->updated_at->diffForHumans() }} · oleh {{ $p['model']->creator?->name ?? '-' }}</p>
                    </div>
                    <x-badge :s="$p['model']->status" />
                    <x-workflow :model="$p['model']" :type="$p['type']" :actions="$p['actions']" />
                </div>
            @empty
                <x-empty icon="check" title="Semua beres" text="Tidak ada data yang menunggu tindakan Anda." />
            @endforelse
        </div>
    </div>
    <div class="card reveal" style="--i:2">
        <h3 class="card-title mb-4">Riwayat Tindakan Saya</h3>
        <div class="space-y-3">
            @forelse ($history as $h)
                <div class="flex gap-3 text-sm">
                    <x-badge :s="$h->to_status" />
                    <div class="min-w-0"><p class="truncate">{{ \App\Support\Workflow::LABELS[$h->approvable_type] ?? $h->approvable_type }} · {{ $h->approvable?->workflowTitle() }}</p><p class="text-[11px] text-slate-400">{{ $h->created_at->diffForHumans() }}</p></div>
                </div>
            @empty
                <p class="text-xs text-slate-400">Belum ada riwayat.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@props(['logs'])
<div class="space-y-0" data-testid="approval-timeline">
    @forelse ($logs as $l)
        <div class="flex gap-3 relative pb-5 last:pb-0">
            @if (! $loop->last)<span class="absolute left-[15px] top-8 bottom-0 w-px bg-slate-200"></span>@endif
            <span class="w-8 h-8 rounded-full grid place-items-center shrink-0 {{ \App\Support\Esakip::badge($l->to_status) }} ring-1"><i data-lucide="{{ \App\Support\Workflow::ACTIONS[$l->action]['icon'] ?? 'circle' }}" class="w-3.5 h-3.5"></i></span>
            <div class="min-w-0">
                <p class="text-sm"><span class="font-semibold">{{ $l->user?->name ?? 'Sistem' }}</span> <span class="text-slate-500">· {{ \App\Support\Workflow::ACTIONS[$l->action]['label'] ?? $l->action }}</span></p>
                <p class="text-[11px] text-slate-400">{{ config('esakip.roles')[$l->role] ?? $l->role }} · {{ $l->created_at->translatedFormat('d M Y H:i') }} · {{ \App\Support\Esakip::label($l->from_status) }} → {{ \App\Support\Esakip::label($l->to_status) }}</p>
                @if ($l->note)<p class="text-xs text-slate-600 mt-1.5 bg-slate-50 rounded-xl px-3 py-2">“{{ $l->note }}”</p>@endif
            </div>
        </div>
    @empty
        <p class="text-xs text-slate-400">Belum ada riwayat persetujuan.</p>
    @endforelse
</div>

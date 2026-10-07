@php
    $payload = [
        'name' => $ind->name, 'code' => $ind->code, 'type' => $ind->type, 'satuan_id' => (string) $ind->satuan_id, 'definition' => $ind->definition,
        'formula' => $ind->formula, 'baseline' => $ind->baseline, 'data_source' => $ind->data_source,
        'targets' => $ind->targets->mapWithKeys(fn ($t) => [$t->year => (float) $t->value]),
    ];
    $lt = isset($latest) ? ($latest[$ind->id] ?? null) : null;
@endphp
<div class="group rounded-2xl bg-slate-50/80 hover:bg-white border border-transparent hover:border-slate-200 p-3.5 transition-colors" data-testid="indicator-{{ $ind->id }}">
    <div class="flex items-start gap-3">
        <span class="w-8 h-8 rounded-xl bg-white text-brand-600 grid place-items-center shrink-0 shadow-sm"><i data-lucide="target" class="w-4 h-4"></i></span>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold leading-snug">{{ $ind->name }} @if ($ind->code)<span class="text-[11px] font-medium text-slate-400 ml-1">{{ $ind->code }}</span>@endif</p>
            <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                <span class="chip">{{ $ind->satuan?->name }}</span>
                <span class="chip">{{ ucfirst($ind->type) }}</span>
                <span class="chip {{ $ind->formula === 'negatif' ? '!bg-amber-50 !text-amber-700' : '' }}"><i data-lucide="{{ $ind->formula === 'negatif' ? 'trending-down' : 'trending-up' }}" class="w-3 h-3"></i>{{ ucfirst($ind->formula) }}</span>
                @if ($ind->baseline !== null)<span class="chip">Baseline {{ \App\Support\Esakip::num($ind->baseline) }}</span>@endif
            </div>
            <div class="flex flex-wrap gap-1.5 mt-2">
                @foreach ($ind->targets as $t)
                    <span class="inline-flex items-center rounded-lg bg-white border border-slate-200 text-[11px] overflow-hidden"><span class="px-1.5 py-0.5 bg-ink text-white font-semibold">{{ $t->year }}</span><span class="px-1.5 py-0.5 num font-semibold">{{ \App\Support\Esakip::num($t->value) }}</span></span>
                @endforeach
            </div>
            <p class="text-[11px] text-slate-400 mt-2 line-clamp-2" title="{{ $ind->definition }}"><b class="text-slate-500">Definisi:</b> {{ $ind->definition }}</p>
            @if ($lt)
                <div class="mt-2 max-w-xs"><x-capbar :v="$lt->capaian" sm /></div>
                <p class="text-[10px] text-slate-400 mt-0.5">Realisasi {{ $lt->periode }} {{ $lt->year }}: {{ \App\Support\Esakip::num($lt->realisasi) }} dari target {{ \App\Support\Esakip::num($lt->target) }}</p>
            @endif
        </div>
        @if ($editable ?? false)
            <div class="flex gap-1 opacity-60 group-hover:opacity-100 transition-opacity">
                <button type="button" class="icon-btn-sm" @click="$dispatch('open-drawer', { name: 'indikator', data: @js($payload), action: @js(route('indikator.update', $ind)), method: 'PUT', title: 'Ubah Indikator' })" data-testid="indicator-edit-{{ $ind->id }}"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></button>
                <form method="POST" action="{{ route('indikator.destroy', $ind) }}" data-confirm="Hapus indikator ini beserta targetnya?">@csrf @method('DELETE')<button class="icon-btn-sm" data-testid="indicator-delete-{{ $ind->id }}"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button></form>
            </div>
        @endif
    </div>
</div>

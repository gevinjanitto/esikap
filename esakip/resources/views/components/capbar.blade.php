@props(['v' => 0, 'sm' => false, 'label' => true])
@php
    $v = (float) $v;
    $st = \App\Support\Esakip::statusCapaian($v);
    $color = ['tercapai' => 'bg-emerald-500', 'perlu_perhatian' => 'bg-amber-400', 'tidak_tercapai' => 'bg-brand-600'][$st];
@endphp
<div class="flex items-center gap-3 min-w-[140px]">
    <div class="bar {{ $sm ? 'sm' : '' }} flex-1"><div class="bar-fill {{ $color }}" style="width: {{ min($v, 100) }}%"></div></div>
    @if ($label)<span class="text-xs font-semibold num w-14 text-right {{ $st === 'tidak_tercapai' ? 'text-brand-600' : 'text-slate-700' }}">{{ \App\Support\Esakip::num($v, 1) }}%</span>@endif
</div>

@props(['icon' => 'inbox', 'title' => 'Belum ada data', 'text' => null])
<div class="py-14 text-center" data-testid="empty-state">
    <span class="w-16 h-16 mx-auto rounded-3xl bg-brand-50 text-brand-600 grid place-items-center mb-4 float-a"><i data-lucide="{{ $icon }}" class="w-7 h-7"></i></span>
    <p class="font-semibold">{{ $title }}</p>
    @if ($text)<p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">{{ $text }}</p>@endif
    {{ $slot }}
</div>

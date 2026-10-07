@props(['name', 'title', 'sub' => null, 'action' => '', 'method' => 'POST', 'defaults' => [], 'files' => false, 'wide' => false, 'submit' => 'Simpan'])
<div x-data="drawer(@js($name), @js($action), @js($method), @js((object) $defaults))" x-show="open" x-cloak @keydown.escape.window="open = false" class="fixed inset-0 z-[90]" data-testid="drawer-{{ $name }}">
    <div class="drawer-backdrop" x-show="open" x-transition.opacity.duration.400ms @click="open = false"></div>
    <div class="drawer-panel {{ $wide ? 'sm:max-w-2xl' : 'sm:max-w-lg' }}" x-show="open"
         x-transition:enter="drawer-enter" x-transition:enter-start="drawer-enter-start" x-transition:enter-end="drawer-enter-end"
         x-transition:leave="drawer-leave" x-transition:leave-start="drawer-enter-end" x-transition:leave-end="drawer-enter-start">
        <div class="flex items-start gap-3 px-6 sm:px-8 pt-7 pb-5 border-b border-slate-100">
            <div class="flex-1">
                <p class="eyebrow mb-1.5">{{ $sub ?? 'Formulir' }}</p>
                <h3 class="text-2xl font-semibold tracking-tight" x-text="title || @js($title)"></h3>
            </div>
            <button type="button" class="icon-btn" @click="open = false" data-testid="drawer-{{ $name }}-close"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <form :action="action" method="POST" @if ($files) enctype="multipart/form-data" @endif class="flex-1 flex flex-col min-h-0">
            @csrf
            <input type="hidden" name="_method" :value="method">
            <template x-if="open">
                <div class="flex-1 overflow-y-auto thin-scroll px-6 sm:px-8 py-6 space-y-4 stagger">
                    {{ $slot }}
                </div>
            </template>
            <div class="px-6 sm:px-8 py-5 border-t border-slate-100 flex justify-end gap-2 bg-slate-50/60">
                <button type="button" class="btn-ghost" @click="open = false">Batal</button>
                <button type="submit" class="btn-primary" data-testid="drawer-{{ $name }}-submit"><i data-lucide="check" class="w-4 h-4"></i>{{ $submit }}</button>
            </div>
        </form>
    </div>
</div>

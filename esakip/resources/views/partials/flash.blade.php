@php $msgs = array_filter(['ok' => session('ok'), 'err' => session('err')]); @endphp
@if ($msgs || $errors->any())
    <div class="fixed top-5 right-5 z-[150] flex flex-col gap-2 w-[min(380px,calc(100vw-2.5rem))]" data-testid="flash-container">
        @foreach ($msgs as $type => $msg)
            <div x-data="{ s: true }" x-init="setTimeout(() => s = false, 5500)" x-show="s" x-transition.opacity.duration.400ms class="toast card !p-4 flex gap-3 items-start !rounded-2xl" data-testid="flash-{{ $type }}">
                <span class="w-8 h-8 rounded-full grid place-items-center shrink-0 {{ $type === 'ok' ? 'bg-emerald-50 text-emerald-600' : 'bg-brand-50 text-brand-600' }}"><i data-lucide="{{ $type === 'ok' ? 'check' : 'alert-triangle' }}" class="w-4 h-4"></i></span>
                <p class="text-sm text-slate-700 pt-1.5 flex-1">{{ $msg }}</p>
                <button @click="s = false" class="text-slate-400 hover:text-ink"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
        @endforeach
        @if ($errors->any())
            <div x-data="{ s: true }" x-init="setTimeout(() => s = false, 8000)" x-show="s" x-transition.opacity class="toast card !p-4 !rounded-2xl" data-testid="flash-validation">
                <div class="flex gap-3">
                    <span class="w-8 h-8 rounded-full grid place-items-center shrink-0 bg-brand-50 text-brand-600"><i data-lucide="alert-triangle" class="w-4 h-4"></i></span>
                    <ul class="text-sm text-slate-700 pt-1 space-y-0.5 flex-1">
                        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                    <button @click="s = false" class="text-slate-400 hover:text-ink self-start"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>
            </div>
        @endif
    </div>
@endif

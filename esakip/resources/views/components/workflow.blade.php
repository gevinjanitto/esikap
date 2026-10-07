@props(['model', 'type', 'actions' => []])
@php $A = \App\Support\Workflow::ACTIONS; @endphp
<div x-data="{ act: null, label: '', need: false }" class="flex flex-wrap items-center gap-2" data-testid="workflow-{{ $type }}-{{ $model->id }}">
    @foreach ($actions as $a)
        @php
            $tone = ['primary' => 'btn-primary', 'success' => 'btn-dark', 'dark' => 'btn-dark', 'warn' => 'btn-ghost', 'danger' => 'btn-ghost', 'ghost' => 'btn-ghost'][$A[$a]['tone']];
        @endphp
        <button type="button" class="{{ $tone }} btn-sm" @click="act = @js($a); label = @js($A[$a]['label']); need = @js(in_array($a, ['revise', 'reject', 'unlock']))" data-testid="wf-{{ $a }}-{{ $type }}-{{ $model->id }}">
            <i data-lucide="{{ $A[$a]['icon'] }}" class="w-3.5 h-3.5"></i>{{ $A[$a]['label'] }}
        </button>
    @endforeach

    <template x-teleport="body">
        <div x-show="act" x-cloak class="fixed inset-0 z-[120] grid place-items-center p-4">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md" @click="act = null" x-show="act" x-transition.opacity></div>
            <form x-show="act" method="POST" :action="@js(route('workflow', [$type, $model->id, '__A__'])).replace('__A__', act)" class="relative card w-full max-w-md !p-6 modal-pop">
                @csrf
                <p class="eyebrow mb-1">{{ \App\Support\Workflow::LABELS[$type] }}</p>
                <h3 class="text-xl font-semibold mb-1" x-text="label"></h3>
                <p class="text-sm text-slate-500 mb-4">{{ method_exists($model, 'workflowTitle') ? $model->workflowTitle() : '' }}</p>
                <label class="lbl">Catatan <span x-show="need" class="text-brand-600">*wajib</span></label>
                <textarea name="note" class="inp" :required="need" placeholder="Tulis catatan / alasan..." data-testid="wf-note-input"></textarea>
                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" class="btn-ghost btn-sm" @click="act = null">Batal</button>
                    <button class="btn-primary btn-sm" data-testid="wf-confirm-button"><i data-lucide="check" class="w-4 h-4"></i>Konfirmasi</button>
                </div>
            </form>
        </div>
    </template>
</div>

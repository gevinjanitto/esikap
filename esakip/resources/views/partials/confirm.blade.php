<div x-data="{ open: false, msg: '', form: null }" @ask-confirm.window="open = true; msg = $event.detail.msg; form = $event.detail.form" x-show="open" x-cloak class="fixed inset-0 z-[160] grid place-items-center p-4" data-testid="confirm-modal">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md" @click="open = false" x-show="open" x-transition.opacity></div>
    <div class="relative card w-full max-w-sm !p-6 modal-pop" x-show="open">
        <span class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 grid place-items-center mb-4"><i data-lucide="alert-octagon" class="w-6 h-6"></i></span>
        <h3 class="text-lg font-semibold mb-1">Konfirmasi</h3>
        <p class="text-sm text-slate-500 mb-6" x-text="msg"></p>
        <div class="flex gap-2 justify-end">
            <button class="btn-ghost btn-sm" @click="open = false" data-testid="confirm-cancel-button">Batal</button>
            <button class="btn-primary btn-sm" @click="form.dataset.confirmed = 1; form.submit(); open = false" data-testid="confirm-ok-button">Ya, lanjutkan</button>
        </div>
    </div>
</div>

@props(['items', 'type' => null, 'id' => null, 'can' => false])
<div class="flex flex-wrap items-center gap-1.5" x-data="{ up: false }">
    @foreach ($items as $e)
        <span class="chip group">
            <a href="{{ route('evidence.download', $e) }}" target="_blank" data-no-transition class="inline-flex items-center gap-1 hover:text-brand-600" data-testid="evidence-{{ $e->id }}">
                <i data-lucide="{{ $e->link ? 'link' : 'paperclip' }}" class="w-3 h-3"></i>{{ \Illuminate\Support\Str::limit($e->original_name, 22) }}
            </a>
            @if ($can)
                <form method="POST" action="{{ route('evidence.destroy', $e) }}" data-confirm="Hapus bukti dukung ini?" class="inline">@csrf @method('DELETE')<button class="opacity-50 hover:opacity-100 hover:text-brand-600"><i data-lucide="x" class="w-3 h-3"></i></button></form>
            @endif
        </span>
    @endforeach
    @if ($can && $type)
        <button type="button" class="icon-btn-sm !w-7 !h-7" @click="up = !up" title="Unggah bukti" data-testid="evidence-upload-toggle-{{ $type }}-{{ $id }}"><i data-lucide="upload" class="w-3.5 h-3.5"></i></button>
        <form x-show="up" x-cloak x-transition method="POST" action="{{ route('evidence.store', [$type, $id]) }}" enctype="multipart/form-data" class="w-full flex flex-wrap gap-2 mt-2 p-2 bg-slate-50 rounded-2xl">
            @csrf
            <input type="file" name="files[]" multiple class="text-xs flex-1 min-w-[160px]" data-testid="evidence-file-input-{{ $type }}-{{ $id }}">
            <input type="url" name="link" class="inp !h-8 !text-xs flex-1 min-w-[140px]" placeholder="atau tautan https://">
            <button class="btn-primary btn-xs" data-testid="evidence-upload-submit-{{ $type }}-{{ $id }}">Unggah</button>
        </form>
    @elseif ($items->isEmpty())
        <span class="text-[11px] text-slate-400">Belum ada bukti</span>
    @endif
</div>

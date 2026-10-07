@props(['type', 'item', 'parentId', 'childType' => null, 'childLabel' => null, 'indicatorOwner' => null, 'editable' => false])
{{-- action buttons for a tree node --}}
@if ($editable)
    <div class="flex items-center gap-1 shrink-0">
        @if ($indicatorOwner)
            <button type="button" class="btn-ghost btn-xs" @click="$dispatch('open-drawer', { name: 'indikator', data: { owner_type: @js($indicatorOwner), owner_id: {{ $item->id }}, context: @js(\Illuminate\Support\Str::limit($item->name, 90)) } })" data-testid="add-indicator-{{ $type }}-{{ $item->id }}"><i data-lucide="target" class="w-3 h-3"></i>Indikator</button>
        @endif
        @if ($childType)
            <button type="button" class="btn-ghost btn-xs" @click="$dispatch('open-drawer', { name: 'tree', data: { parent_id: {{ $item->id }}, type: @js($childType), context: @js(\Illuminate\Support\Str::limit($item->name, 90)) }, action: @js(route('tree.store', $childType)), title: @js('Tambah '.$childLabel) })" data-testid="add-{{ $childType }}-to-{{ $item->id }}"><i data-lucide="plus" class="w-3 h-3"></i>{{ $childLabel }}</button>
        @endif
        <button type="button" class="icon-btn-sm !w-7 !h-7" @click="$dispatch('open-drawer', { name: 'tree', data: { type: @js($type), code: @js($item->code), name: @js($item->name), sasaran_daerah_id: @js((string) ($item->sasaran_daerah_id ?? '')) }, action: @js(route('tree.update', [$type, $item->id])), method: 'PUT', title: 'Ubah Data' })" data-testid="edit-{{ $type }}-{{ $item->id }}"><i data-lucide="pencil" class="w-3 h-3"></i></button>
        <form method="POST" action="{{ route('tree.destroy', [$type, $item->id]) }}" data-confirm="Hapus data ini beserta turunannya?">@csrf @method('DELETE')<button class="icon-btn-sm !w-7 !h-7" data-testid="delete-{{ $type }}-{{ $item->id }}"><i data-lucide="trash-2" class="w-3 h-3"></i></button></form>
    </div>
@endif

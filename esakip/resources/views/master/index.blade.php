@extends('layouts.app')
@section('title', $cfg['title'])
@section('subtitle', 'Master Data · '.$cfg['sub'])
@section('heading', $cfg['title'])
@php $defaults = collect($cfg['fields'])->map(fn ($f, $k) => $k === 'is_active' ? '1' : ($k === 'status' ? 'aktif' : ''))->all(); @endphp

@section('actions')
    <form class="relative w-full sm:w-64"><i data-lucide="search" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i><input name="q" value="{{ request('q') }}" class="inp !rounded-full !h-12 pl-11" placeholder="Cari nama..." data-testid="master-search-input"></form>
    <button class="btn-primary" @click="$dispatch('open-drawer', { name: 'master', title: 'Tambah {{ $cfg['title'] }}' })" data-testid="master-create-button"><i data-lucide="plus" class="w-4 h-4"></i>Tambah</button>
@endsection

@section('content')
<div class="card reveal" style="--i:1">
    <div class="overflow-x-auto thin-scroll">
        <table class="tbl" data-testid="master-table">
            <thead><tr>@foreach ($cfg['fields'] as $f)<th>{{ $f[0] }}</th>@endforeach<th></th></tr></thead>
            <tbody>
            @forelse ($rows as $r)
                <tr data-testid="master-row-{{ $r->id }}">
                    @foreach ($cfg['fields'] as $k => $f)
                        <td class="{{ $loop->first ? 'font-semibold' : '' }}">
                            @if ($k === 'is_active')<x-badge :s="$r->is_active ? 'aktif' : 'nonaktif'" />
                            @elseif ($k === 'status')<x-badge :s="$r->status" />
                            @elseif ($k === 'opd_id'){{ $r->opd?->name }}
                            @else{{ $r->$k }}@endif
                        </td>
                    @endforeach
                    <td class="text-right whitespace-nowrap">
                        <button class="icon-btn-sm" @click="$dispatch('open-drawer', { name: 'master', data: @js(collect($cfg['fields'])->keys()->mapWithKeys(fn ($k) => [$k => is_bool($r->$k) ? ($r->$k ? '1' : '0') : (string) $r->$k])), action: @js(route('master.update', [$res, $r->id])), method: 'PUT', title: 'Ubah {{ $cfg['title'] }}' })" data-testid="master-edit-{{ $r->id }}"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></button>
                        <form method="POST" action="{{ route('master.destroy', [$res, $r->id]) }}" class="inline" data-confirm="Arsipkan / hapus data ini?">@csrf @method('DELETE')<button class="icon-btn-sm" data-testid="master-delete-{{ $r->id }}"><i data-lucide="archive" class="w-3.5 h-3.5"></i></button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ count($cfg['fields']) + 1 }}"><x-empty icon="database" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $rows->links('partials.pagination') }}
</div>

<x-drawer name="master" :title="$cfg['title']" sub="Master Data" :action="route('master.store', $res)" :defaults="$defaults">
    @foreach ($cfg['fields'] as $k => $f)
        <div>
            <label class="lbl">{{ $f[0] }}{{ str_contains($f[2], 'required') ? ' *' : '' }}</label>
            @if ($f[1] === 'select')
                <select name="{{ $k }}" x-model="form.{{ $k }}" class="inp" data-testid="master-field-{{ $k }}">@if ($k === 'opd_id')<option value="">Pilih</option>@endif @foreach ($f[3] as $ov => $ol)<option value="{{ $ov }}">{{ $ol }}</option>@endforeach</select>
            @else
                <input type="{{ $f[1] }}" name="{{ $k }}" x-model="form.{{ $k }}" class="inp" @if (str_contains($f[2], 'required')) required @endif data-testid="master-field-{{ $k }}">
            @endif
        </div>
    @endforeach
</x-drawer>
@endsection

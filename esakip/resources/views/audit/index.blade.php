@extends('layouts.app')
@section('title', 'Audit Trail')
@section('subtitle', 'Siapa melakukan apa, kapan, beserta data sebelum & sesudah')
@section('heading', 'Audit Trail')

@section('content')
<div class="card reveal" style="--i:1">
    <form class="flex flex-wrap gap-2 mb-4">
        <select name="user" class="inp !w-52 !h-10" data-testid="audit-user-filter"><option value="">Semua pengguna</option>@foreach ($users as $id => $n)<option value="{{ $id }}" @selected(request('user') == $id)>{{ $n }}</option>@endforeach</select>
        <select name="action" class="inp !w-40 !h-10"><option value="">Semua aksi</option>@foreach ($actions as $a)<option @selected(request('action') === $a)>{{ $a }}</option>@endforeach</select>
        <select name="model" class="inp !w-44 !h-10"><option value="">Semua objek</option>@foreach ($models as $m)<option @selected(request('model') === $m)>{{ $m }}</option>@endforeach</select>
        <input type="date" name="dari" value="{{ request('dari') }}" class="inp !w-40 !h-10"><input type="date" name="sampai" value="{{ request('sampai') }}" class="inp !w-40 !h-10">
        <button class="btn-dark btn-sm !h-10" data-testid="audit-filter-button"><i data-lucide="filter" class="w-4 h-4"></i>Filter</button>
    </form>
    <div class="overflow-x-auto thin-scroll">
        <table class="tbl" data-testid="audit-table">
            <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Objek</th><th>Keterangan</th><th>IP</th><th></th></tr></thead>
            @forelse ($rows as $a)
                <tbody x-data="{ o: false }">
                    <tr data-testid="audit-row-{{ $a->id }}">
                        <td class="text-xs whitespace-nowrap">{{ $a->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="text-sm font-medium">{{ $a->user?->name ?? 'Sistem' }}</td>
                        <td><span class="badge {{ ['create' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'update' => 'bg-sky-50 text-sky-700 ring-sky-200', 'delete' => 'bg-rose-50 text-rose-700 ring-rose-200'][$a->action] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">{{ $a->action }}</span></td>
                        <td class="text-xs">{{ $a->model_type }} @if ($a->model_id)#{{ $a->model_id }}@endif</td>
                        <td class="text-xs text-slate-500 max-w-[320px] truncate">{{ $a->label }}</td>
                        <td class="text-[11px] text-slate-400">{{ $a->ip }}</td>
                        <td>@if ($a->before || $a->after)<button class="icon-btn-sm" @click="o = !o" data-testid="audit-expand-{{ $a->id }}"><i data-lucide="chevron-down" class="w-4 h-4"></i></button>@endif</td>
                    </tr>
                    <tr x-show="o" x-cloak><td colspan="7" class="!bg-slate-50">
                        <div class="grid md:grid-cols-2 gap-3 text-[11px]">
                            <div><p class="font-semibold text-slate-500 mb-1">SEBELUM</p><pre class="whitespace-pre-wrap bg-white rounded-xl p-3 border border-slate-200">{{ json_encode($a->before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></div>
                            <div><p class="font-semibold text-slate-500 mb-1">SESUDAH</p><pre class="whitespace-pre-wrap bg-white rounded-xl p-3 border border-slate-200">{{ json_encode($a->after, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></div>
                        </div>
                    </td></tr>
                </tbody>
            @empty
                <tbody><tr><td colspan="7"><x-empty icon="shield-check" title="Belum ada log" /></td></tr></tbody>
            @endforelse
        </table>
    </div>
    {{ $rows->links('partials.pagination') }}
</div>
@endsection

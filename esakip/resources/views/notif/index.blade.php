@extends('layouts.app')
@section('title', 'Notifikasi')
@section('subtitle', 'Pemberitahuan alur kerja untuk Anda')
@section('heading', 'Notifikasi')
@section('actions')
    <form method="POST" action="{{ route('notif.readAll') }}">@csrf<button class="btn-ghost" data-testid="notif-read-all-button"><i data-lucide="check" class="w-4 h-4"></i>Tandai semua dibaca</button></form>
@endsection
@section('content')
<div class="card reveal max-w-3xl" style="--i:1">
    @forelse ($rows as $n)
        <a href="{{ route('notif.open', $n) }}" class="flex gap-4 p-4 rounded-2xl hover:bg-slate-50 transition-colors" data-testid="notif-item-{{ $n->id }}">
            <span class="w-10 h-10 rounded-2xl grid place-items-center shrink-0 {{ $n->read_at ? 'bg-slate-100 text-slate-400' : 'bg-brand-600 text-white' }}"><i data-lucide="bell" class="w-4 h-4"></i></span>
            <div class="flex-1"><p class="font-semibold text-sm">{{ $n->title }}</p><p class="text-sm text-slate-500">{{ $n->message }}</p><p class="text-[11px] text-slate-400 mt-1">{{ $n->created_at->diffForHumans() }}</p></div>
            @unless ($n->read_at)<span class="w-2 h-2 rounded-full bg-brand-600 mt-2"></span>@endunless
        </a>
    @empty
        <x-empty icon="bell" title="Tidak ada notifikasi" />
    @endforelse
    {{ $rows->links('partials.pagination') }}
</div>
@endsection

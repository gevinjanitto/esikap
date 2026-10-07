@extends('layouts.app')
@section('title', 'Pengguna')
@section('subtitle', 'Master Data · Pengguna & hak akses berbasis peran (RBAC)')
@section('heading', 'Pengguna')
@php $roles = config('esakip.roles'); @endphp

@section('actions')
    <form class="flex gap-2">
        <select name="role" onchange="this.form.submit()" class="inp !rounded-full !w-52 !h-12" data-testid="users-role-filter"><option value="">Semua peran</option>@foreach ($roles as $k => $l)<option value="{{ $k }}" @selected(request('role') === $k)>{{ $l }}</option>@endforeach</select>
        <input name="q" value="{{ request('q') }}" class="inp !rounded-full !h-12 !w-56" placeholder="Cari nama/email...">
    </form>
    <button class="btn-primary" @click="$dispatch('open-drawer', { name: 'user', title: 'Tambah Pengguna' })" data-testid="users-create-button"><i data-lucide="user-plus" class="w-4 h-4"></i>Tambah</button>
@endsection

@section('content')
<div class="card reveal" style="--i:1">
    <div class="overflow-x-auto thin-scroll">
        <table class="tbl" data-testid="users-table">
            <thead><tr><th>Pengguna</th><th>Username</th><th>Peran</th><th>OPD</th><th>Jabatan</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @foreach ($rows as $u)
                <tr data-testid="user-row-{{ $u->id }}">
                    <td><div class="flex items-center gap-3"><span class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-white grid place-items-center text-xs font-bold">{{ $u->initials }}</span><div><p class="font-semibold">{{ $u->name }}</p><p class="text-[11px] text-slate-400">{{ $u->email }}</p></div></div></td>
                    <td class="text-xs">{{ $u->username }}</td>
                    <td><span class="chip">{{ $u->role_label }}</span></td>
                    <td class="text-xs">{{ $u->opd?->name ?? '—' }}</td>
                    <td class="text-xs text-slate-500">{{ $u->jabatan }}</td>
                    <td><x-badge :s="$u->is_active ? 'aktif' : 'nonaktif'" /></td>
                    <td><button class="icon-btn-sm" @click="$dispatch('open-drawer', { name: 'user', data: @js(['name' => $u->name, 'email' => $u->email, 'username' => $u->username, 'role' => $u->role, 'opd_id' => (string) $u->opd_id, 'jabatan' => $u->jabatan, 'nip' => $u->nip, 'is_active' => $u->is_active ? '1' : '0']), action: @js(route('users.update', $u)), method: 'PUT', title: 'Ubah Pengguna' })" data-testid="user-edit-{{ $u->id }}"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></button></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $rows->links('partials.pagination') }}
</div>

<x-drawer name="user" title="Pengguna" sub="Master Data" :action="route('users.store')" :defaults="['name' => '', 'email' => '', 'username' => '', 'role' => 'operator_opd', 'opd_id' => '', 'jabatan' => '', 'nip' => '', 'is_active' => '1']">
    <div><label class="lbl">Nama *</label><input name="name" x-model="form.name" class="inp" required data-testid="user-name-input"></div>
    <div class="grid grid-cols-2 gap-3">
        <div><label class="lbl">Email *</label><input type="email" name="email" x-model="form.email" class="inp" required data-testid="user-email-input"></div>
        <div><label class="lbl">Username *</label><input name="username" x-model="form.username" class="inp" required data-testid="user-username-input"></div>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div><label class="lbl">Peran *</label><select name="role" x-model="form.role" class="inp" data-testid="user-role-select">@foreach ($roles as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
        <div><label class="lbl">OPD <span x-show="['operator_opd','kepala_opd'].includes(form.role)" class="text-brand-600">*</span></label><select name="opd_id" x-model="form.opd_id" class="inp" data-testid="user-opd-select"><option value="">—</option>@foreach ($opds as $id => $n)<option value="{{ $id }}">{{ $n }}</option>@endforeach</select></div>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div><label class="lbl">Jabatan</label><input name="jabatan" x-model="form.jabatan" class="inp"></div>
        <div><label class="lbl">NIP</label><input name="nip" x-model="form.nip" class="inp"></div>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div><label class="lbl">Kata Sandi <span class="font-normal text-slate-400">(min 8, kosongkan bila tidak diubah)</span></label><input type="password" name="password" class="inp" autocomplete="new-password" data-testid="user-password-input"></div>
        <div><label class="lbl">Status</label><select name="is_active" x-model="form.is_active" class="inp"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
    </div>
</x-drawer>
@endsection

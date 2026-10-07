<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $r)
    {
        $q = User::with('opd')->orderBy('role')->orderBy('name');
        if ($s = $r->get('q')) {
            $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%")->orWhere('username', 'like', "%$s%"));
        }
        if ($role = $r->get('role')) {
            $q->where('role', $role);
        }
        $rows = $q->paginate(15)->withQueryString();
        $opds = Opd::orderBy('name')->pluck('name', 'id');

        return view('master.users', compact('rows', 'opds'));
    }

    private function rules(?User $u = null): array
    {
        return [
            'name' => 'required|string|max:120',
            'email' => ['required', 'email', Rule::unique('users')->ignore($u?->id)],
            'username' => ['required', 'alpha_dash', 'max:60', Rule::unique('users')->ignore($u?->id)],
            'role' => ['required', Rule::in(array_keys(config('esakip.roles')))],
            'opd_id' => 'nullable|required_if:role,operator_opd,kepala_opd|exists:opds,id',
            'jabatan' => 'nullable|string|max:150',
            'nip' => 'nullable|string|max:30',
            'is_active' => 'required|boolean',
            'password' => $u ? 'nullable|min:8' : 'required|min:8',
        ];
    }

    public function store(Request $r)
    {
        $data = $r->validate($this->rules());
        $data['email'] = strtolower($data['email']);
        $data['username'] = strtolower($data['username']);
        User::create($data);

        return back()->with('ok', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $r, User $user)
    {
        $data = $r->validate($this->rules($user));
        if (empty($data['password'])) {
            unset($data['password']);
        }
        if ($user->id === auth()->id() && ! $data['is_active']) {
            return back()->with('err', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }
        $user->update($data);

        return back()->with('ok', 'Pengguna berhasil diperbarui.');
    }
}

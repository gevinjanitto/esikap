<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Auditable, Notifiable;

    protected $guarded = ['id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'is_active' => 'boolean'];
    }

    public function opd()
    {
        return $this->belongsTo(Opd::class)->withTrashed();
    }

    public function notifs()
    {
        return $this->hasMany(Notif::class)->latest('id');
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function isOpdScoped(): bool
    {
        return in_array($this->role, ['operator_opd', 'kepala_opd']);
    }

    public function getRoleLabelAttribute(): string
    {
        return config('esakip.roles')[$this->role] ?? $this->role;
    }

    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', $this->name))->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('');
    }
}

<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// role e is_blocked NO son mass-assignable a propósito (ver Admin\UserController::store()/
// toggleBlock(), que los asignan por propiedad directa) — así ningún mass-assignment futuro
// (p. ej. $request->all()) podría autopromover a un usuario a admin o desbloquearse.
#[Fillable([
    'name', 'first_name', 'last_name', 'email', 'username', 'password', 'phone', 'phone_country_code',
    'company', 'address_line_1', 'address_line_2', 'city', 'state', 'postal_code', 'country',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_blocked' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * "Super Admin" tiene acceso total al panel; cualquier otro admin (`admin_role`
     * distinto, ej. `support`) queda sin Configuración ni Paquetes/Categorías/Métodos de
     * pago/Cupones — ver App\Http\Middleware\EnsureUserIsSuperAdmin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->isAdmin() && $this->admin_role === 'super_admin';
    }

    public function isBlocked(): bool
    {
        return ! $this->isAdmin() && $this->is_blocked;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function lines()
    {
        return $this->hasMany(Line::class);
    }

    public function activeLine()
    {
        return $this->hasOne(Line::class)->where('status', 'active')->latestOfMany('expires_at');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}

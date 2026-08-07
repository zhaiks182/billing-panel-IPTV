<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin
        {username : Usuario de acceso al panel admin (sin @, no es un correo)}
        {password : Contraseña del usuario administrador}
        {--name=Administrador : Nombre para mostrar}';

    protected $description = 'Crea o actualiza un usuario con rol admin (usado por install.sh)';

    public function handle(): int
    {
        $username = $this->argument('username');
        $password = $this->argument('password');
        $name = $this->option('name');

        $validator = Validator::make(
            ['username' => $username, 'password' => $password],
            ['username' => ['required', 'string', 'regex:/^[a-zA-Z0-9_.-]+$/'], 'password' => ['required', 'string', 'min:8']]
        );

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        // El panel admin ya no se identifica por correo (ver App\Http\Requests\Admin\LoginRequest),
        // pero la columna `email` sigue siendo NOT NULL/unica a nivel de esquema — se rellena con un
        // valor interno no entregable (dominio .local) que nunca se usa para enviar nada.
        $user = User::updateOrCreate(
            ['username' => $username],
            [
                'name' => $name,
                'email' => "{$username}@admin.local",
                'password' => Hash::make($password),
                'role' => 'admin',
            ]
        );

        // 'email_verified_at' no está en el mass-assignment permitido del modelo (a propósito,
        // ver App\Models\User) — se marca aparte con el mismo método que usa el resto de la app.
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $this->info("Usuario admin listo: {$user->username} (id {$user->id}).");

        return self::SUCCESS;
    }
}

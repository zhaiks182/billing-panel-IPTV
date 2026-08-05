<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin
        {email : Correo del usuario administrador}
        {password : Contraseña del usuario administrador}
        {--name=Administrador : Nombre para mostrar}';

    protected $description = 'Crea o actualiza un usuario con rol admin (usado por install.sh)';

    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $name = $this->option('name');

        $validator = Validator::make(
            ['email' => $email, 'password' => $password],
            ['email' => ['required', 'email'], 'password' => ['required', 'string', 'min:8']]
        );

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $this->info("Usuario admin listo: {$user->email} (id {$user->id}).");

        return self::SUCCESS;
    }
}

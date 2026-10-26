<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateApiUser extends Command
{
    protected $signature = 'app:create-api-user';
    protected $description = 'Crea un usuario para probar las rutas protegidas de la API';

    public function handle(): int
    {
        $name = trim((string) $this->ask('Nombre del usuario'));
        $email = strtolower(trim((string) $this->ask('Correo electrónico')));
        $password = (string) $this->secret('Contraseña (mínimo 8 caracteres)');
        $confirmation = (string) $this->secret('Repetir contraseña');

        $validator = Validator::make(
            compact('name', 'email', 'password'),
            [
                'name' => ['required', 'string', 'max:150'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        if ($password !== $confirmation) {
            $this->error('Las contraseñas no coinciden.');
            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info('Usuario creado. Ya puede iniciar sesión en Postman.');
        return self::SUCCESS;
    }
}

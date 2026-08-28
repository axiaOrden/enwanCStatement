<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateUser extends Command
{
    protected $signature = 'users:create
        {name? : User display name}
        {email? : User email address}';

    protected $description = 'Create an application user from the secure backend console';

    public function handle(): int
    {
        $name = trim((string) ($this->argument('name') ?: $this->ask('Name')));
        $email = strtolower(trim((string) ($this->argument('email') ?: $this->ask('Email'))));
        $password = (string) $this->secret('Password');
        $passwordConfirmation = (string) $this->secret('Confirm password');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create(compact('name', 'email', 'password'));

        $this->components->info("User {$user->email} created successfully.");

        return self::SUCCESS;
    }
}

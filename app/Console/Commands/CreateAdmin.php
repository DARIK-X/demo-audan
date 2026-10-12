<?php
namespace App\Console\Commands;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

class CreateAdmin extends Command
{
    protected $signature = 'portal:create-admin';
    protected $description = 'Create an administrator interactively';
    public function handle(): int
    {
        $name = $this->ask('Аты');
        $email = $this->ask('Email');
        $password = $this->secret('Құпиясөз (кемінде 12 таңба)');
        $data = compact('name', 'email', 'password');
        $validator = Validator::make($data, ['name' => ['required', 'string', 'max:100'], 'email' => ['required', 'email', 'max:255', 'unique:users,email'], 'password' => ['required', Password::min(12)->letters()->numbers()]]);
        if ($validator->fails()) { foreach ($validator->errors()->all() as $error) $this->error($error); return self::FAILURE; }
        User::create(['name' => $name, 'email' => $email, 'password' => $password, 'is_admin' => true]);
        $this->info('Администратор создан.');
        return self::SUCCESS;
    }
}

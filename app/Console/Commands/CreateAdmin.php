<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateAdmin extends Command
{
    protected $signature = 'marketplace:create-admin {name} {email}';

    protected $description = 'Create or promote a marketplace administrator account';

    public function handle(): int
    {
        $password = $this->secret('Admin password (at least 12 characters)');
        if (! $password || strlen($password) < 12) {
            $this->error('The admin password must contain at least 12 characters.');

            return self::FAILURE;
        }

        $user = User::firstOrNew(['email' => $this->argument('email')]);
        $user->name = $this->argument('name');
        $user->password = $password;
        $user->role = 'admin';
        $user->is_active = true;
        $user->save();

        $this->info("Administrator account ready for {$user->email}.");

        return self::SUCCESS;
    }
}

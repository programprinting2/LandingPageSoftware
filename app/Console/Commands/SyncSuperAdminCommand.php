<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SyncSuperAdminCommand extends Command
{
    protected $signature = 'user:sync-super-admin';

    protected $description = 'Promote SUPER_ADMIN_EMAIL user to super_admin role';

    public function handle(): int
    {
        $email = strtolower(trim((string) config('softsale.super_admin_email')));

        if ($email === '') {
            $this->warn('SUPER_ADMIN_EMAIL is not set in .env');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->warn("No user found with email: {$email}. Register first, then run this command again.");

            return self::FAILURE;
        }

        if ($user->role === 'super_admin') {
            $this->info("User {$email} is already super_admin.");

            return self::SUCCESS;
        }

        $user->update(['role' => 'super_admin']);
        $this->info("User {$email} promoted to super_admin.");

        return self::SUCCESS;
    }
}

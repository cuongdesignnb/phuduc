<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GrantAdminUser extends Command
{
    protected $signature = 'user:grant-admin {email : Email of the existing user to promote}';

    protected $description = 'Grant Admin access to an existing user';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));

        if ($email === '') {
            $this->error('A valid existing user email is required.');

            return self::FAILURE;
        }

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $user) {
            $this->error("No existing user was found for {$email}.");

            return self::FAILURE;
        }

        if ($user->is_admin) {
            $this->info("User {$email} already has Admin access.");

            return self::SUCCESS;
        }

        $user->forceFill(['is_admin' => true])->save();

        Log::notice('Admin access granted through user:grant-admin.', [
            'user_id' => $user->getKey(),
        ]);

        $this->info("Admin access granted to {$email}.");

        return self::SUCCESS;
    }
}

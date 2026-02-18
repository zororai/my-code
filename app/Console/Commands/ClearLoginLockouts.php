<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\LoginLockout;
use App\LoginAttempt;

class ClearLoginLockouts extends Command
{
    protected $signature = 'fix:lockouts {--email= : Clear lockout for specific email/phone}';
    protected $description = 'Clear login lockouts and failed attempt records';

    public function handle()
    {
        $email = $this->option('email');

        if ($email) {
            // Clear specific account
            $lockouts = LoginLockout::where('email', $email)->delete();
            $attempts = LoginAttempt::where('email', $email)->delete();
            $this->info("Cleared {$lockouts} lockout(s) and {$attempts} attempt(s) for: {$email}");
        } else {
            // Clear all lockouts
            $lockouts = LoginLockout::truncate();
            $attempts = LoginAttempt::where('successful', false)->delete();
            $this->info("Cleared all lockouts and failed login attempts.");
        }

        return 0;
    }
}

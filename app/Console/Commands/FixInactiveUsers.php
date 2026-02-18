<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;

class FixInactiveUsers extends Command
{
    protected $signature = 'fix:users';
    protected $description = 'Activate all users that have is_active = null or false';

    public function handle()
    {
        $count = User::whereNull('is_active')
            ->orWhere('is_active', false)
            ->update(['is_active' => true]);

        $this->info("Activated {$count} users.");
        
        return 0;
    }
}

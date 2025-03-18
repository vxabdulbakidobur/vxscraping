<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Enums\UserStatusEnum;

class FixAllUsers extends Command
{
    protected $signature = 'users:fix-all';
    protected $description = 'Fix all users by setting status to ACTIVE';

    public function handle()
    {
        $users = User::all();
        $count = 0;
        
        $this->info("Fixing user accounts...");
        
        foreach ($users as $user) {
            if (!isset($user->status) || $user->status !== UserStatusEnum::ACTIVE) {
                $user->status = UserStatusEnum::ACTIVE;
                $user->save();
                $count++;
                $this->info("Activated user: {$user->email}");
            }
        }
        
        $this->info("Completed! {$count} user(s) were activated.");
        
        return 0;
    }
} 
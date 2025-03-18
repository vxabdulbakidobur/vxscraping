<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Enums\UserStatusEnum;

class ActivateUser extends Command
{
    protected $signature = 'user:activate {email}';
    protected $description = 'Activate a user account';

    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '$email' not found.");
            return 1;
        }
        
        if (isset($user->status) && $user->status == UserStatusEnum::ACTIVE) {
            $this->info("User account is already active.");
            return 0;
        }
        
        // Set the status to ACTIVE
        $user->status = UserStatusEnum::ACTIVE;
        $user->save();
        
        $this->info("User account '$email' has been activated successfully.");
        
        return 0;
    }
} 
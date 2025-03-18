<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetUserPassword extends Command
{
    protected $signature = 'user:reset-password {email} {password}';
    protected $description = 'Reset user password';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        
        $newPasswordHash = Hash::make($password);
        
        $userExists = DB::table('users')->where('email', $email)->exists();
        
        if (!$userExists) {
            $this->error("User with email '$email' not found.");
            return 1;
        }
        
        DB::table('users')
            ->where('email', $email)
            ->update(['password' => $newPasswordHash]);
            
        $this->info("Password for user '$email' has been updated successfully.");
        $this->info("New password hash: " . substr($newPasswordHash, 0, 10) . "...");
        
        return 0;
    }
} 
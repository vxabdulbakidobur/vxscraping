<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class VerifyUserCredentials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:verify-credentials {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify user credentials and debug authentication issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        
        $this->info("Verifying credentials for: $email");
        
        // 1. Check if user exists
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '$email' not found in the database.");
            return 1;
        }
        
        $this->info("User found: ID #{$user->id}, Name: {$user->name}");
        
        // 2. Check password against hash
        $passwordCorrect = Hash::check($password, $user->password);
        
        if (!$passwordCorrect) {
            $this->error("Password does not match the stored hash.");
            
            // Debug info
            $this->info("Password hash in DB: " . substr($user->password, 0, 10) . "...");
            
            // Try rehashing the password
            $newHash = Hash::make($password);
            $this->info("Generated hash for entered password: " . substr($newHash, 0, 10) . "...");
            
            return 1;
        }
        
        $this->info("✓ Password is correct!");
        
        // 3. Check user status
        if (isset($user->status)) {
            $statusValue = $user->status instanceof \BackedEnum ? $user->status->value : $user->status;
            if ($statusValue === 1) {  // 1 = ACTIVE in UserStatusEnum
                $this->info("User account is active. Status: " . $statusValue);
            } else {
                $this->warn("User account is not active. Status: " . $statusValue);
            }
        }
        
        // 4. Try Auth::attempt
        $result = auth()->attempt([
            'email' => $email,
            'password' => $password
        ]);
        
        if (!$result) {
            $this->error("Auth::attempt() failed despite correct credentials.");
            $this->info("This indicates a problem with the authentication guard or provider.");
        } else {
            $this->info("✓ Auth::attempt() successful!");
        }
        
        // 5. Check auth configuration
        $this->info("\nAuthentication Configuration:");
        $this->info("Default Guard: " . config('auth.defaults.guard'));
        $this->info("User Provider: " . config('auth.guards.web.provider'));
        $this->info("Provider Model: " . config('auth.providers.users.model'));
        
        return 0;
    }
}

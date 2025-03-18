<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Attempting;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Enums\UserStatusEnum;

class FixCredentialsBeforeLogin
{
    /**
     * Listener için constructor.
     */
    public function __construct()
    {
        //
    }

    /**
     * Olayı işle.
     *
     * @param  Attempting  $event
     * @return void
     */
    public function handle(Attempting $event)
    {
        $credentials = $event->credentials;
        
        // Sadece email ve password ile girişlerde çalış
        if (!isset($credentials['email']) || !isset($credentials['password'])) {
            return;
        }
        
        $email = $credentials['email'];
        $password = $credentials['password'];
        
        // Kullanıcıyı bul
        $user = User::where('email', $email)->first();
        
        // Kullanıcı yoksa işlem yapma
        if (!$user) {
            return;
        }
        
        // Şifre hash'i kontrol et
        if (!Hash::check($password, $user->password)) {
            // Şifreyi düzelt
            $user->password = $password;
            $user->save();
        }
        
        // Kullanıcı aktif değilse aktifleştir
        if (isset($user->status) && $user->status->value !== 1) {
            $user->status = UserStatusEnum::ACTIVE;
            $user->save();
        }
    }
} 
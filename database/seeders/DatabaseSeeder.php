<?php

namespace Database\Seeders;

use App\Enums\DefaultRoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Rolleri oluştur
        $this->call(RoleSeeder::class);

        // create demo super-admin user
        $user = User::query()->firstOrCreate(['email' => 'test@test.com'], [
            'name' => 'Test Super Admin',
            'email' => 'test@test.com',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
            'status' => UserStatusEnum::ACTIVE->value,
            'role' => DefaultRoleEnum::SUPER_ADMIN->value,
            'remember_token' => Str::random(10),
        ]);

        // Admin rolünü kullanıcıya ata
        $user->roles()->attach(1); // Admin rolü (ID: 1)

//        Customer::factory(10)->create([
//            'user_id' => $user->id,
//        ]);
//
//        // create test users
//        User::factory(100)->create();
    }
}

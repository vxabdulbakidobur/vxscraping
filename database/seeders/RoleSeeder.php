<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin rolü
        Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'permissions' => [
                'view_users', 'create_users', 'edit_users', 'delete_users',
                'view_roles', 'create_roles', 'edit_roles', 'delete_roles',
                'view_customers', 'create_customers', 'edit_customers', 'delete_customers',
                'view_sites', 'create_sites', 'edit_sites', 'delete_sites',
            ],
            'description' => 'Administrator role with full access',
            'is_default' => false,
        ]);

        // Editor rolü
        Role::create([
            'name' => 'Editor',
            'slug' => 'editor',
            'permissions' => [
                'view_customers', 'create_customers', 'edit_customers',
                'view_sites', 'create_sites', 'edit_sites',
            ],
            'description' => 'Editor role with limited access',
            'is_default' => false,
        ]);

        // Viewer rolü
        Role::create([
            'name' => 'Viewer',
            'slug' => 'viewer',
            'permissions' => [
                'view_customers',
                'view_sites',
            ],
            'description' => 'Viewer role with read-only access',
            'is_default' => true,
        ]);
    }
}

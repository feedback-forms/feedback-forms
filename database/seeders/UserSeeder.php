<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First check if admin user already exists to avoid unique constraint violation
        $adminExists = User::where('name', 'admin')->exists();

        if (!$adminExists) {
            User::create([
                'name' => 'admin',
                'email' => 'noreply@uts-x.com',
                'password' => Hash::make('admin'),
                'is_admin' => 1,
                'email_verified_at' => now(),
            ]);
        } else {
            // Optionally update existing admin user if needed
            User::where('name', 'admin')->update([
                'email' => 'noreply@uts-x.com',
                'is_admin' => 1,
                'email_verified_at' => now(),
            ]);
        }
    }
}

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
        User::updateOrCreate([
            'name' => 'admin',
            'email' => 'noreply@uts-x.com',
            'password' => Hash::make('admin'),
            'is_admin' => 1,
            'email_verified_at' => now(),
        ]);
    }
}

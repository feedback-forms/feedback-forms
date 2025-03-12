<?php

namespace Database\Seeders;

use App\Models\Registerkey;
use Illuminate\Database\Seeder;

class RegisterKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Registerkey::updateOrCreate([
            'code' => 'ABCD-EFGH'
        ]);

        Registerkey::updateOrCreate([
            'code' => 'XYZ1-2345',
            'expire_at' => '2020-01-01 12:00:00'
        ]);
    }
}

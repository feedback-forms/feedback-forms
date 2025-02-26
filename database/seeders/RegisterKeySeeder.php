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
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\SchoolYear;
use Illuminate\Database\Seeder;

class SchoolYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolYears = [
            ['name' => '2023/24'],
            ['name' => '2024/25'],
        ];

        foreach ($schoolYears as $year) {
            SchoolYear::create($year);
        }
    }
}

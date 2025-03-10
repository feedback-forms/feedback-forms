<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            ['code' => 'AWP', 'name' => 'Anwendungsentwicklung & Programmierung'],
            ['code' => 'ITT', 'name' => 'Informationstechnologie Technik'],
            ['code' => 'ITS', 'name' => 'Informationstechnologie Systeme'],
            ['code' => 'SP', 'name' => 'Sport'],
            ['code' => 'ITP', 'name' => 'Informationstechnologie Projekt'],
            ['code' => 'D', 'name' => 'Deutsch'],
            ['code' => 'E', 'name' => 'Englisch'],
            ['code' => 'PUG', 'name' => 'Politik und Gesellschaft'],
            ['code' => 'BGP', 'name' => 'Betriebs- und gesamtwirtschaftliche Prozesse'],
            ['code' => 'kRel', 'name' => 'Katholische Religion'],
            ['code' => 'eRel', 'name' => 'Evangelische Religion'],
            ['code' => 'ET', 'name' => 'Ethik'],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate($subject);
        }
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SchoolYear>
 */
class SchoolYearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a realistic school year in format "YYYY-YYYY" where the second year is first+1
        $startYear = $this->faker->numberBetween(2020, 2030);
        $endYear = $startYear + 1;

        return [
            'name' => "{$startYear}-{$endYear}",
        ];
    }

    /**
     * Configure the factory to create the current school year.
     */
    public function current()
    {
        $currentYear = now()->year;
        $nextYear = $currentYear + 1;

        if (now()->month >= 8) { // Assuming school year starts in August/September
            return $this->state(function (array $attributes) use ($currentYear, $nextYear) {
                return [
                    'name' => "{$currentYear}-{$nextYear}",
                ];
            });
        } else {
            $previousYear = $currentYear - 1;
            return $this->state(function (array $attributes) use ($previousYear, $currentYear) {
                return [
                    'name' => "{$previousYear}-{$currentYear}",
                ];
            });
        }
    }
}
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Feedback>
 */
class FeedbackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'user_id' => 1,
            'feedback_template_id' => 1,
            'accesskey' => strtoupper(substr(md5(uniqid()), 0, 8)),
            'limit' => $this->faker->numberBetween(-1, 100),
            'expire_date' => now()->addDays($this->faker->numberBetween(1, 30)),
            'status' => $this->faker->randomElement(['draft', 'running', 'expired']),
            'school_year' => '2023-2024',
            'department' => 'IT',
            'grade_level' => '10',
            'class' => '10A',
            'subject' => 'Math',
        ];
    }
}

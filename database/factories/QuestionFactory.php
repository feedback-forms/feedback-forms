<?php

namespace Database\Factories;

use App\Models\Feedback;
use App\Models\FeedbackTemplate;
use App\Models\QuestionTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'content', 'teaching_style', 'learning_environment',
            'materials', 'feedback', 'overall'
        ];

        return [
            'feedback_template_id' => FeedbackTemplate::factory(),
            'question_template_id' => QuestionTemplate::inRandomOrder()->first() ?? 1,
            'feedback_id' => Feedback::factory(),
            'question' => $this->faker->sentence() . '?',
            'order' => $this->faker->numberBetween(1, 20),
            'category' => $this->faker->randomElement($categories),
        ];
    }

    /**
     * Configure the factory to create a question for a specific feedback template.
     */
    public function forTemplate(Feedback_template $template)
    {
        return $this->state(function (array $attributes) use ($template) {
            return [
                'feedback_template_id' => $template->id,
                'feedback_id' => null,
            ];
        });
    }

    /**
     * Configure the factory to create a question for a specific feedback survey.
     */
    public function forSurvey(Feedback $feedback)
    {
        return $this->state(function (array $attributes) use ($feedback) {
            return [
                'feedback_id' => $feedback->id,
                'feedback_template_id' => null,
            ];
        });
    }
}
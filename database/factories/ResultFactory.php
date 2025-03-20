<?php

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Result>
 */
class ResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $valueTypes = ['numeric', 'text', 'checkbox'];
        $selectedType = $this->faker->randomElement($valueTypes);

        // Generate realistic rating values based on type
        $ratingValue = match ($selectedType) {
            'numeric' => (string)$this->faker->numberBetween(1, 5),
            'text' => $this->faker->sentence(10),
            'checkbox' => (string)$this->faker->boolean(),
            default => null
        };

        return [
            'question_id' => Question::factory(),
            'submission_id' => Str::uuid()->toString(),
            'value_type' => $selectedType,
            'rating_value' => $ratingValue
        ];
    }

    /**
     * Configure the factory to create a result for a specific question.
     */
    public function forQuestion(Question $question)
    {
        return $this->state(function (array $attributes) use ($question) {
            return [
                'question_id' => $question->id,
            ];
        });
    }

    /**
     * Configure the factory to create a result for a specific submission.
     */
    public function forSubmission(string $submissionId)
    {
        return $this->state(function (array $attributes) use ($submissionId) {
            return [
                'submission_id' => $submissionId,
            ];
        });
    }

    /**
     * Configure the factory to create a numeric rating result.
     */
    public function asNumericRating($rating = null)
    {
        return $this->state(function (array $attributes) use ($rating) {
            return [
                'value_type' => 'numeric',
                'rating_value' => $rating ?? (string)$this->faker->numberBetween(1, 5),
            ];
        });
    }

    /**
     * Configure the factory to create a text feedback result.
     */
    public function asTextFeedback($text = null)
    {
        return $this->state(function (array $attributes) use ($text) {
            return [
                'value_type' => 'text',
                'rating_value' => $text ?? $this->faker->paragraph(),
            ];
        });
    }

    /**
     * Configure the factory to create a checkbox result.
     */
    public function asCheckbox($checked = null)
    {
        return $this->state(function (array $attributes) use ($checked) {
            $value = $checked ?? $this->faker->boolean();
            return [
                'value_type' => 'checkbox',
                'rating_value' => (string)$value,
            ];
        });
    }
}
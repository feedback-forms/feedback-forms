<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Question;
use Illuminate\Support\Str;

class CategoryAssignmentSeeder extends Seeder
{
    /**
     * List of sample categories.
     */
    protected $categories = [
        'teacher_behavior',
        'teaching_quality',
        'classroom_atmosphere',
        'learning_materials',
        'feedback',
        'communication',
        'assessment'
    ];

    /**
     * Assign categories to existing questions.
     */
    public function run(): void
    {
        $questions = Question::all();

        if ($questions->isEmpty()) {
            $this->command->info('No questions found to categorize.');
            return;
        }

        $this->command->info('Assigning categories to ' . $questions->count() . ' questions.');

        foreach ($questions as $question) {
            // Parse the question text to determine a suitable category
            $category = $this->categorizeByText($question->question);

            // Update the question with the assigned category
            $question->category = $category;
            $question->save();
        }

        $this->command->info('Categories assigned successfully.');
    }

    /**
     * Determine category based on question text.
     */
    private function categorizeByText(string $questionText): string
    {
        $questionText = Str::lower($questionText);

        // Keywords for different categories
        $keywordMap = [
            'teacher_behavior' => ['teacher', 'behavior', 'instructor', 'teaching style', 'attitude', 'punctual'],
            'teaching_quality' => ['quality', 'clarity', 'understanding', 'explains', 'methods', 'approach'],
            'classroom_atmosphere' => ['atmosphere', 'environment', 'climate', 'feel', 'comfort', 'classroom'],
            'learning_materials' => ['materials', 'textbook', 'resources', 'handouts', 'slides', 'presentation'],
            'feedback' => ['feedback', 'response', 'comments', 'suggestions', 'improvement'],
            'communication' => ['communication', 'speaks', 'listens', 'interact', 'discussion', 'questions'],
            'assessment' => ['assessment', 'test', 'exam', 'grade', 'evaluation', 'assignment']
        ];

        foreach ($keywordMap as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (Str::contains($questionText, $keyword)) {
                    return $category;
                }
            }
        }

        // If no match found, assign a random category to ensure distribution
        return $this->categories[array_rand($this->categories)];
    }
}
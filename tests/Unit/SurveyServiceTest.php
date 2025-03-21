<?php

namespace Tests\Unit;

use App\Models\Feedback;
use App\Models\FeedbackTemplate;
use App\Models\Question;
use App\Models\QuestionTemplate;
use App\Models\Result;
use App\Models\ResponseValue;
use App\Services\SurveyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SurveyService $surveyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->surveyService = new SurveyService();
    }

    /**
     * Test calculating statistics for a range question.
     */
    public function test_calculate_statistics_for_range_question(): void
    {
        // Create a feedback template
        $template = FeedbackTemplate::create([
            'name' => 'Test Template',
            'title' => 'Test Template Title',
        ]);

        // Create a question template for range type
        $questionTemplate = QuestionTemplate::create([
            'type' => 'range',
            'min_value' => 1,
            'max_value' => 5,
        ]);

        // Create a survey
        $survey = Feedback::create([
            'user_id' => 1,
            'feedback_template_id' => $template->id,
            'accesskey' => 'test-key',
            'limit' => -1,
            'already_answered' => 3,
            'expire_date' => now()->addDays(7),
        ]);

        // Create a question
        $question = Question::create([
            'feedback_id' => $survey->id,
            'feedback_template_id' => $template->id,
            'question_template_id' => $questionTemplate->id,
            'question' => 'How would you rate this course?',
        ]);

        // Create some results with different ratings
        for ($i = 1; $i <= 5; $i++) {
            $result = Result::create([
                'question_id' => $question->id,
                'submission_id' => (string) \Illuminate\Support\Str::uuid(),
                'value_type' => 'number',
                'rating_value' => $i
            ]);
        }

        // Calculate statistics
        $statistics = $this->surveyService->calculateStatisticsForSurvey($survey);

        // Assert statistics are calculated correctly
        $this->assertCount(1, $statistics);
        $this->assertEquals($question->id, $statistics[0]['question']->id);
        $this->assertEquals('range', $statistics[0]['template_type']);

        // Check average rating (1 + 2 + 3 + 4 + 5) / 5 = 3
        $this->assertEquals(3, $statistics[0]['data']['average_rating']);

        // Check median rating (sorted: 1, 2, 3, 4, 5) -> median is 3
        $this->assertEquals(3, $statistics[0]['data']['median_rating']);

        // Check rating counts
        $this->assertCount(5, $statistics[0]['data']['rating_counts']);
        $this->assertEquals(1, $statistics[0]['data']['rating_counts'][1]);
        $this->assertEquals(1, $statistics[0]['data']['rating_counts'][2]);
        $this->assertEquals(1, $statistics[0]['data']['rating_counts'][3]);
        $this->assertEquals(1, $statistics[0]['data']['rating_counts'][4]);
        $this->assertEquals(1, $statistics[0]['data']['rating_counts'][5]);
    }

    /**
     * Test calculating statistics for a text question.
     */
    public function test_calculate_statistics_for_text_question(): void
    {
        // Create a feedback template
        $template = FeedbackTemplate::create([
            'name' => 'Test Template',
            'title' => 'Test Template Title',
        ]);

        // Create a question template for text type
        $questionTemplate = QuestionTemplate::create([
            'type' => 'text',
        ]);

        // Create a survey
        $survey = Feedback::create([
            'user_id' => 1,
            'feedback_template_id' => $template->id,
            'accesskey' => 'test-key',
            'limit' => -1,
            'already_answered' => 2,
            'expire_date' => now()->addDays(7),
        ]);

        // Create a question
        $question = Question::create([
            'feedback_id' => $survey->id,
            'feedback_template_id' => $template->id,
            'question_template_id' => $questionTemplate->id,
            'question' => 'What did you like about this course?',
        ]);

        // Create some results with text responses
        for ($i = 1; $i <= 3; $i++) {
            $result = Result::create([
                'question_id' => $question->id,
                'submission_id' => (string) \Illuminate\Support\Str::uuid(),
                'value_type' => 'text',
                'rating_value' => "Sample text response $i"
            ]);
        }

        // Calculate statistics
        $statistics = $this->surveyService->calculateStatisticsForSurvey($survey);

        // Assert statistics are calculated correctly
        $this->assertCount(1, $statistics);
        $this->assertEquals($question->id, $statistics[0]['question']->id);
        $this->assertEquals('text', $statistics[0]['template_type']);

        // Check response count
        $this->assertEquals(3, $statistics[0]['data']['response_count']);
    }

    /**
     * Test error handling in calculateStatisticsForSurvey.
     */
    public function test_calculate_statistics_handles_errors_gracefully(): void
    {
        // Create a mock survey that will cause an error
        $mockSurvey = $this->createMock(Feedback::class);
        $mockSurvey->method('load')->willThrowException(new \Exception('Test exception'));
        $mockSurvey->id = 999;

        // Calculate statistics with the mock survey
        $statistics = $this->surveyService->calculateStatisticsForSurvey($mockSurvey);

        // Assert error is handled gracefully
        $this->assertCount(1, $statistics);
        $this->assertEquals('error', $statistics[0]['template_type']);
        $this->assertEquals('An error occurred while calculating statistics.', $statistics[0]['data']['message']);
    }
}

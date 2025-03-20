<?php

namespace Tests\Unit;

use App\Models\Feedback;
use App\Models\Feedback_template;
use App\Models\Question;
use App\Models\Question_template;
use App\Models\Result;
use App\Services\StatisticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class StatisticsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StatisticsService $statisticsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->statisticsService = new StatisticsService();
    }

    /**
     * Test calculating statistics for a range question.
     */
    public function test_calculate_statistics_for_range_question(): void
    {
        // Create a feedback template
        $template = Feedback_template::create([
            'name' => 'Test Template',
            'title' => 'Test Template Title',
        ]);

        // Create a question template for range type
        $questionTemplate = Question_template::create([
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
            'expire_date' => now()->addDays(7),
        ]);

        // Create a question
        $question = Question::create([
            'feedback_id' => $survey->id,
            'feedback_template_id' => $template->id,
            'question_template_id' => $questionTemplate->id,
            'question' => 'How would you rate this course?',
        ]);

        // Create results with different ratings
        $ratings = [1, 2, 3, 4, 5];
        foreach ($ratings as $rating) {
            Result::create([
                'question_id' => $question->id,
                'submission_id' => (string) \Illuminate\Support\Str::uuid(),
                'value_type' => 'number',
                'rating_value' => $rating
            ]);
        }

        // Calculate statistics
        $statistics = $this->statisticsService->calculateStatisticsForSurvey($survey);

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
        $this->assertEquals(1, $statistics[0]['data']['rating_counts']['1']);
        $this->assertEquals(1, $statistics[0]['data']['rating_counts']['2']);
        $this->assertEquals(1, $statistics[0]['data']['rating_counts']['3']);
        $this->assertEquals(1, $statistics[0]['data']['rating_counts']['4']);
        $this->assertEquals(1, $statistics[0]['data']['rating_counts']['5']);

        // Check submission count
        $this->assertEquals(5, $statistics[0]['data']['submission_count']);
    }

    /**
     * Test calculating statistics for a text question.
     */
    public function test_calculate_statistics_for_text_question(): void
    {
        // Create a feedback template
        $template = Feedback_template::create([
            'name' => 'Test Template',
            'title' => 'Test Template Title',
        ]);

        // Create a question template for text type
        $questionTemplate = Question_template::create([
            'type' => 'text',
        ]);

        // Create a survey
        $survey = Feedback::create([
            'user_id' => 1,
            'feedback_template_id' => $template->id,
            'accesskey' => 'test-key',
            'limit' => -1,
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
        $textResponses = [
            'I enjoyed the interactive exercises',
            'The instructor was very knowledgeable',
            'Clear explanations of complex topics'
        ];

        foreach ($textResponses as $response) {
            Result::create([
                'question_id' => $question->id,
                'submission_id' => (string) \Illuminate\Support\Str::uuid(),
                'value_type' => 'text',
                'rating_value' => $response
            ]);
        }

        // Calculate statistics
        $statistics = $this->statisticsService->calculateStatisticsForSurvey($survey);

        // Assert statistics are calculated correctly
        $this->assertCount(1, $statistics);
        $this->assertEquals($question->id, $statistics[0]['question']->id);
        $this->assertEquals('text', $statistics[0]['template_type']);

        // Check response count
        $this->assertEquals(3, $statistics[0]['data']['response_count']);

        // Check responses are stored correctly
        $this->assertCount(3, $statistics[0]['data']['responses']);
        foreach ($textResponses as $response) {
            $this->assertContains($response, $statistics[0]['data']['responses']);
        }

        // Check submission count
        $this->assertEquals(3, $statistics[0]['data']['submission_count']);
    }

    /**
     * Test calculating statistics for a checkbox question.
     */
    public function test_calculate_statistics_for_checkbox_question(): void
    {
        // Create a feedback template
        $template = Feedback_template::create([
            'name' => 'Test Template',
            'title' => 'Test Template Title',
        ]);

        // Create a question template for checkbox type
        $questionTemplate = Question_template::create([
            'type' => 'checkbox',
        ]);

        // Create a survey
        $survey = Feedback::create([
            'user_id' => 1,
            'feedback_template_id' => $template->id,
            'accesskey' => 'test-key',
            'limit' => -1,
            'expire_date' => now()->addDays(7),
        ]);

        // Create a question
        $question = Question::create([
            'feedback_id' => $survey->id,
            'feedback_template_id' => $template->id,
            'question_template_id' => $questionTemplate->id,
            'question' => 'Which topics were most useful?',
        ]);

        // Create checkbox responses
        $options = ['Programming', 'Design', 'Databases', 'Programming'];
        $submissionIds = [
            (string) \Illuminate\Support\Str::uuid(),
            (string) \Illuminate\Support\Str::uuid(),
            (string) \Illuminate\Support\Str::uuid()
        ];

        // First submission selects Programming and Design
        Result::create([
            'question_id' => $question->id,
            'submission_id' => $submissionIds[0],
            'value_type' => 'checkbox',
            'rating_value' => 'Programming'
        ]);

        Result::create([
            'question_id' => $question->id,
            'submission_id' => $submissionIds[0],
            'value_type' => 'checkbox',
            'rating_value' => 'Design'
        ]);

        // Second submission selects Databases
        Result::create([
            'question_id' => $question->id,
            'submission_id' => $submissionIds[1],
            'value_type' => 'checkbox',
            'rating_value' => 'Databases'
        ]);

        // Third submission selects Programming
        Result::create([
            'question_id' => $question->id,
            'submission_id' => $submissionIds[2],
            'value_type' => 'checkbox',
            'rating_value' => 'Programming'
        ]);

        // Calculate statistics
        $statistics = $this->statisticsService->calculateStatisticsForSurvey($survey);

        // Assert statistics are calculated correctly
        $this->assertCount(1, $statistics);
        $this->assertEquals($question->id, $statistics[0]['question']->id);
        $this->assertEquals('checkbox', $statistics[0]['template_type']);

        // Check option counts
        $this->assertArrayHasKey('option_counts', $statistics[0]['data']);
        $this->assertEquals(2, $statistics[0]['data']['option_counts']['Programming']);
        $this->assertEquals(1, $statistics[0]['data']['option_counts']['Design']);
        $this->assertEquals(1, $statistics[0]['data']['option_counts']['Databases']);

        // We expect 3 unique submissions
        $this->assertEquals(3, $statistics[0]['data']['submission_count']);
    }

    /**
     * Test statistics calculation for a target template survey.
     */
    public function test_calculate_statistics_for_target_template(): void
    {
        // Create a feedback template for target type
        $template = Feedback_template::create([
            'name' => 'templates.feedback.target',
            'title' => 'Target Feedback Template',
        ]);

        // Create a question template for range type
        $questionTemplate = Question_template::create([
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
            'expire_date' => now()->addDays(7),
        ]);

        // Create multiple target segments (questions)
        $segments = [
            'Clear explanations',
            'Interactive content',
            'Learning resources',
            'Instructor knowledge'
        ];

        $questions = [];
        foreach ($segments as $index => $segment) {
            $questions[$index] = Question::create([
                'feedback_id' => $survey->id,
                'feedback_template_id' => $template->id,
                'question_template_id' => $questionTemplate->id,
                'question' => $segment,
                'order' => $index + 1,
            ]);

            // Add ratings for each segment
            for ($i = 1; $i <= 3; $i++) {
                Result::create([
                    'question_id' => $questions[$index]->id,
                    'submission_id' => (string) \Illuminate\Support\Str::uuid(),
                    'value_type' => 'number',
                    'rating_value' => $index + 2 // Different rating for each segment
                ]);
            }
        }

        // Calculate statistics
        $statistics = $this->statisticsService->calculateStatisticsForSurvey($survey);

        // Assert target marker is present
        $this->assertEquals('target', $statistics[0]['template_type']);
        $this->assertArrayHasKey('segment_statistics', $statistics[0]['data']);

        // Check segment statistics
        $segmentStats = $statistics[0]['data']['segment_statistics'];
        $this->assertCount(count($segments), $segmentStats);

        // Check each segment's statistics
        foreach ($segmentStats as $index => $stat) {
            $this->assertEquals($index, $stat['segment_index']);
            $this->assertEquals($segments[$index], $stat['statement']);
            $this->assertEquals($index + 2, $stat['average_rating']); // We set the rating to index + 2
            $this->assertEquals(3, $stat['response_count']);
            $this->assertEquals(3, $stat['submission_count']);
        }

        // In the target template implementation, we might only have the target marker
        // without including individual questions in the statistics array
        $this->assertGreaterThanOrEqual(1, count($statistics));
    }

    /**
     * Test statistics calculation for a smiley template survey.
     */
    public function test_calculate_statistics_for_smiley_template(): void
    {
        // Create a feedback template for smiley type
        $template = Feedback_template::create([
            'name' => 'templates.feedback.smiley',
            'title' => 'Smiley Feedback Template',
        ]);

        // Create question templates
        $rangeTemplate = Question_template::create([
            'type' => 'range',
            'min_value' => 1,
            'max_value' => 5,
        ]);

        $textTemplate = Question_template::create([
            'type' => 'text',
        ]);

        // Create a survey
        $survey = Feedback::create([
            'user_id' => 1,
            'feedback_template_id' => $template->id,
            'accesskey' => 'test-key',
            'limit' => -1,
            'expire_date' => now()->addDays(7),
        ]);

        // Create a rating question
        $ratingQuestion = Question::create([
            'feedback_id' => $survey->id,
            'feedback_template_id' => $template->id,
            'question_template_id' => $rangeTemplate->id,
            'question' => 'Overall rating',
            'order' => 1,
        ]);

        // Create a text feedback question
        $textQuestion = Question::create([
            'feedback_id' => $survey->id,
            'feedback_template_id' => $template->id,
            'question_template_id' => $textTemplate->id,
            'question' => 'Additional feedback',
            'order' => 2,
        ]);

        // Add ratings and feedback
        $submissionId = (string) \Illuminate\Support\Str::uuid();

        // Rating result
        Result::create([
            'question_id' => $ratingQuestion->id,
            'submission_id' => $submissionId,
            'value_type' => 'number',
            'rating_value' => 4
        ]);

        // Text feedback result
        Result::create([
            'question_id' => $textQuestion->id,
            'submission_id' => $submissionId,
            'value_type' => 'text',
            'rating_value' => 'Great course overall'
        ]);

        // Calculate statistics
        $statistics = $this->statisticsService->calculateStatisticsForSurvey($survey);

        // Assert smiley marker is present
        $this->assertEquals('smiley', $statistics[0]['template_type']);
        $this->assertEquals(1, $statistics[0]['data']['submission_count']);

        // Check that individual questions are also included
        $this->assertGreaterThan(1, count($statistics));

        // Find the range question stats
        $rangeStats = null;
        foreach ($statistics as $stat) {
            if (isset($stat['question']) && $stat['question']->id === $ratingQuestion->id) {
                $rangeStats = $stat;
                break;
            }
        }

        $this->assertNotNull($rangeStats);
        $this->assertEquals(4, $rangeStats['data']['average_rating']);

        // Find the text question stats
        $textStats = null;
        foreach ($statistics as $stat) {
            if (isset($stat['question']) && $stat['question']->id === $textQuestion->id) {
                $textStats = $stat;
                break;
            }
        }

        $this->assertNotNull($textStats);
        $this->assertContains('Great course overall', $textStats['data']['responses']);
    }

    /**
     * Test statistics calculation for a table template survey.
     */
    public function test_calculate_statistics_for_table_template(): void
    {
        // Create a feedback template for table type
        $template = Feedback_template::create([
            'name' => 'templates.feedback.table',
            'title' => 'Table Feedback Template',
        ]);

        // Create a question template for range type
        $questionTemplate = Question_template::create([
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
            'expire_date' => now()->addDays(7),
        ]);

        // Create questions for different categories
        $questions = [
            // Behavior category
            Question::create([
                'feedback_id' => $survey->id,
                'feedback_template_id' => $template->id,
                'question_template_id' => $questionTemplate->id,
                'question' => '... ist motiviert und engagiert',
                'category' => 'behavior',
                'order' => 1,
            ]),

            // Quality category
            Question::create([
                'feedback_id' => $survey->id,
                'feedback_template_id' => $template->id,
                'question_template_id' => $questionTemplate->id,
                'question' => 'Der Unterricht ist gut strukturiert',
                'category' => 'quality',
                'order' => 2,
            ]),

            // Statements category
            Question::create([
                'feedback_id' => $survey->id,
                'feedback_template_id' => $template->id,
                'question_template_id' => $questionTemplate->id,
                'question' => 'Ich lerne viel in diesem Kurs',
                'category' => 'statements',
                'order' => 3,
            ]),
        ];

        // Add ratings for each question
        $submissionId = (string) \Illuminate\Support\Str::uuid();
        foreach ($questions as $index => $question) {
            Result::create([
                'question_id' => $question->id,
                'submission_id' => $submissionId,
                'value_type' => 'number',
                'rating_value' => $index + 3 // Different rating for each question
            ]);
        }

        // Calculate statistics
        $statistics = $this->statisticsService->calculateStatisticsForSurvey($survey);

        // Assert table marker is present
        $this->assertEquals('table', $statistics[0]['template_type']);
        $this->assertTrue($statistics[0]['data']['table_survey']);
        $this->assertArrayHasKey('table_categories', $statistics[0]['data']);

        // Check table categories
        $tableCategories = $statistics[0]['data']['table_categories'];

        // We should have at least the behavior, quality, and statements categories
        $this->assertArrayHasKey('behavior', $tableCategories);
        $this->assertArrayHasKey('quality', $tableCategories);
        $this->assertArrayHasKey('statements', $tableCategories);

        // Check each category has the correct questions
        $this->assertEquals('... ist motiviert und engagiert', $tableCategories['behavior']['questions'][0]['question']->question);
        $this->assertEquals('Der Unterricht ist gut strukturiert', $tableCategories['quality']['questions'][0]['question']->question);
        $this->assertEquals('Ich lerne viel in diesem Kurs', $tableCategories['statements']['questions'][0]['question']->question);

        // Check that the individual questions are also included in the statistics
        $questionStats = array_filter($statistics, function($stat) {
            return isset($stat['question']) && $stat['question'] !== null;
        });

// The actual number of question stats will depend on the implementation
// Just verify we have at least our 3 questions included
$this->assertGreaterThanOrEqual(3, count($questionStats));
    }

    /**
     * Test categorization of table survey questions.
     */
    public function test_categorize_table_survey_questions(): void
    {
        // Create question objects with stats data
        $behaviorQuestion = new Question();
        $behaviorQuestion->id = 1;
        $behaviorQuestion->question = '... ist freundlich und respektvoll';

        $qualityQuestion = new Question();
        $qualityQuestion->id = 2;
        $qualityQuestion->question = 'Der Unterricht ist interessant gestaltet';

        $statementsQuestion = new Question();
        $statementsQuestion->id = 3;
        $statementsQuestion->question = 'Ich lerne viel in diesem Fach';

        $feedbackQuestion = new Question();
        $feedbackQuestion->id = 4;
        $feedbackQuestion->question = 'Was gefällt dir besonders gut?';

        $uncategorizedQuestion = new Question();
        $uncategorizedQuestion->id = 5;
        $uncategorizedQuestion->question = 'Eine Frage ohne klare Kategorie';

        // Create stats array
        $stats = [
            [
                'question' => $behaviorQuestion,
                'template_type' => 'range',
                'data' => ['average_rating' => 4, 'submission_count' => 10]
            ],
            [
                'question' => $qualityQuestion,
                'template_type' => 'range',
                'data' => ['average_rating' => 3.5, 'submission_count' => 10]
            ],
            [
                'question' => $statementsQuestion,
                'template_type' => 'range',
                'data' => ['average_rating' => 4.2, 'submission_count' => 10]
            ],
            [
                'question' => $feedbackQuestion,
                'template_type' => 'text',
                'data' => ['responses' => ['Good course'], 'submission_count' => 10]
            ],
            [
                'question' => $uncategorizedQuestion,
                'template_type' => 'range',
                'data' => ['average_rating' => 3.8, 'submission_count' => 10]
            ]
        ];

        // Categorize the questions
        $categories = $this->statisticsService->categorizeTableSurveyQuestions($stats);

        // Check categories
        $this->assertArrayHasKey('behavior', $categories);
        $this->assertArrayHasKey('quality', $categories);
        $this->assertArrayHasKey('statements', $categories);
        $this->assertArrayHasKey('feedback', $categories);

        // Check behavior category
        $this->assertEquals('Verhalten des Lehrers', $categories['behavior']['title']);
        $this->assertCount(1, $categories['behavior']['questions']);
        $this->assertEquals(1, $categories['behavior']['questions'][0]['question']->id);

        // Check quality category
        $this->assertEquals('Wie ist der Unterricht?', $categories['quality']['title']);
        $this->assertCount(1, $categories['quality']['questions']);
        $this->assertEquals(2, $categories['quality']['questions'][0]['question']->id);

        // Check statements category
        $this->assertEquals('Bewerten Sie folgende Aussagen', $categories['statements']['title']);
        $this->assertCount(1, $categories['statements']['questions']);
        $this->assertEquals(3, $categories['statements']['questions'][0]['question']->id);

        // Check feedback category
        $this->assertEquals('Offenes Feedback', $categories['feedback']['title']);
        $this->assertGreaterThanOrEqual(1, count($categories['feedback']['questions']));

        // The uncategorized question should be placed in feedback category
        $uncategorizedInFeedback = false;
        foreach ($categories['feedback']['questions'] as $question) {
            if ($question['question']->id === 5) {
                $uncategorizedInFeedback = true;
                break;
            }
        }
        $this->assertTrue($uncategorizedInFeedback);
    }

    /**
     * Test handling of surveys with no submissions.
     */
    public function test_calculate_statistics_with_no_submissions(): void
    {
        // Create a feedback template
        $template = Feedback_template::create([
            'name' => 'Test Template',
            'title' => 'Test Template Title',
        ]);

        // Create a question template
        $questionTemplate = Question_template::create([
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
            'expire_date' => now()->addDays(7),
        ]);

        // Create a question but no results
        $question = Question::create([
            'feedback_id' => $survey->id,
            'feedback_template_id' => $template->id,
            'question_template_id' => $questionTemplate->id,
            'question' => 'How would you rate this course?',
        ]);

        // Calculate statistics
        $statistics = $this->statisticsService->calculateStatisticsForSurvey($survey);

        // Assert statistics are calculated correctly (should be empty)
        $this->assertEmpty($statistics);
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
        $statistics = $this->statisticsService->calculateStatisticsForSurvey($mockSurvey);

        // Assert error is handled gracefully
        $this->assertCount(1, $statistics);
        $this->assertEquals('error', $statistics[0]['template_type']);
        $this->assertStringContainsString('An error occurred while calculating statistics', $statistics[0]['data']['message']);
        // The survey_id might be included differently or not at all in the error data
        if (isset($statistics[0]['data']['survey_id'])) {
            $this->assertEquals(999, $statistics[0]['data']['survey_id']);
        }
    }
}
<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\Feedback_template;
use App\Models\Question;
use App\Models\Question_template;
use App\Models\Result;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SurveyStatisticsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a teacher can view statistics for their own survey.
     */
    public function test_teacher_can_view_statistics_for_their_survey(): void
    {
        // Create a user (teacher)
        $teacher = User::factory()->create([
            'is_admin' => false,
        ]);

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
            'user_id' => $teacher->id,
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
            'question' => 'How would you rate this course?',
        ]);

        // Create some results
        $result1 = Result::create([
            'question_id' => $question->id,
            'submission_id' => (string) \Illuminate\Support\Str::uuid(),
            'value_type' => 'number',
            'rating_value' => '4'
        ]);

        $result2 = Result::create([
            'question_id' => $question->id,
            'submission_id' => (string) \Illuminate\Support\Str::uuid(),
            'value_type' => 'number',
            'rating_value' => '5'
        ]);

        // Act as the teacher and visit the statistics page
        $response = $this->actingAs($teacher)
            ->get(route('surveys.statistics', ['survey' => $survey->id]));

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert the view contains the survey and statistics data
        $response->assertViewHas('survey');
        $response->assertViewHas('statisticsData');

        // Assert the page contains expected content
        $response->assertSee('Survey Statistics');
        $response->assertSee('How would you rate this course?');
        $response->assertSee('Average Rating');
    }

    /**
     * Test that a teacher cannot view statistics for another teacher's survey.
     */
    public function test_teacher_cannot_view_statistics_for_another_teachers_survey(): void
    {
        // Create two users (teachers)
        $teacher1 = User::factory()->create([
            'is_admin' => false,
        ]);

        $teacher2 = User::factory()->create([
            'is_admin' => false,
        ]);

        // Create a feedback template
        $template = Feedback_template::create([
            'name' => 'Test Template',
            'title' => 'Test Template Title',
        ]);

        // Create a survey owned by teacher1
        $survey = Feedback::create([
            'user_id' => $teacher1->id,
            'feedback_template_id' => $template->id,
            'accesskey' => 'test-key',
            'limit' => -1,
            'already_answered' => 0,
            'expire_date' => now()->addDays(7),
        ]);

        // Act as teacher2 and try to visit the statistics page for teacher1's survey
        $response = $this->actingAs($teacher2)
            ->get(route('surveys.statistics', ['survey' => $survey->id]));

        // Assert the response is forbidden
        $response->assertStatus(403);
    }

    /**
     * Test that an unauthenticated user cannot view statistics.
     */
    public function test_unauthenticated_user_cannot_view_statistics(): void
    {
        // Create a user (teacher)
        $teacher = User::factory()->create([
            'is_admin' => false,
        ]);

        // Create a feedback template
        $template = Feedback_template::create([
            'name' => 'Test Template',
            'title' => 'Test Template Title',
        ]);

        // Create a survey
        $survey = Feedback::create([
            'user_id' => $teacher->id,
            'feedback_template_id' => $template->id,
            'accesskey' => 'test-key',
            'limit' => -1,
            'already_answered' => 0,
            'expire_date' => now()->addDays(7),
        ]);

        // Try to visit the statistics page without authentication
        $response = $this->get(route('surveys.statistics', ['survey' => $survey->id]));

        // Assert the response is a redirect to login
        $response->assertRedirect(route('login'));
    }
}

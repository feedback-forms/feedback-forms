<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\FeedbackTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SurveyNamingTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Test that a survey can be created with a name.
     */
    public function test_survey_can_be_created_with_name(): void
    {
        // Create a template
        $template = FeedbackTemplate::create([
            'name' => 'templates.feedback.test'
        ]);

        // Create a user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true
        ]);

        // Create a survey with a name
        $surveyName = 'Test Survey Name';
        $survey = Feedback::create([
            'name' => $surveyName,
            'user_id' => $user->id,
            'feedback_template_id' => $template->id,
            'accesskey' => 'TESTKEY1',
            'limit' => 100,
            'expire_date' => now()->addDays(30),
            'status' => 'running',
            'school_year' => '2023-2024',
            'department' => 'IT',
            'grade_level' => '10',
            'class' => '10A',
            'subject' => 'Math'
        ]);

        // Assert the survey was created with the correct name
        $this->assertDatabaseHas('feedback', [
            'id' => $survey->id,
            'name' => $surveyName
        ]);

        // Test updating the name
        $newName = 'Updated Survey Name';
        $survey->update(['name' => $newName]);

        // Assert the name was updated
        $this->assertDatabaseHas('feedback', [
            'id' => $survey->id,
            'name' => $newName
        ]);
    }
}

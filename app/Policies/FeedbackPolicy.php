<?php

namespace App\Policies;

use App\Models\Feedback;
use App\Models\User;

class FeedbackPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the user owns the survey/feedback.
     */
    public function ownsSurvey(User $user, Feedback $feedback): bool
    {
        return $feedback->user_id === $user->id;
    }
}

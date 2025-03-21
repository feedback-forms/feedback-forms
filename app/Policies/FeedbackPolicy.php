<?php

namespace App\Policies;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Auth\Access\Response;

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
     * Determine if the user can view any surveys.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view surveys list
    }

    /**
     * Determine if the user can view the survey.
     */
    public function view(User $user, Feedback $feedback): bool
    {
        return $feedback->user_id === $user->id;
    }

    /**
     * Determine if the user can view statistics for the survey.
     */
    public function viewStatistics(User $user, Feedback $feedback): bool
    {
        return $feedback->user_id === $user->id;
    }

    /**
     * Determine if the user can create surveys.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create surveys
    }

    /**
     * Determine if the user can update the survey.
     */
    public function update(User $user, Feedback $feedback): bool
    {
        return $feedback->user_id === $user->id;
    }

    /**
     * Determine if the user can delete the survey.
     */
    public function delete(User $user, Feedback $feedback): bool
    {
        return $feedback->user_id === $user->id;
    }

    /**
     * Determine if the user can restore the survey.
     */
    public function restore(User $user, Feedback $feedback): bool
    {
        return $feedback->user_id === $user->id;
    }

    /**
     * Determine if the user can permanently delete the survey.
     */
    public function forceDelete(User $user, Feedback $feedback): bool
    {
        return $feedback->user_id === $user->id;
    }

    /**
     * Determine if the user owns the survey/feedback.
     *
     * @deprecated Use view(), update(), or delete() instead
     */
    public function ownsSurvey(User $user, Feedback $feedback): bool
    {
        return $feedback->user_id === $user->id;
    }
}

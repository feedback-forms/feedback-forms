<?php

namespace App\Repositories;

use App\Models\Feedback;
use App\Exceptions\SurveyNotAvailableException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeedbackRepository
{
    /**
     * @var Feedback
     */
    private $feedback;

    /**
     * Constructor
     *
     * @param Feedback $feedback
     */
    public function __construct(Feedback $feedback)
    {
        $this->feedback = $feedback;
    }

    /**
     * Get all feedback surveys
     *
     * @return Collection
     */
    public function get()
    {
        return $this->feedback->get();
    }

    /**
     * Find a survey by ID
     *
     * @param int $id
     * @return Feedback|null
     */
    public function find(int $id)
    {
        return $this->feedback->find($id);
    }

    /**
     * Find a survey by ID with specified relations
     *
     * @param int $id
     * @param array $relations
     * @return Feedback|null
     */
    public function findWithRelations(int $id, array $relations = [])
    {
        return $this->feedback->with($relations)->find($id);
    }

    /**
     * Find a survey by access key
     *
     * @param string $accessKey
     * @return Feedback|null
     */
    public function findByAccessKey(string $accessKey)
    {
        return $this->feedback->where('accesskey', $accessKey)->first();
    }

    /**
     * Create a new survey
     *
     * @param array $data
     * @return Feedback
     */
    public function create(array $data)
    {
        return $this->feedback->create($data);
    }

    /**
     * Update a survey
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data)
    {
        return $this->feedback->where('id', $id)->update($data);
    }

    /**
     * Delete a survey
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        return $this->feedback->where('id', $id)->delete();
    }

    /**
     * Get surveys for a specific user
     *
     * @param int $userId
     * @return Collection
     */
    public function getForUser(int $userId)
    {
        return $this->feedback->where('user_id', $userId)->get();
    }

    /**
     * Generate a unique access key for a survey
     *
     * @return string
     */
    public function generateUniqueAccessKey(): string
    {
        do {
            $key = strtoupper(substr(md5(uniqid()), 0, 8));
            $formattedKey = substr($key, 0, 4) . '-' . substr($key, 4, 4);
        } while ($this->feedback->where('accesskey', $formattedKey)->exists());

        return $formattedKey;
    }

    /**
     * Check if a survey can be answered (not expired, within limits)
     *
     * @param Feedback $survey
     * @return bool
     * @throws SurveyNotAvailableException If the survey cannot be answered
     */
    public function canBeAnswered(Feedback $survey): bool
    {
        if ($survey->expire_date < Carbon::now()) {
            throw new SurveyNotAvailableException(
                __('surveys.survey_expired')
            );
        }

        if ($survey->limit > 0 && $survey->submission_count >= $survey->limit) {
            throw new SurveyNotAvailableException(
                __('surveys.survey_limit_reached')
            );
        }

        return true;
    }

    /**
     * Update survey status
     *
     * @param Feedback $survey
     * @param string $status
     * @return bool
     */
    public function updateStatus(Feedback $survey, string $status): bool
    {
        return $survey->update(['status' => $status]);
    }

    /**
     * Get all surveys with their questions
     *
     * @return Collection
     */
    public function getAllWithQuestions()
    {
        return $this->feedback->with('questions')->get();
    }

    /**
     * Get surveys with filtering options
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getWithFilters(array $filters = [])
    {
        $query = $this->feedback->query();

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['school_year_id'])) {
            $query->where('school_year_id', $filters['school_year_id']);
        }

        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (isset($filters['grade_level_id'])) {
            $query->where('grade_level_id', $filters['grade_level_id']);
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query;
    }

    /**
     * Get the count of surveys with filtering options
     *
     * @param array $filters
     * @return int
     */
    public function countWithFilters(array $filters = [])
    {
        return $this->getWithFilters($filters)->count();
    }
}
<?php

namespace App\Repositories;

use App\Models\Feedback;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Repository for feedback surveys data access
 *
 * This class follows the repository pattern to abstract all data access operations
 * for the Feedback model. It adheres to the single responsibility principle by focusing
 * exclusively on data access without mixing in business logic.
 */
class FeedbackRepository
{
    /**
     * @var Feedback
     */
    private $model;

    /**
     * Constructor
     *
     * @param Feedback $model The Feedback model
     */
    public function __construct(Feedback $model)
    {
        $this->model = $model;
    }

    /**
     * Get all feedback surveys
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->model->get();
    }

    /**
     * Find a survey by ID
     *
     * @param int $id
     * @return Feedback|null
     */
    public function find(int $id): ?Feedback
    {
        return $this->model->find($id);
    }

    /**
     * Find a survey by ID with specified relations
     *
     * @param int $id
     * @param array $relations
     * @return Feedback|null
     */
    public function findWithRelations(int $id, array $relations = []): ?Feedback
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * Find a survey by access key
     *
     * @param string $accessKey
     * @return Feedback|null
     */
    public function findByAccessKey(string $accessKey): ?Feedback
    {
        return $this->model->where('accesskey', $accessKey)->first();
    }

    /**
     * Check if an access key exists
     *
     * @param string $accessKey
     * @return bool
     */
    public function accessKeyExists(string $accessKey): bool
    {
        return $this->model->where('accesskey', $accessKey)->exists();
    }

    /**
     * Create a new survey
     *
     * @param array $data
     * @return Feedback
     */
    public function create(array $data): Feedback
    {
        return $this->model->create($data);
    }

    /**
     * Update a survey
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        return $this->model->where('id', $id)->update($data);
    }

    /**
     * Update a survey model instance
     *
     * @param Feedback $survey
     * @param array $data
     * @return bool
     */
    public function updateModel(Feedback $survey, array $data): bool
    {
        return $survey->update($data);
    }

    /**
     * Delete a survey
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->model->where('id', $id)->delete();
    }

    /**
     * Get surveys for a specific user
     *
     * @param int $userId
     * @return Collection
     */
    public function getForUser(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->get();
    }

    /**
     * Get surveys with filtering options and optional eager loading
     *
     * @param array $filters Array of filter criteria
     * @param array $relations Array of relationships to eager load
     * @return Builder
     */
    public function getWithFilters(array $filters = [], array $relations = []): Builder
    {
        $query = $this->model->query();

        // Apply eager loading if relations are specified
        if (!empty($relations)) {
            $query->with($relations);
        }

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
     * Get all surveys with their questions
     *
     * @return Collection
     */
    public function getAllWithQuestions(): Collection
    {
        return $this->getAllWithQuestionsAndRelations();
    }

    /**
     * Get all surveys with their questions and optional additional relations
     *
     * @param array $additionalRelations Additional relationships to eager load
     * @return Collection
     */
    public function getAllWithQuestionsAndRelations(array $additionalRelations = []): Collection
    {
        $relations = array_merge(['questions'], $additionalRelations);
        return $this->model->with($relations)->get();
    }

    /**
     * Get the count of surveys with filtering options
     *
     * @param array $filters
     * @return int
     */
    public function countWithFilters(array $filters = []): int
    {
        // We don't need to eager load relations for counting
        return $this->getWithFilters($filters)->count();
    }

    /**
     * Generate a query for upcoming surveys
     *
     * @return Builder
     */
    public function getUpcomingSurveysQuery(): Builder
    {
        return $this->model->where('expire_date', '>', now());
    }

    /**
     * Generate a query for active surveys (not expired and with responses available)
     *
     * @return Builder
     */
    public function getActiveSurveysQuery(): Builder
    {
        return $this->model->where('expire_date', '>', now())
            ->where(function ($query) {
                $query->where('limit', '<=', 0) // No limit
                    ->orWhereRaw('submission_count < `limit`'); // Under limit
            });
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasManyThrough};
use Illuminate\Support\Facades\DB;

class Feedback extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'user_id',
        'feedback_template_id',
        'accesskey',
        'limit',
        'expire_date',
        'status',
        'school_year_id',
        'department_id',
        'grade_level_id',
        'school_class_id',
        'subject_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expire_date' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'submission_count',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the feedback template that this feedback belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function feedbackTemplate(): BelongsTo
    {
        return $this->belongsTo(FeedbackTemplate::class);
    }

    /**
     * @deprecated Use feedbackTemplate() instead
     */
    public function feedback_template(): BelongsTo
    {
        return $this->feedbackTemplate();
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    /**
     * Get all results associated with this feedback through its questions.
     */
    public function results(): HasManyThrough
    {
        return $this->hasManyThrough(Result::class, Question::class);
    }

    public function year(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    /**
     * Get the grade level that this feedback belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id', 'id');
    }

    /**
     * @deprecated Use gradeLevel() instead
     */
    public function grade_level(): BelongsTo
    {
        return $this->gradeLevel();
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id', 'id');
    }

    /**
     * A caching key for the submission count to avoid redundant queries
     *
     * @var string
     */
    private const SUBMISSION_COUNT_CACHE_KEY = 'feedback_submission_count';

    /**
     * Get the base query for submissions related to this feedback
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function getSubmissionsBaseQuery()
    {
        return DB::table('results')
            ->join('questions', 'results.question_id', '=', 'questions.id')
            ->where('questions.feedback_id', $this->id);
    }

    /**
     * Get the number of unique submissions for this feedback
     * This implementation uses caching to avoid redundant queries
     *
     * @return int
     */
    public function getSubmissionCountAttribute()
    {
        // Use Laravel's remember() method to cache the result
        return cache()->remember(
            self::SUBMISSION_COUNT_CACHE_KEY . ":{$this->id}",
            now()->addMinutes(10), // Cache for 10 minutes
            function () {
                return $this->getSubmissionsBaseQuery()
                    ->distinct('results.submission_id')
                    ->count('results.submission_id');
            }
        );
    }

    /**
     * Get all unique submission IDs for this feedback
     * Uses query caching to improve performance
     *
     * @return array
     */
    public function getUniqueSubmissionIdsAttribute()
    {
        return cache()->remember(
            "feedback_submission_ids:{$this->id}",
            now()->addMinutes(10), // Cache for 10 minutes
            function () {
                return $this->getSubmissionsBaseQuery()
                    ->distinct('results.submission_id')
                    ->pluck('results.submission_id')
                    ->toArray();
            }
        );
    }

    /**
     * Get a query builder for the unique submissions of this feedback
     *
     * This method provides a cleaner API that mimics Laravel's relationship methods
     * while still using the actual data model (submissions are just distinct submission_ids in results)
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function submissions()
    {
        return $this->getSubmissionsBaseQuery()
            ->select('results.submission_id')
            ->distinct();
    }

    /**
     * Get the count of unique submissions for this survey.
     *
     * @deprecated Use submission_count attribute instead to take advantage of caching
     * @return int
     */
    public function getUniqueSubmissionsCount(): int
    {
        return $this->submission_count;
    }
}

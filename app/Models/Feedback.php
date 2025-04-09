<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasManyThrough};
use Illuminate\Support\Facades\DB;

class Feedback extends Model
{
    use HasFactory;

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
        'already_answered',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function feedback_template(): BelongsTo
    {
        return $this->belongsTo(Feedback_template::class);
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

    public function grade_level(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id', 'id');
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
     *
     * @return int
     */
    public function getSubmissionCountAttribute()
    {
        return $this->getSubmissionsBaseQuery()
            ->distinct('results.submission_id')
            ->count('results.submission_id');
    }

    /**
     * Get the already_answered attribute dynamically
     *
     * @return int
     */
    public function getAlreadyAnsweredAttribute()
    {
        return $this->submission_count;
    }

    /**
     * Get all unique submission IDs for this feedback
     *
     * @return array
     */
    public function getUniqueSubmissionIdsAttribute()
    {
        return $this->getSubmissionsBaseQuery()
            ->distinct('results.submission_id')
            ->pluck('results.submission_id')
            ->toArray();
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
     * @return int
     */
    public function getUniqueSubmissionsCount(): int
    {
        return $this->getSubmissionsBaseQuery()
            ->distinct('results.submission_id')
            ->count('results.submission_id');
    }
}

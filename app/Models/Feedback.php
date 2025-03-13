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
        'already_answered',
        'expire_date',
        'status',
        'school_year',
        'department',
        'grade_level',
        'class',
        'subject'
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

    /**
     * Get the number of unique submissions for this feedback
     *
     * @return int
     */
    public function getSubmissionCountAttribute()
    {
        return DB::table('results')
            ->join('questions', 'results.question_id', '=', 'questions.id')
            ->where('questions.feedback_id', $this->id)
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
        return DB::table('results')
            ->join('questions', 'results.question_id', '=', 'questions.id')
            ->where('questions.feedback_id', $this->id)
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
        return DB::table('results')
            ->join('questions', 'results.question_id', '=', 'questions.id')
            ->where('questions.feedback_id', $this->id)
            ->select('results.submission_id')
            ->distinct();
    }
}

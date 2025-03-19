<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Question extends Model
{
    protected $fillable = [
        'feedback_template_id',
        'question_template_id',
        'feedback_id',
        'question',
        'order',
        'category'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'order' => 'integer',
    ];

    public function feedback_template(): BelongsTo
    {
        return $this->belongsTo(Feedback_template::class);
    }

    public function feedback(): BelongsTo
    {
        return $this->belongsTo(Feedback::class);
    }

    public function question_template(): BelongsTo
    {
        return $this->belongsTo(Question_template::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    /**
     * Get the latest result for this question.
     */
    public function latestResult()
    {
        return $this->results()->latest()->first();
    }

    /**
     * Get all results for a specific submission.
     */
    public function submissionResults($submissionId)
    {
        return $this->results()->where('submission_id', $submissionId)->get();
    }
}

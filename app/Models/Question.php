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

    /**
     * Get the feedback template that this question belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function feedbackTemplate(): BelongsTo
    {
        return $this->belongsTo(FeedbackTemplate::class, 'feedback_template_id');
    }

    /**
     * @deprecated Use feedbackTemplate() instead
     */
    public function feedback_template(): BelongsTo
    {
        return $this->feedbackTemplate();
    }

    public function feedback(): BelongsTo
    {
        return $this->belongsTo(Feedback::class);
    }

    /**
     * Get the question template that this question belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function questionTemplate(): BelongsTo
    {
        return $this->belongsTo(QuestionTemplate::class, 'question_template_id');
    }

    /**
     * @deprecated Use questionTemplate() instead
     */
    public function question_template(): BelongsTo
    {
        return $this->questionTemplate();
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

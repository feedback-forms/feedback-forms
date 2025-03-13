<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    protected $fillable = [
        'question_id',
        'submission_id',
        'value_type',
        'rating_value'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'submission_id' => 'string',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Scope a query to only include results from a specific submission.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $submissionId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSubmission($query, $submissionId)
    {
        return $query->where('submission_id', $submissionId);
    }
}

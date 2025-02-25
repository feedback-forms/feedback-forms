<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};

class Question extends Model
{
    protected $fillable = [
        'feedback_template_id',
        'question_template_id',
        'feedback_id',
        'question'
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

    public function result(): HasOne
    {
        return $this->hasOne(Result::class);
    }
}

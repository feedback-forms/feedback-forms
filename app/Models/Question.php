<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};
class Question extends Model
{
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

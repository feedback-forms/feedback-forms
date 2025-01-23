<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Feedback extends Model
{
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
        $this->hasMany(Question::class);
    }
}

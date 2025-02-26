<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    protected $fillable = [
        'question_id',
        'value'
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}

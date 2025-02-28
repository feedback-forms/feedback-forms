<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Feedback extends Model
{
    protected $fillable = [
        'user_id',
        'feedback_template_id',
        'accesskey',
        'limit',
        'already_answered',
        'expire_date',
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
        return $this->hasMany(Question::class);
    }
}

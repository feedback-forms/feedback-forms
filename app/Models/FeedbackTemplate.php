<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasOne, HasMany};

class FeedbackTemplate extends Model
{
    use HasFactory;

    protected $table = 'feedback_templates';

    protected $fillable = [
        'name',
        'title'
    ];

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
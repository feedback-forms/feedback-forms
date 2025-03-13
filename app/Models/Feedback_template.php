<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasOne, HasMany};

class Feedback_template extends Model
{
    use HasFactory;

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

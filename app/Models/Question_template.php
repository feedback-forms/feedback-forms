<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question_template extends Model
{
    protected $fillable = [
        'type',
        'min_value',
        'max_value'
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}

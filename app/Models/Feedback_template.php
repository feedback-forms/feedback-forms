<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Feedback_template extends Model
{
    public function feedbacks(): HasMany 
    {
        return $this->hasMany(Feedback::class);
    }    

    public function questions(): HasMany
    {
        return $this->hasMany(Question:class);
    }
}

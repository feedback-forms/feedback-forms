<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    public function question(): HasOne
    {
        return $this->hasOne(Question::class);
    }
}

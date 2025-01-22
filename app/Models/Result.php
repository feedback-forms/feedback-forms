<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Result extends Model
{
    public function question(): HasOne
    {
        return $this->hasOne(Question::class);
    }
}

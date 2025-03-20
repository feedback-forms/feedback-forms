<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolYear extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }
}

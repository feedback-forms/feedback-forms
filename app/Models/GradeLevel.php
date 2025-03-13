<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GradeLevel extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'level', 'is_active'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function schoolClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }
}
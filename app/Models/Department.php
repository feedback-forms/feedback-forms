<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['code', 'name', 'is_active'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }
}
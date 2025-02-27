<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    protected $fillable = ['name', 'is_active'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
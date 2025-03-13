<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolYear extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'is_active'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
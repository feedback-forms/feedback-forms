<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InviteToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];
}
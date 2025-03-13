<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registerkey extends Model
{
    const KEY_REGEX = '/^[A-Z0-9]{4}-[A-Z0-9]{4}$/';

    protected $fillable = [
        'expire_at',
        'code'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expireAt(): Attribute
    {
        return Attribute::make(
            get: fn ($expiredAt) => $expiredAt ? Carbon::parse($expiredAt) : null
        );
    }
}

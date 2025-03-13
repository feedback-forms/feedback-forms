<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Result extends Model
{
    protected $fillable = [
        'question_id',
        'submission_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the response value for this result.
     */
    public function responseValue(): HasOne
    {
        return $this->hasOne(ResponseValue::class);
    }

    /**
     * Get the value attribute dynamically.
     * This maintains backward compatibility with existing code that uses $result->value.
     */
    public function getValueAttribute()
    {
        $responseValue = $this->responseValue;

        if ($responseValue) {
            return $responseValue->value;
        }

        // For backward compatibility - try to get the value from the database column if it exists
        $value = $this->attributes['value'] ?? null;

        if (is_string($value)) {
            try {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            } catch (\Exception $e) {
                // If there's an error decoding, just return the original value
            }
        }

        return $value;
    }
}

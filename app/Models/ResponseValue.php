<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResponseValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'result_id',
        'question_template_type',
        'range_value',
        'text_value',
        'json_value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'json_value' => 'json',
    ];

    /**
     * Get the result that owns this response value.
     */
    public function result(): BelongsTo
    {
        return $this->belongsTo(Result::class);
    }

    /**
     * Get the value attribute dynamically based on question_template_type.
     */
    public function getValueAttribute()
    {
        switch ($this->question_template_type) {
            case 'range':
                return $this->range_value;
            case 'textarea':
            case 'text': // Assuming 'text' type might also store in text_value
                return $this->text_value;
            case 'checkboxes':
            case 'target':
            case 'table': // Add other complex types as needed
                return $this->json_value;
            default:
                return $this->text_value ?? $this->json_value; // Default to text or json if type is unknown
        }
    }
}

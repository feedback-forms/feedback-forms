<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Notifications\CustomVerifyEmail;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'registerkey_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        // Skip verification in non-production environments
        if (app()->environment('local', 'testing')) {
            $this->markEmailAsVerified();
            return;
        }

        if (!$this->email) {
            return;
        }


        try {
            $this->notify(new CustomVerifyEmail);
            Log::info('Verification email queued successfully', [
                'user_id' => $this->id,
                'email' => $this->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue verification email', [
                'user_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function registerkey(): BelongsTo
    {
        return $this->belongsTo(Registerkey::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }
}

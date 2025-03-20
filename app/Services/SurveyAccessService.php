<?php

namespace App\Services;

use App\Exceptions\InvalidAccessKeyException;
use App\Models\Feedback;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SurveyAccessService
{
    /**
     * Number of characters in the access key (before formatting)
     */
    const ACCESS_KEY_LENGTH = 12;

    /**
     * Format: Group size for formatting the access key (XXXX-XXXX-XXXX)
     */
    const ACCESS_KEY_GROUP_SIZE = 4;

    /**
     * Maximum attempts allowed within the time window
     */
    const MAX_ACCESS_ATTEMPTS = 10;

    /**
     * Time window for rate limiting (in minutes)
     */
    const RATE_LIMIT_WINDOW = 10;

    /**
     * Generate a cryptographically secure unique access key
     *
     * @return string Formatted access key in the pattern XXXX-XXXX-XXXX
     */
    public function generateAccessKey(): string
    {
        do {
            // Generate a cryptographically secure random string
            $rawKey = Str::random(self::ACCESS_KEY_LENGTH);

            // Convert to uppercase for readability
            $rawKey = strtoupper($rawKey);

            // Format the key with hyphens for better readability (XXXX-XXXX-XXXX)
            $formattedKey = $this->formatAccessKey($rawKey);

            // Check if this key already exists in the database
        } while (Feedback::where('accesskey', $formattedKey)->exists());

        return $formattedKey;
    }

    /**
     * Format a raw access key with hyphens for better readability
     *
     * @param string $rawKey The raw access key string
     * @return string The formatted key with hyphens
     */
    private function formatAccessKey(string $rawKey): string
    {
        $parts = [];
        $groupSize = self::ACCESS_KEY_GROUP_SIZE;

        for ($i = 0; $i < strlen($rawKey); $i += $groupSize) {
            $parts[] = substr($rawKey, $i, $groupSize);
        }

        return implode('-', $parts);
    }

    /**
     * Validate the access key and return the associated survey
     * Includes protection against brute force attacks with rate limiting
     *
     * @param string $accessKey The access key to validate
     * @param string $ipAddress The IP address of the requester (for rate limiting)
     * @return Feedback The survey associated with the access key
     * @throws InvalidAccessKeyException If the access key is invalid or rate limited
     */
    public function validateAccessKey(string $accessKey, string $ipAddress): Feedback
    {
        // Check for rate limiting
        if ($this->isRateLimited($ipAddress)) {
            throw new InvalidAccessKeyException(
                'Too many invalid attempts. Please try again later.',
                [
                    'ip_address' => $ipAddress,
                    'rate_limited' => true
                ]
            );
        }

        // Find the survey with this access key
        $survey = Feedback::where('accesskey', $accessKey)->first();

        if (!$survey) {
            // Increment the failed attempt counter
            $this->recordFailedAttempt($ipAddress);

            throw new InvalidAccessKeyException(
                __('surveys.invalid_access_key'),
                [
                    'attempted_key' => $accessKey,
                    'ip_address' => $ipAddress
                ]
            );
        }

        // Reset failed attempts on successful key validation
        $this->resetFailedAttempts($ipAddress);

        return $survey;
    }

    /**
     * Check if the IP address is currently rate limited
     *
     * @param string $ipAddress The IP address to check
     * @return bool True if rate limited, false otherwise
     */
    private function isRateLimited(string $ipAddress): bool
    {
        $cacheKey = "survey_access:attempts:{$ipAddress}";
        $attempts = Cache::get($cacheKey, 0);

        return $attempts >= self::MAX_ACCESS_ATTEMPTS;
    }

    /**
     * Record a failed access attempt for the given IP address
     *
     * @param string $ipAddress The IP address of the requester
     * @return void
     */
    private function recordFailedAttempt(string $ipAddress): void
    {
        $cacheKey = "survey_access:attempts:{$ipAddress}";
        $attempts = Cache::get($cacheKey, 0);

        // Increment the counter
        Cache::put(
            $cacheKey,
            $attempts + 1,
            now()->addMinutes(self::RATE_LIMIT_WINDOW)
        );
    }

    /**
     * Reset failed attempts counter for the given IP address
     *
     * @param string $ipAddress The IP address of the requester
     * @return void
     */
    private function resetFailedAttempts(string $ipAddress): void
    {
        $cacheKey = "survey_access:attempts:{$ipAddress}";
        Cache::forget($cacheKey);
    }
}
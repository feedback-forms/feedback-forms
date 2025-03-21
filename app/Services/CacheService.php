<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Service for standardized caching operations across the application
 *
 * This service provides a consistent approach to caching with:
 * - Standardized key naming conventions
 * - Configurable expiration times
 * - Support for cache tags for efficient invalidation
 * - Ability to globally enable/disable caching for testing
 */
class CacheService
{
    /**
     * Cache durations in minutes
     */
    public const DURATION_SHORT = 5;  // 5 minutes
    public const DURATION_MEDIUM = 30; // 30 minutes
    public const DURATION_LONG = 120;  // 2 hours

    /**
     * Cache tag prefixes for different types of data
     */
    public const TAG_SURVEY = 'survey';
    public const TAG_STATISTICS = 'statistics';
    public const TAG_SUBMISSIONS = 'submissions';

    /**
     * Flag to enable/disable caching globally (useful for testing)
     *
     * @var bool
     */
    protected $cacheEnabled;

    /**
     * Constructor
     */
    public function __construct()
    {
        // We can default this to true, or load from config if needed
        $this->cacheEnabled = config('cache.enabled', true);
    }

    /**
     * Remember a value in the cache
     *
     * @param string $key The cache key
     * @param int $duration Duration in minutes
     * @param callable $callback Function to generate the value if not in cache
     * @param array $tags Optional cache tags for invalidation
     * @return mixed The cached or calculated value
     */
    public function remember(string $key, int $duration, callable $callback, array $tags = [])
    {
        if (!$this->cacheEnabled) {
            return $callback();
        }

        // Use tagging if supported by the cache driver and tags are provided
        if (!empty($tags) && method_exists(Cache::store(), 'tags')) {
            return Cache::tags($tags)->remember($key, now()->addMinutes($duration), $callback);
        }

        return Cache::remember($key, now()->addMinutes($duration), $callback);
    }

    /**
     * Build a standardized cache key
     *
     * @param string $prefix The key prefix
     * @param mixed ...$parts Additional parts to include in the key
     * @return string The constructed cache key
     */
    public function buildKey(string $prefix, ...$parts): string
    {
        return $prefix . ':' . implode(':', array_map(function ($part) {
            return (string) $part;
        }, $parts));
    }

    /**
     * Clear cache by tags
     *
     * @param array $tags The tags to clear
     * @return bool True if the operation succeeded
     */
    public function clearByTags(array $tags): bool
    {
        if (method_exists(Cache::store(), 'tags')) {
            Cache::tags($tags)->flush();
            return true;
        }

        return false;
    }

    /**
     * Clear cache for a specific survey
     *
     * @param int $surveyId The survey ID
     * @return bool True if the operation succeeded
     */
    public function clearSurveyCache(int $surveyId): bool
    {
        return $this->clearByTags([self::TAG_SURVEY . ':' . $surveyId]);
    }

    /**
     * Clear all statistics caches
     *
     * @return bool True if the operation succeeded
     */
    public function clearStatisticsCache(): bool
    {
        return $this->clearByTags([self::TAG_STATISTICS]);
    }

    /**
     * Enable or disable caching
     *
     * @param bool $enabled True to enable caching, false to disable
     */
    public function setCacheEnabled(bool $enabled): void
    {
        $this->cacheEnabled = $enabled;
    }

    /**
     * Check if caching is enabled
     *
     * @return bool True if caching is enabled
     */
    public function isCacheEnabled(): bool
    {
        return $this->cacheEnabled;
    }
}
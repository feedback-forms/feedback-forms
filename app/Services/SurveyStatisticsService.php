<?php

namespace App\Services;

use App\Models\Feedback;
use App\Exceptions\ServiceException;

/**
 * Service responsible for calculating survey statistics
 *
 * This service handles the calculation of statistics for surveys,
 * delegating to the StatisticsService for the actual calculations.
 * It implements caching to avoid expensive recalculations.
 */
class SurveyStatisticsService
{
    /**
     * @var StatisticsService
     */
    protected $statisticsService;

    /**
     * @var CacheService
     */
    protected $cacheService;

    /**
     * Constructor to initialize dependencies
     *
     * @param StatisticsService $statisticsService
     * @param CacheService $cacheService
     */
    public function __construct(
        StatisticsService $statisticsService,
        CacheService $cacheService
    ) {
        $this->statisticsService = $statisticsService;
        $this->cacheService = $cacheService;
    }

    /**
     * Calculate statistics for a survey
     *
     * This method adds a service-level caching layer on top of the
     * StatisticsService calculations. The StatisticsService already implements
     * caching internally, but this provides an additional layer that can use
     * different cache durations and invalidation strategies.
     *
     * @param Feedback $survey The survey to calculate statistics for
     * @return array An array of statistics data for each question
     * @throws ServiceException If there's an error during statistics calculation
     */
    public function calculateStatisticsForSurvey(Feedback $survey): array
    {
        try {
            // We'll use a longer cache duration at this service level
            // This provides a "stale while revalidate" pattern where this cache might
            // still serve data while the underlying StatisticsService cache is being rebuilt
            $cacheKey = $this->cacheService->buildKey(
                'survey_statistics_service',
                $survey->id,
                $survey->updated_at->timestamp
            );

            $cacheTags = [
                $this->cacheService::TAG_STATISTICS,
                $this->cacheService::TAG_SURVEY . ':' . $survey->id
            ];

            return $this->cacheService->remember(
                $cacheKey,
                $this->cacheService::DURATION_LONG, // Using longer duration (2 hours)
                function () use ($survey) {
                    return $this->statisticsService->calculateStatisticsForSurvey($survey);
                },
                $cacheTags
            );
        } catch (\Exception $e) {
            throw ServiceException::fromException(
                $e,
                ServiceException::CATEGORY_UNEXPECTED,
                ['survey_id' => $survey->id]
            );
        }
    }

    /**
     * Clear the statistics cache for a specific survey
     *
     * @param int $surveyId The ID of the survey to clear cache for
     * @return bool True if the operation succeeded
     */
    public function clearStatisticsCache(int $surveyId): bool
    {
        return $this->cacheService->clearSurveyCache($surveyId);
    }
}
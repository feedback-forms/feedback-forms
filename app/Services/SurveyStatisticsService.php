<?php

namespace App\Services;

use App\Models\Feedback;
use App\Exceptions\ServiceException;

/**
 * Service responsible for calculating survey statistics
 *
 * This service handles the calculation of statistics for surveys,
 * delegating to the StatisticsService for the actual calculations.
 */
class SurveyStatisticsService
{
    /**
     * @var StatisticsService
     */
    protected $statisticsService;

    /**
     * Constructor to initialize dependencies
     *
     * @param StatisticsService $statisticsService
     */
    public function __construct(
        StatisticsService $statisticsService
    ) {
        $this->statisticsService = $statisticsService;
    }

    /**
     * Calculate statistics for a survey
     *
     * Delegates statistics calculation to the StatisticsService.
     *
     * @param Feedback $survey The survey to calculate statistics for
     * @return array An array of statistics data for each question
     * @throws ServiceException If there's an error during statistics calculation
     */
    public function calculateStatisticsForSurvey(Feedback $survey): array
    {
        try {
            return $this->statisticsService->calculateStatisticsForSurvey($survey);
        } catch (\Exception $e) {
            throw ServiceException::fromException(
                $e,
                ServiceException::CATEGORY_UNEXPECTED,
                ['survey_id' => $survey->id]
            );
        }
    }
}
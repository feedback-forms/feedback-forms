<?php

namespace App\Services;

use App\Models\Feedback;
use App\Exceptions\ServiceException;
use App\Exceptions\SurveyNotAvailableException;
use Illuminate\Support\Facades\Log;

/**
 * Main service for survey operations
 *
 * This service acts as a facade for survey-related operations, delegating
 * specific responsibilities to specialized services.
 */
class SurveyService
{
    /**
     * @var SurveyCreationService
     */
    protected $surveyCreationService;

    /**
     * @var SurveyResponseHandlerService
     */
    protected $surveyResponseHandlerService;

    /**
     * @var SurveyStatisticsService
     */
    protected $surveyStatisticsService;

    /**
     * @var SurveyValidationService
     */
    protected $surveyValidationService;

    /**
     * Constructor to initialize dependencies
     *
     * @param SurveyCreationService $surveyCreationService
     * @param SurveyResponseHandlerService $surveyResponseHandlerService
     * @param SurveyStatisticsService $surveyStatisticsService
     * @param SurveyValidationService $surveyValidationService
     */
    public function __construct(
        SurveyCreationService $surveyCreationService,
        SurveyResponseHandlerService $surveyResponseHandlerService,
        SurveyStatisticsService $surveyStatisticsService,
        SurveyValidationService $surveyValidationService
    ) {
        $this->surveyCreationService = $surveyCreationService;
        $this->surveyResponseHandlerService = $surveyResponseHandlerService;
        $this->surveyStatisticsService = $surveyStatisticsService;
        $this->surveyValidationService = $surveyValidationService;
    }

    /**
     * Create a new survey from template
     *
     * Delegates to SurveyCreationService.
     *
     * @param array $surveyConfig The survey configuration data
     * @param int $userId The ID of the user creating the survey
     * @return Feedback The created survey
     * @throws ServiceException If there's an error during survey creation
     */
    public function createFromTemplate(array $surveyConfig, int $userId): Feedback
    {
        return $this->surveyCreationService->createFromTemplate($surveyConfig, $userId);
    }

    /**
     * Validate if survey can be answered (not expired, within limits)
     *
     * Delegates to SurveyValidationService.
     *
     * @param Feedback $survey The survey to check
     * @return bool True if the survey can be answered
     * @throws SurveyNotAvailableException If the survey cannot be answered due to expiration or limits
     * @throws ServiceException If there's an unexpected error during validation
     */
    public function canBeAnswered(Feedback $survey): bool
    {
        try {
            return $this->surveyValidationService->canBeAnswered($survey);
        } catch (SurveyNotAvailableException $e) {
            // Log the exception with additional context
            Log::warning($e->getMessage(), [
                'survey_id' => $survey->id,
                'expire_date' => $survey->expire_date,
                'limit' => $survey->limit,
                'submission_count' => $survey->submission_count
            ]);

            // Re-throw the exception
            throw $e;
        } catch (\Exception $e) {
            // Wrap any unexpected exceptions
            throw ServiceException::fromException(
                $e,
                ServiceException::CATEGORY_UNEXPECTED,
                ['survey_id' => $survey->id]
            );
        }
    }

    /**
     * Store survey responses
     *
     * Delegates to SurveyResponseHandlerService.
     *
     * @param Feedback $survey The survey to store responses for
     * @param array $responses The responses to store
     * @return bool True if responses were stored successfully
     * @throws ServiceException If there's an error during response storage
     */
    public function storeResponses(Feedback $survey, array $responses): bool
    {
        return $this->surveyResponseHandlerService->storeResponses($survey, $responses);
    }

    /**
     * Calculate statistics for a survey
     *
     * Delegates to SurveyStatisticsService.
     *
     * @param Feedback $survey The survey to calculate statistics for
     * @return array An array of statistics data for each question
     */
    public function calculateStatisticsForSurvey(Feedback $survey): array
    {
        return $this->surveyStatisticsService->calculateStatisticsForSurvey($survey);
    }
}

<?php

namespace App\Services;

use App\Models\Feedback;
use App\Exceptions\ServiceException;

/**
 * Service responsible for handling survey responses
 *
 * This service handles the storing of responses for surveys,
 * delegating to the SurveyResponseService for the actual storage.
 */
class SurveyResponseHandlerService
{
    /**
     * @var SurveyResponseService
     */
    protected $surveyResponseService;

    /**
     * Constructor to initialize dependencies
     *
     * @param SurveyResponseService $surveyResponseService
     */
    public function __construct(
        SurveyResponseService $surveyResponseService
    ) {
        $this->surveyResponseService = $surveyResponseService;
    }

    /**
     * Store survey responses
     *
     * Delegates response storage to the SurveyResponseService.
     *
     * @param Feedback $survey The survey to store responses for
     * @param array $responses The responses to store
     * @return bool True if responses were stored successfully
     * @throws ServiceException If there's an error during response storage
     */
    public function storeResponses(Feedback $survey, array $responses): bool
    {
        try {
            return $this->surveyResponseService->storeResponses($survey, $responses);
        } catch (\Exception $e) {
            throw ServiceException::fromException(
                $e,
                ServiceException::CATEGORY_UNEXPECTED,
                ['survey_id' => $survey->id]
            );
        }
    }
}
<?php

namespace App\Services;

use App\Models\Feedback;
use App\Exceptions\SurveyNotAvailableException;
use App\Exceptions\ExceptionHandler;
use App\Services\ErrorLogger;
use Carbon\Carbon;

/**
 * Service for validating survey availability and constraints
 *
 * This service encapsulates business logic related to determining
 * whether a survey can be answered based on various criteria.
 */
class SurveyValidationService
{
    /**
     * Check if a survey can be answered (not expired, within limits)
     *
     * @param Feedback $survey
     * @return bool
     * @throws SurveyNotAvailableException If the survey cannot be answered
     */
    public function canBeAnswered(Feedback $survey): bool
    {
        if ($survey->expire_date < Carbon::now()) {
            throw SurveyNotAvailableException::expired([
                'survey_id' => $survey->id,
                'expire_date' => $survey->expire_date->toIso8601String(),
                'current_date' => Carbon::now()->toIso8601String()
            ]);
        }

        if ($survey->limit > 0 && $survey->submission_count >= $survey->limit) {
            throw SurveyNotAvailableException::limitReached([
                'survey_id' => $survey->id,
                'limit' => $survey->limit,
                'submission_count' => $survey->submission_count
            ]);
        }

        return true;
    }

    /**
     * Try to determine if a survey can be answered, wrapping exceptions in a consistent way
     *
     * @param Feedback $survey
     * @return bool True if the survey can be answered, false otherwise
     */
    public function trySurveyAvailability(Feedback $survey): bool
    {
        try {
            return ExceptionHandler::tryExecute(
                fn() => $this->canBeAnswered($survey),
                ErrorLogger::CATEGORY_USER_INPUT
            );
        } catch (SurveyNotAvailableException $e) {
            // We expect this exception, so return false
            return false;
        }
    }
}
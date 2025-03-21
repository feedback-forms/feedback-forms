<?php

namespace App\Services;

use App\Models\Feedback;
use App\Exceptions\SurveyNotAvailableException;
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
            throw new SurveyNotAvailableException(
                __('surveys.survey_expired')
            );
        }

        if ($survey->limit > 0 && $survey->submission_count >= $survey->limit) {
            throw new SurveyNotAvailableException(
                __('surveys.survey_limit_reached')
            );
        }

        return true;
    }
}
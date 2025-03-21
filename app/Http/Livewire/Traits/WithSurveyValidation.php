<?php

namespace App\Http\Livewire\Traits;

trait WithSurveyValidation
{
    /**
     * Get the validation rules for surveys.
     *
     * @return array
     */
    protected function getSurveyValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'expire_date' => 'required|date|after:now',
            'response_limit' => 'nullable|integer|min:-1',
            'school_year' => 'required|exists:school_years,id',
            'department' => 'required|exists:departments,id',
            'grade_level' => 'required|exists:grade_levels,id',
            'class' => 'required|exists:school_classes,id',
            'subject' => 'required|exists:subjects,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    protected function getSurveyValidationMessages(): array
    {
        return [
            'name.required' => __('validation.required', ['attribute' => __('surveys.name')]),
            'expire_date.required' => __('validation.required', ['attribute' => __('surveys.expire_date')]),
            'expire_date.date' => __('validation.date', ['attribute' => __('surveys.expire_date')]),
            'expire_date.after' => __('validation.after', ['attribute' => __('surveys.expire_date'), 'date' => __('surveys.now')]),
            'school_year.required' => __('validation.required', ['attribute' => __('surveys.school_year')]),
            'department.required' => __('validation.required', ['attribute' => __('surveys.department')]),
            'grade_level.required' => __('validation.required', ['attribute' => __('surveys.grade_level')]),
            'class.required' => __('validation.required', ['attribute' => __('surveys.class')]),
            'subject.required' => __('validation.required', ['attribute' => __('surveys.subject')]),
        ];
    }

    /**
     * Check if the user is authorized to update the survey.
     *
     * @param int $surveyId
     * @return bool
     */
    protected function canUpdateSurvey(int $surveyId): bool
    {
        // Get the survey instance
        $survey = \App\Models\Feedback::find($surveyId);

        // Check if the survey exists and belongs to the authenticated user
        return $survey && $survey->user_id === auth()->id();
    }
}
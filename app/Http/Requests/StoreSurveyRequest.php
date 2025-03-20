<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSurveyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only authenticated users can create surveys
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'template_id' => 'required|exists:feedback_templates,id',
            'expire_date' => 'required|date|after:now',
            'response_limit' => 'nullable|integer|min:-1',
            'school_year_id' => 'required|exists:school_years,id',
            'department_id' => 'required|exists:departments,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'school_class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'survey_data' => 'nullable|string',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Handle survey data as JSON if it exists
        if ($this->has('survey_data') && is_string($this->survey_data)) {
            $surveyData = json_decode($this->survey_data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // Store the survey data for later use
                session(['survey_data' => $surveyData]);
            }
        }
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => __('validation.required', ['attribute' => __('surveys.name')]),
            'template_id.required' => __('validation.required', ['attribute' => __('surveys.template')]),
            'template_id.exists' => __('validation.exists', ['attribute' => __('surveys.template')]),
            'expire_date.required' => __('validation.required', ['attribute' => __('surveys.expire_date')]),
            'expire_date.date' => __('validation.date', ['attribute' => __('surveys.expire_date')]),
            'expire_date.after' => __('validation.after', ['attribute' => __('surveys.expire_date'), 'date' => __('surveys.now')]),
            'school_year_id.required' => __('validation.required', ['attribute' => __('surveys.school_year')]),
            'department_id.required' => __('validation.required', ['attribute' => __('surveys.department')]),
            'grade_level_id.required' => __('validation.required', ['attribute' => __('surveys.grade_level')]),
            'school_class_id.required' => __('validation.required', ['attribute' => __('surveys.class')]),
            'subject_id.required' => __('validation.required', ['attribute' => __('surveys.subject')]),
        ];
    }
}
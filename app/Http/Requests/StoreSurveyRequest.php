<?php

namespace App\Http\Requests;

class StoreSurveyRequest extends BaseFormRequest
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
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate the name field for security concerns
            if ($this->has('name') && is_string($this->input('name'))) {
                $this->validateTextContent($validator, $this->input('name'), 'name', 255);
            }

            // Validate survey_data if it's provided (could be a JSON string)
            if ($this->has('survey_data') && is_string($this->input('survey_data'))) {
                if ($this->isValidJson($this->input('survey_data'))) {
                    // Validate as JSON for security concerns
                    $jsonData = $this->validateJsonString($validator, $this->input('survey_data'), 'survey_data');

                    if ($jsonData) {
                        // Store the validated data for later use
                        session(['survey_data' => $jsonData]);
                    }
                } else {
                    // Treat as regular text and validate
                    $this->validateTextContent(
                        $validator,
                        $this->input('survey_data'),
                        'survey_data',
                        50000 // Allowing larger limit for survey data
                    );

                    $validator->errors()->add(
                        'survey_data',
                        'The survey data must be a valid JSON string.'
                    );
                }
            }
        });
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // No longer needed as JSON validation is handled in withValidator
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
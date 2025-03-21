<?php

namespace App\Http\Requests;

use App\Models\Feedback;

class UpdateSurveyRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $survey = $this->route('survey');

        // Ensure the survey exists and belongs to the authenticated user
        if ($survey instanceof Feedback) {
            return $survey->user_id === auth()->id();
        }

        // If we're in a Livewire component or another context where the route parameter isn't available
        $surveyId = $this->input('id');
        if ($surveyId) {
            $survey = Feedback::find($surveyId);
            return $survey && $survey->user_id === auth()->id();
        }

        return false;
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

            // Check any other text fields that might contain potentially harmful content
            $textFields = ['description', 'notes', 'additional_info'];

            foreach ($textFields as $field) {
                if ($this->has($field) && is_string($this->input($field))) {
                    $maxLength = ($field === 'description') ? 2000 : 1000;
                    $this->validateTextContent($validator, $this->input($field), $field, $maxLength);
                }
            }
        });
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
}
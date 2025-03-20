<?php

namespace App\Http\Requests;

use App\Models\Feedback;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSurveyRequest extends FormRequest
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
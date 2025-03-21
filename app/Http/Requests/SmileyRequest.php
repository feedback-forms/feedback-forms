<?php

namespace App\Http\Requests;

class SmileyRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'positive' => 'required|string|max:5000',
            'negative' => 'required|string|max:5000',
            'response_data' => 'nullable|string'
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
            // Validate text content for positive and negative fields
            if ($this->has('positive') && is_string($this->input('positive'))) {
                $this->validateTextContent(
                    $validator,
                    $this->input('positive'),
                    'positive',
                    5000
                );
            }

            if ($this->has('negative') && is_string($this->input('negative'))) {
                $this->validateTextContent(
                    $validator,
                    $this->input('negative'),
                    'negative',
                    5000
                );
            }

            // Validate response_data if it's provided as JSON
            if ($this->has('response_data') && is_string($this->input('response_data'))) {
                if ($this->isValidJson($this->input('response_data'))) {
                    $this->validateJsonString($validator, $this->input('response_data'), 'response_data');
                } else {
                    $this->validateTextContent(
                        $validator,
                        $this->input('response_data'),
                        'response_data',
                        10000
                    );
                }
            }
        });
    }
}

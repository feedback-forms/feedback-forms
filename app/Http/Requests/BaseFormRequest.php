<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseFormRequest extends FormRequest
{
    /**
     * Check if a string contains potentially suspicious content
     *
     * @param string $content The content to check
     * @return bool True if suspicious content is found
     */
    protected function containsSuspiciousContent(string $content): bool
    {
        // Check for common script injection patterns
        $suspiciousPatterns = [
            '/<script\b[^>]*>/i',                    // Script tags
            '/javascript:/i',                        // JavaScript protocol
            '/on\w+\s*=\s*["\'][^"\']*["\']/i',    // Event handlers (onclick, onload, etc.)
            '/eval\s*\(/i',                          // eval()
            '/document\.(location|cookie|write)/i',  // Document manipulation
            '/<iframe\b[^>]*>/i',                    // iframes
            '/<object\b[^>]*>/i',                    // object tags
            '/<embed\b[^>]*>/i',                     // embed tags
            '/\bdata:(?:text|image)\/[a-z]*;base64/i' // Data URIs
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate text content for potential security issues
     *
     * @param \Illuminate\Validation\Validator $validator The validator instance
     * @param string $text The text to validate
     * @param string $attribute The attribute name for error messages
     * @param int $maxLength Maximum allowed length (default: 10000)
     * @return void
     */
    protected function validateTextContent($validator, string $text, string $attribute, int $maxLength = 10000): void
    {
        // Check length (prevent DoS attacks with extremely long inputs)
        if (strlen($text) > $maxLength) {
            $validator->errors()->add(
                $attribute,
                __('validation.max.string', ['attribute' => $attribute, 'max' => $maxLength])
            );
        }

        // Check for potentially malicious content
        if ($this->containsSuspiciousContent($text)) {
            $validator->errors()->add(
                $attribute,
                "The response contains potentially malicious content."
            );
        }
    }

    /**
     * Check if a string is valid JSON
     *
     * @param mixed $string The string to check
     * @return bool True if the string is valid JSON, false otherwise
     */
    protected function isValidJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Validate a JSON string for security concerns
     *
     * @param \Illuminate\Validation\Validator $validator The validator instance
     * @param string $jsonString The JSON string to validate
     * @param string $attribute The attribute name for error messages
     * @return array|null The decoded JSON data if valid, null otherwise
     */
    protected function validateJsonString($validator, string $jsonString, string $attribute): ?array
    {
        if (!$this->isValidJson($jsonString)) {
            $validator->errors()->add(
                $attribute,
                "The provided JSON is invalid."
            );
            return null;
        }

        $jsonData = json_decode($jsonString, true);

        // Validate the JSON structure is an array
        if (!is_array($jsonData)) {
            $validator->errors()->add(
                $attribute,
                "The JSON must contain a valid data structure."
            );
            return null;
        }

        // Recursively check JSON data for malicious content
        $this->validateJsonData($validator, $jsonData, $attribute);

        return $jsonData;
    }

    /**
     * Recursively validate JSON data for security concerns
     *
     * @param \Illuminate\Validation\Validator $validator The validator instance
     * @param array $data The JSON data to validate
     * @param string $attribute The attribute name for error messages
     * @param string $path The current path in the JSON structure (for nested error reporting)
     * @return void
     */
    protected function validateJsonData($validator, array $data, string $attribute, string $path = ''): void
    {
        foreach ($data as $key => $value) {
            $currentPath = $path ? "$path.$key" : $key;

            if (is_string($value)) {
                if ($this->containsSuspiciousContent($value)) {
                    $validator->errors()->add(
                        $attribute,
                        "The data contains potentially malicious content at '$currentPath'."
                    );
                }
            } else if (is_array($value)) {
                $this->validateJsonData($validator, $value, $attribute, $currentPath);
            }
        }
    }
}
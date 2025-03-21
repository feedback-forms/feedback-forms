<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Services\ErrorLogger;

/**
 * Exception thrown when a survey is not available for submission
 *
 * This exception handles various reasons why a survey might not be available:
 * - Expired survey
 * - Submission limit reached
 * - Survey closed or disabled
 */
class SurveyNotAvailableException extends Exception
{
    use LoggableException;

    /**
     * Constructor to initialize with message and context
     *
     * @param string $message The error message
     * @param array $context Additional context data
     * @param int $code The error code
     * @param \Throwable|null $previous The previous exception
     */
    public function __construct(
        string $message = '',
        array $context = [],
        int $code = 0,
        \Throwable $previous = null
    ) {
        // Use the default message if not provided
        if (empty($message)) {
            $message = __('surveys.survey_not_available');
        }

        parent::__construct($message, $code, $previous);

        // Set default category and log level
        $this->category = ErrorLogger::CATEGORY_USER_INPUT;
        $this->logLevel = ErrorLogger::LOG_LEVEL_WARNING;
        $this->context = $context;

        // Note: We don't log in constructor to avoid duplicate logging when render() is called
        // which happens with HTTP requests. This class only logs in render().
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function render(Request $request): RedirectResponse
    {
        // Log the exception when rendering the response
        $this->logException();

        return redirect()->route('welcome')
            ->with('error', $this->getMessage());
    }

    /**
     * Create an exception for an expired survey
     *
     * @param array $context Additional context data
     * @return static
     */
    public static function expired(array $context = []): self
    {
        return static::forCategory(__('surveys.survey_expired'), ErrorLogger::CATEGORY_USER_INPUT, $context);
    }

    /**
     * Create an exception for a survey that reached its submission limit
     *
     * @param array $context Additional context data
     * @return static
     */
    public static function limitReached(array $context = []): self
    {
        return static::forCategory(__('surveys.survey_limit_reached'), ErrorLogger::CATEGORY_USER_INPUT, $context);
    }
}
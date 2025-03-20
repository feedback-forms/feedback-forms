<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Services\ErrorLogger;

class SurveyNotAvailableException extends Exception
{
    /**
     * Additional context data for the error
     *
     * @var array
     */
    protected $context;

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
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get the error context
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function render(Request $request): RedirectResponse
    {
        // Use the specific message if one was provided, otherwise use the default
        $message = !empty($this->getMessage())
            ? $this->getMessage()
            : __('surveys.survey_not_available');
// Use the ErrorLogger service for structured logging
ErrorLogger::logException(
    $this,
    ErrorLogger::CATEGORY_USER_INPUT,
    ErrorLogger::LOG_LEVEL_WARNING,
    array_merge(
        ['message' => $message],
        $this->context
    )
);


        return redirect()->route('welcome')
            ->with('error', $message);
    }
}
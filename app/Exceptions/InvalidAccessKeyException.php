<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Services\ErrorLogger;

class InvalidAccessKeyException extends Exception
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

        // Log the exception when it's created
        $this->logException();
    }

    /**
     * Log the exception with appropriate level and context
     */
    protected function logException(): void
    {
        // Use the ErrorLogger service for structured logging
        ErrorLogger::logException(
            $this,
            ErrorLogger::CATEGORY_SECURITY,
            ErrorLogger::LOG_LEVEL_WARNING,
            $this->context
        );
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
        return redirect()->route('welcome')
            ->with('error', __('surveys.invalid_access_key'));
    }
}
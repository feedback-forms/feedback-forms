<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Services\ErrorLogger;

/**
 * Exception thrown when an invalid access key is provided
 *
 * This exception handles security-related issues with survey access keys
 * and provides a consistent error response.
 */
class InvalidAccessKeyException extends Exception
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
            $message = __('surveys.invalid_access_key');
        }

        parent::__construct($message, $code, $previous);

        // Set default category and log level
        $this->category = ErrorLogger::CATEGORY_SECURITY;
        $this->logLevel = ErrorLogger::LOG_LEVEL_WARNING;
        $this->context = $context;

        // Log the exception when it's created
        $this->logException();
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
            ->with('error', $this->getMessage());
    }

    /**
     * Create a rate-limited access exception
     *
     * @param string $message The error message
     * @param array $context Additional context data
     * @return static
     */
    public static function rateLimited(string $message = '', array $context = []): self
    {
        $message = !empty($message) ? $message : 'Too many invalid attempts. Please try again later.';
        return static::forCategory($message, ErrorLogger::CATEGORY_SECURITY, $context);
    }
}
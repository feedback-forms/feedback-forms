<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Throwable;

class ErrorLogger
{
    /**
     * Log levels with their corresponding methods
     */
    const LOG_LEVEL_DEBUG = 'debug';
    const LOG_LEVEL_INFO = 'info';
    const LOG_LEVEL_NOTICE = 'notice';
    const LOG_LEVEL_WARNING = 'warning';
    const LOG_LEVEL_ERROR = 'error';
    const LOG_LEVEL_CRITICAL = 'critical';
    const LOG_LEVEL_ALERT = 'alert';
    const LOG_LEVEL_EMERGENCY = 'emergency';

    /**
     * Categories for error classification
     */
    const CATEGORY_DATABASE = 'database';
    const CATEGORY_VALIDATION = 'validation';
    const CATEGORY_BUSINESS_LOGIC = 'business_logic';
    const CATEGORY_EXTERNAL_SERVICE = 'external_service';
    const CATEGORY_SECURITY = 'security';
    const CATEGORY_UNEXPECTED = 'unexpected';
    const CATEGORY_USER_INPUT = 'user_input';

    /**
     * Fields that should be redacted from logs for security/privacy
     *
     * @var array
     */
    protected static $sensitiveFields = [
        'password', 'secret', 'token', 'key', 'auth', 'credential', 'passwd', 'api_key',
        'credit_card', 'card_number', 'cvv', 'ssn', 'social_security'
    ];

    /**
     * Log an exception with structured data
     *
     * @param Throwable $exception The exception to log
     * @param string $category Error category for classification
     * @param string $logLevel The log level to use
     * @param array $context Additional context data
     * @return void
     */
    public static function logException(
        Throwable $exception,
        string $category = self::CATEGORY_UNEXPECTED,
        string $logLevel = self::LOG_LEVEL_ERROR,
        array $context = []
    ): void {
        $logContext = static::buildLogContext($exception, $category, $context);

        // Use specific message if available, otherwise use the exception message
        $message = $context['message'] ?? $exception->getMessage();

        // Add origin information to the message for easier tracing
        $logMessage = sprintf(
            '[%s] %s | %s::%s',
            strtoupper($category),
            $message,
            $logContext['class'] ?? 'Unknown',
            $logContext['function'] ?? 'unknown'
        );

        // Redact any sensitive information
        $logContext = static::redactSensitiveData($logContext);

        // Log with the appropriate level
        Log::$logLevel($logMessage, $logContext);
    }

    /**
     * Build the context array for logging
     *
     * @param Throwable $exception The exception to log
     * @param string $category Error category
     * @param array $context Additional context data
     * @return array
     */
    protected static function buildLogContext(
        Throwable $exception,
        string $category,
        array $context
    ): array {
        // Get exception source
        $trace = $exception->getTrace();
        $firstFrame = $trace[0] ?? [];

        $baseContext = [
            'exception' => [
                'class' => get_class($exception),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ],
            'category' => $category,
            'class' => $firstFrame['class'] ?? null,
            'function' => $firstFrame['function'] ?? null,
            'request_id' => request()->header('X-Request-ID') ?? uniqid('req-'),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
        ];

        // Include previous exception if available
        if ($exception->getPrevious()) {
            $baseContext['previous_exception'] = [
                'class' => get_class($exception->getPrevious()),
                'message' => $exception->getPrevious()->getMessage(),
                'code' => $exception->getPrevious()->getCode(),
                'file' => $exception->getPrevious()->getFile(),
                'line' => $exception->getPrevious()->getLine(),
            ];
        }

        // Include truncated stack trace in non-production environments
        if (config('app.env') !== 'production') {
            // Only include the first 5 frames of the stack trace
            $baseContext['trace'] = static::formatStackTraceForLogging(
                $exception->getTrace(),
                5
            );
        } else {
            // In production, just log the class/file/line of the origin point
            $origin = static::findExceptionOrigin($exception);
            if ($origin) {
                $baseContext['origin'] = $origin;
            }
        }

        // Merge with user-provided context, allowing it to override defaults
        return array_merge($baseContext, $context);
    }

    /**
     * Format stack trace for logging with limited frames
     *
     * @param array $trace The stack trace array
     * @param int $maxFrames Maximum number of frames to include
     * @return array
     */
    protected static function formatStackTraceForLogging(array $trace, int $maxFrames = 5): array
    {
        $formattedTrace = [];

        // Only include the first $maxFrames frames
        $limitedTrace = array_slice($trace, 0, $maxFrames);

        foreach ($limitedTrace as $i => $frame) {
            $formattedTrace[$i] = [
                'file' => $frame['file'] ?? '[internal function]',
                'line' => $frame['line'] ?? 0,
                'function' => $frame['function'] ?? null,
                'class' => $frame['class'] ?? null,
            ];

            // Don't include args in the log to prevent sensitive data exposure
            // and excessive log size
        }

        return $formattedTrace;
    }

    /**
     * Find the most likely source of the exception within the application code
     *
     * @param Throwable $exception The exception to analyze
     * @return array|null The origin information or null if not found
     */
    protected static function findExceptionOrigin(Throwable $exception): ?array
    {
        $trace = $exception->getTrace();

        // Look for the first frame that is within the application code
        foreach ($trace as $frame) {
            $file = $frame['file'] ?? '';

            // Check if it's an application file (not vendor)
            if (strpos($file, base_path('app')) === 0) {
                return [
                    'file' => $file,
                    'line' => $frame['line'] ?? 0,
                    'function' => $frame['function'] ?? null,
                    'class' => $frame['class'] ?? null,
                ];
            }
        }

        return null;
    }

    /**
     * Redact sensitive data from log context
     *
     * @param array $data The data to redact
     * @return array The redacted data
     */
    protected static function redactSensitiveData(array $data): array
    {
        foreach ($data as $key => $value) {
            // Check if this key should be redacted
            if (is_string($key) && static::shouldRedactKey($key)) {
                $data[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                // Recursively check nested arrays
                $data[$key] = static::redactSensitiveData($value);
            }
        }

        return $data;
    }

    /**
     * Determine if a key should be redacted
     *
     * @param string $key The key to check
     * @return bool
     */
    protected static function shouldRedactKey(string $key): bool
    {
        $key = strtolower($key);

        foreach (static::$sensitiveFields as $sensitiveField) {
            if (strpos($key, $sensitiveField) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log a general error message without an exception
     *
     * @param string $message The error message
     * @param string $category Error category
     * @param string $logLevel The log level to use
     * @param array $context Additional context data
     * @return void
     */
    public static function logError(
        string $message,
        string $category = self::CATEGORY_UNEXPECTED,
        string $logLevel = self::LOG_LEVEL_ERROR,
        array $context = []
    ): void {
        $baseContext = [
            'category' => $category,
            'request_id' => request()->header('X-Request-ID') ?? uniqid('req-'),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
        ];

        // Format message with category
        $logMessage = sprintf('[%s] %s', strtoupper($category), $message);

        // Get debug backtrace to find the caller
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        if (isset($trace[1])) {
            $baseContext['class'] = $trace[1]['class'] ?? null;
            $baseContext['function'] = $trace[1]['function'] ?? null;

            // Add origin to message
            $logMessage .= sprintf(
                ' | %s::%s',
                $baseContext['class'] ?? 'Unknown',
                $baseContext['function'] ?? 'unknown'
            );
        }

        // Merge contexts and redact sensitive data
        $logContext = static::redactSensitiveData(array_merge($baseContext, $context));

        // Log with the appropriate level
        Log::$logLevel($logMessage, $logContext);
    }
}
<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use App\Services\ErrorLogger;

/**
 * Base exception class for the application
 *
 * This class serves as the foundation for all exceptions in the application.
 * It provides consistent methods for logging, categorization, and context handling.
 */
abstract class BaseException extends Exception
{
    use LoggableException;

    /**
     * Create a new base exception
     *
     * @param string $message The error message
     * @param string $category The error category
     * @param array $context Additional context data
     * @param int $code The error code
     * @param Throwable|null $previous The previous exception
     */
    public function __construct(
        string $message,
        string $category = ErrorLogger::CATEGORY_UNEXPECTED,
        array $context = [],
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->category = $category;
        $this->context = $context;
        $this->logLevel = $this->determineLogLevel();
    }

    /**
     * Create a new exception instance for a specific error category
     *
     * @param string $message The error message
     * @param string $category The error category
     * @param array $context Additional context data
     * @param int $code The error code
     * @param Throwable|null $previous The previous exception
     * @return static The new exception instance
     */
    public static function forCategory(
        string $message,
        string $category,
        array $context = [],
        int $code = 0,
        ?Throwable $previous = null
    ): self {
        return new static($message, $category, $context, $code, $previous);
    }

    /**
     * Create a new exception from an existing exception
     *
     * @param Throwable $exception The exception to wrap
     * @param string $category The error category
     * @param array $context Additional context data
     * @return static
     */
    public static function fromException(
        Throwable $exception,
        string $category = ErrorLogger::CATEGORY_UNEXPECTED,
        array $context = []
    ): self {
        return new static(
            $exception->getMessage(),
            $category,
            $context,
            $exception->getCode(),
            $exception
        );
    }
}
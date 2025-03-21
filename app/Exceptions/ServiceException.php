<?php

namespace App\Exceptions;

use Exception;
use App\Services\ErrorLogger;

/**
 * ServiceException is the base exception class for all service-level exceptions.
 *
 * It provides consistent error categorization, logging, and context management.
 */
class ServiceException extends Exception
{
    use LoggableException;

    /**
     * Error categories for better classification (constants for backward compatibility)
     */
    const CATEGORY_DATABASE = ErrorLogger::CATEGORY_DATABASE;
    const CATEGORY_VALIDATION = ErrorLogger::CATEGORY_VALIDATION;
    const CATEGORY_BUSINESS_LOGIC = ErrorLogger::CATEGORY_BUSINESS_LOGIC;
    const CATEGORY_EXTERNAL_SERVICE = ErrorLogger::CATEGORY_EXTERNAL_SERVICE;
    const CATEGORY_UNEXPECTED = ErrorLogger::CATEGORY_UNEXPECTED;

    /**
     * Create a new service exception
     *
     * @param string $message The error message
     * @param string $category The error category
     * @param array $context Additional context data
     * @param int $code The error code
     * @param \Throwable|null $previous The previous exception
     */
    public function __construct(
        string $message,
        string $category = self::CATEGORY_UNEXPECTED,
        array $context = [],
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->category = $category;
        $this->context = $context;
        $this->logLevel = $this->determineLogLevel();

        // Log the exception when it's created
        $this->logException();
    }

    /**
     * Create a new service exception from an existing exception
     *
     * @param \Throwable $exception The exception to wrap
     * @param string $category The error category
     * @param array $context Additional context data
     * @return static
     */
    public static function fromException(
        \Throwable $exception,
        string $category = self::CATEGORY_UNEXPECTED,
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

    /**
     * Create a database error exception
     *
     * @param string $message The error message
     * @param array $context Additional context data
     * @param \Throwable|null $previous The previous exception
     * @return static
     */
    public static function database(string $message, array $context = [], \Throwable $previous = null): self
    {
        return static::forCategory($message, self::CATEGORY_DATABASE, $context, 0, $previous);
    }

    /**
     * Create a validation error exception
     *
     * @param string $message The error message
     * @param array $context Additional context data
     * @param \Throwable|null $previous The previous exception
     * @return static
     */
    public static function validation(string $message, array $context = [], \Throwable $previous = null): self
    {
        return static::forCategory($message, self::CATEGORY_VALIDATION, $context, 0, $previous);
    }

    /**
     * Create a business logic error exception
     *
     * @param string $message The error message
     * @param array $context Additional context data
     * @param \Throwable|null $previous The previous exception
     * @return static
     */
    public static function businessLogic(string $message, array $context = [], \Throwable $previous = null): self
    {
        return static::forCategory($message, self::CATEGORY_BUSINESS_LOGIC, $context, 0, $previous);
    }

    /**
     * Create an external service error exception
     *
     * @param string $message The error message
     * @param array $context Additional context data
     * @param \Throwable|null $previous The previous exception
     * @return static
     */
    public static function externalService(string $message, array $context = [], \Throwable $previous = null): self
    {
        return static::forCategory($message, self::CATEGORY_EXTERNAL_SERVICE, $context, 0, $previous);
    }

    /**
     * Create a security error exception
     *
     * @param string $message The error message
     * @param array $context Additional context data
     * @param \Throwable|null $previous The previous exception
     * @return static
     */
    public static function security(string $message, array $context = [], \Throwable $previous = null): self
    {
        return static::forCategory($message, ErrorLogger::CATEGORY_SECURITY, $context, 0, $previous);
    }

    /**
     * Create a user input error exception
     *
     * @param string $message The error message
     * @param array $context Additional context data
     * @param \Throwable|null $previous The previous exception
     * @return static
     */
    public static function userInput(string $message, array $context = [], \Throwable $previous = null): self
    {
        return static::forCategory($message, ErrorLogger::CATEGORY_USER_INPUT, $context, 0, $previous);
    }
}
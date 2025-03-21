<?php

namespace App\Exceptions;

use App\Services\ErrorLogger;
use Throwable;

/**
 * Trait to standardize exception logging behavior
 *
 * This trait provides consistent methods for exception logging and context management
 * that can be used across different exception types in the application.
 */
trait LoggableException
{
    /**
     * Additional context data for the error
     *
     * @var array
     */
    protected $context = [];

    /**
     * The error category for classification
     *
     * @var string
     */
    protected $category = ErrorLogger::CATEGORY_UNEXPECTED;

    /**
     * The log level to use
     *
     * @var string
     */
    protected $logLevel = ErrorLogger::LOG_LEVEL_ERROR;

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
     * Get the error category
     *
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * Get the log level
     *
     * @return string
     */
    public function getLogLevel(): string
    {
        return $this->logLevel;
    }

    /**
     * Set the error category
     *
     * @param string $category
     * @return $this
     */
    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Set the log level
     *
     * @param string $logLevel
     * @return $this
     */
    public function setLogLevel(string $logLevel): self
    {
        $this->logLevel = $logLevel;
        return $this;
    }

    /**
     * Add context data to the exception
     *
     * @param array $context
     * @return $this
     */
    public function withContext(array $context): self
    {
        $this->context = array_merge($this->context, $context);
        return $this;
    }

    /**
     * Log the exception with appropriate level and context
     */
    protected function logException(): void
    {
        ErrorLogger::logException(
            $this,
            $this->category,
            $this->logLevel,
            $this->context
        );
    }

    /**
     * Determine the appropriate log level based on the error category
     *
     * @return string The log level
     */
    protected function determineLogLevel(): string
    {
        $categoryLogLevelMap = [
            ErrorLogger::CATEGORY_VALIDATION => ErrorLogger::LOG_LEVEL_WARNING,
            ErrorLogger::CATEGORY_USER_INPUT => ErrorLogger::LOG_LEVEL_WARNING,
            ErrorLogger::CATEGORY_SECURITY => ErrorLogger::LOG_LEVEL_WARNING,
            ErrorLogger::CATEGORY_DATABASE => ErrorLogger::LOG_LEVEL_ERROR,
            ErrorLogger::CATEGORY_BUSINESS_LOGIC => ErrorLogger::LOG_LEVEL_ERROR,
            ErrorLogger::CATEGORY_EXTERNAL_SERVICE => ErrorLogger::LOG_LEVEL_ERROR,
            ErrorLogger::CATEGORY_DEPENDENCY_INJECTION => ErrorLogger::LOG_LEVEL_ERROR,
            ErrorLogger::CATEGORY_UNEXPECTED => ErrorLogger::LOG_LEVEL_CRITICAL,
        ];

        return $categoryLogLevelMap[$this->category] ?? ErrorLogger::LOG_LEVEL_ERROR;
    }

    /**
     * Create a new exception with a standard format for a specific category
     *
     * @param string $message The exception message
     * @param string $category The error category
     * @param array $context Additional context data
     * @param int $code Error code
     * @param Throwable|null $previous Previous exception
     * @return static
     */
    public static function forCategory(string $message, string $category, array $context = [], int $code = 0, ?Throwable $previous = null)
    {
        $instance = new static($message, $code, $previous);

        if (method_exists($instance, 'setCategory')) {
            $instance->setCategory($category);
        }

        if (method_exists($instance, 'withContext')) {
            $instance->withContext($context);
        }

        return $instance;
    }
}
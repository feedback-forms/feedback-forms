<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class ServiceException extends Exception
{
    /**
     * Error categories for better classification
     */
    const CATEGORY_DATABASE = 'database';
    const CATEGORY_VALIDATION = 'validation';
    const CATEGORY_BUSINESS_LOGIC = 'business_logic';
    const CATEGORY_EXTERNAL_SERVICE = 'external_service';
    const CATEGORY_UNEXPECTED = 'unexpected';

    /**
     * The error category
     *
     * @var string
     */
    protected $category;

    /**
     * Additional context data for the error
     *
     * @var array
     */
    protected $context;

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

        // Log the exception when it's created
        $this->logException();
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
     * Get the error context
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Log the exception with appropriate level and context
     */
    protected function logException(): void
    {
        $logLevel = $this->determineLogLevel();
        $logContext = $this->buildLogContext();

        Log::$logLevel($this->message, $logContext);
    }

    /**
     * Determine the appropriate log level based on the error category
     *
     * @return string The log level method name (error, warning, critical, etc.)
     */
    protected function determineLogLevel(): string
    {
        switch ($this->category) {
            case self::CATEGORY_VALIDATION:
                return 'warning';

            case self::CATEGORY_DATABASE:
            case self::CATEGORY_BUSINESS_LOGIC:
            case self::CATEGORY_EXTERNAL_SERVICE:
                return 'error';

            case self::CATEGORY_UNEXPECTED:
            default:
                return 'critical';
        }
    }

    /**
     * Build the context array for logging
     *
     * @return array
     */
    protected function buildLogContext(): array
    {
        $logContext = [
            'exception' => get_class($this),
            'category' => $this->category,
            'file' => $this->getFile(),
            'line' => $this->getLine(),
        ];

        // Include previous exception if available
        if ($this->getPrevious()) {
            $logContext['previous_exception'] = [
                'class' => get_class($this->getPrevious()),
                'message' => $this->getPrevious()->getMessage(),
                'code' => $this->getPrevious()->getCode(),
            ];
        }

        // Include stack trace in non-production environments
        if (config('app.env') !== 'production') {
            $logContext['trace'] = $this->getTraceAsString();
        }

        // Merge with user-provided context
        return array_merge($logContext, $this->context);
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
        return new static($message, self::CATEGORY_DATABASE, $context, 0, $previous);
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
        return new static($message, self::CATEGORY_VALIDATION, $context, 0, $previous);
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
        return new static($message, self::CATEGORY_BUSINESS_LOGIC, $context, 0, $previous);
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
        return new static($message, self::CATEGORY_EXTERNAL_SERVICE, $context, 0, $previous);
    }
}
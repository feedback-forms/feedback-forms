<?php

namespace App\Exceptions;

use ArgumentCountError;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use TypeError;
use App\Services\ErrorLogger;

/**
 * Exception for handling dependency injection errors
 */
class DependencyInjectionException extends ServiceException
{
    /**
     * The class with missing dependencies
     *
     * @var string
     */
    protected $class;

    /**
     * The missing dependencies details
     *
     * @var array
     */
    protected $missingDependencies = [];

    /**
     * Create a new dependency injection exception
     *
     * @param string $message
     * @param string $class The class with missing dependencies
     * @param array $missingDependencies Array of missing dependency details
     * @param Exception|null $previous The original exception
     * @param array $context Additional context data
     */
    public function __construct(
        string $message,
        string $class,
        array $missingDependencies = [],
        Exception $previous = null,
        array $context = []
    ) {
        $this->class = $class;
        $this->missingDependencies = $missingDependencies;

        parent::__construct($message, $context, 0, $previous);

        // Log the dependency injection error
        ErrorLogger::logDependencyInjectionError(
            $class,
            $message,
            $missingDependencies,
            $context
        );
    }

    /**
     * Get the class with missing dependencies
     *
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Get missing dependencies details
     *
     * @return array
     */
    public function getMissingDependencies(): array
    {
        return $this->missingDependencies;
    }

    /**
     * Create a DependencyInjectionException from common dependency injection errors
     *
     * @param \Throwable $exception The original exception
     * @param string $category Error category (ignored, always uses DEPENDENCY_INJECTION)
     * @param array $context Additional context data
     * @return static
     */
    public static function fromException(
        \Throwable $exception,
        string $category = self::CATEGORY_UNEXPECTED,
        array $context = []
    ): ServiceException
    {
        // Always use DEPENDENCY_INJECTION category
        $category = ErrorLogger::CATEGORY_DEPENDENCY_INJECTION;

        $class = 'Unknown Class';
        $missingDependencies = [];
        $message = $exception->getMessage();

        // Add dependency information to the context
        $diContext = array_merge($context, [
            'original_exception_class' => get_class($exception),
        ]);

        // Extract information from ArgumentCountError
        if ($exception instanceof ArgumentCountError) {
            // Parse the message to extract class name
            if (preg_match('/function (.+?)::__construct\(\)/', $message, $matches)) {
                $class = $matches[1];
                $diContext['class'] = $class;

                try {
                    $reflectionClass = new ReflectionClass($class);
                    $constructor = $reflectionClass->getConstructor();

                    if ($constructor) {
                        $parameters = $constructor->getParameters();
                        foreach ($parameters as $parameter) {
                            $missingDependencies[] = self::getParameterDetails($parameter);
                        }
                        $diContext['expected_parameters'] = count($parameters);
                    }
                } catch (ReflectionException $e) {
                    // If reflection fails, continue with the information we have
                }
            }
        }

        // Extract information from TypeError
        if ($exception instanceof TypeError) {
            // Parse the message to extract class name
            if (preg_match('/Argument (\d+) passed to (.+?)::__construct\(\)/', $message, $matches)) {
                $argPosition = (int)$matches[1];
                $class = $matches[2];
                $diContext['class'] = $class;
                $diContext['argument_position'] = $argPosition;

                try {
                    $reflectionClass = new ReflectionClass($class);
                    $constructor = $reflectionClass->getConstructor();

                    if ($constructor && isset($constructor->getParameters()[$argPosition - 1])) {
                        $parameter = $constructor->getParameters()[$argPosition - 1];
                        $missingDependencies[] = self::getParameterDetails($parameter);
                    }
                } catch (ReflectionException $e) {
                    // If reflection fails, continue with the information we have
                }
            }
        }

        $diContext['missing_dependencies'] = $missingDependencies;

        // Create a new instance with the enhanced context
        $instance = new static(
            "Dependency injection error: {$message}",
            $class,
            $missingDependencies,
            $exception instanceof Exception ? $exception : null,
            $diContext
        );

        return $instance;
    }

    /**
     * Get details about a parameter for better debugging
     *
     * @param ReflectionParameter $parameter
     * @return array
     */
    private static function getParameterDetails(ReflectionParameter $parameter): array
    {
        $details = [
            'name' => $parameter->getName(),
            'position' => $parameter->getPosition(),
            'is_optional' => $parameter->isOptional(),
        ];

        if ($parameter->hasType()) {
            $details['type'] = $parameter->getType()->getName();
        }

        if ($parameter->isDefaultValueAvailable()) {
            try {
                $details['default_value'] = $parameter->getDefaultValue();
            } catch (ReflectionException $e) {
                $details['default_value'] = '[unable to determine]';
            }
        }

        return $details;
    }
}
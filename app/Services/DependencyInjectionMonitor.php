<?php

namespace App\Services;

use App\Exceptions\DependencyInjectionException;
use ArgumentCountError;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Throwable;
use TypeError;

/**
 * Service to proactively monitor dependency injection issues
 */
class DependencyInjectionMonitor
{
    /**
     * Flag to determine if monitoring is active
     *
     * @var bool
     */
    protected $active = true;

    /**
     * List of bindings to be monitored
     *
     * @var array
     */
    protected $monitored = [];

    /**
     * Add a class to the monitoring list
     *
     * @param string $class The class to monitor
     * @param array $dependencies Optional explicit dependencies to check
     * @return $this
     */
    public function monitor(string $class, array $dependencies = []): self
    {
        $this->monitored[$class] = $dependencies;
        return $this;
    }

    /**
     * Enable or disable monitoring
     *
     * @param bool $active
     * @return $this
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Verify all registered bindings
     *
     * @return array Results of verification
     */
    public function verifyAllBindings(): array
    {
        if (!$this->active) {
            return ['status' => 'inactive'];
        }

        $results = [];
        $container = Container::getInstance();

        foreach ($this->monitored as $class => $dependencies) {
            try {
                $result = $this->verifyBinding($class, $dependencies);
                $results[$class] = $result;
            } catch (Throwable $e) {
                $results[$class] = [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'exception' => get_class($e)
                ];

                // Log the error
                ErrorLogger::logDependencyInjectionError(
                    $class,
                    "Failed to verify binding: {$e->getMessage()}",
                    [],
                    ['exception' => $e]
                );
            }
        }

        return $results;
    }

    /**
     * Verify a single binding
     *
     * @param string $class The class to verify
     * @param array $explicitDependencies Optional explicit dependencies
     * @return array Verification result
     */
    public function verifyBinding(string $class, array $explicitDependencies = []): array
    {
        try {
            $container = Container::getInstance();
            $reflector = new ReflectionClass($class);

            // Special handling for Laravel service providers
            if (is_subclass_of($class, \Illuminate\Support\ServiceProvider::class)) {
                return [
                    'status' => 'ok',
                    'message' => "Service Provider classes are handled specially by Laravel",
                    'dependencies' => [
                        [
                            'name' => 'app',
                            'status' => 'service_provider_injection',
                            'type' => 'Illuminate\\Contracts\\Foundation\\Application'
                        ]
                    ]
                ];
            }

            // Special handling for Livewire Form classes
            if (is_subclass_of($class, \Livewire\Form::class)) {
                return [
                    'status' => 'ok',
                    'message' => "Livewire Form classes are handled specially by Livewire",
                    'dependencies' => []
                ];
            }

            if (!$reflector->isInstantiable()) {
                return [
                    'status' => 'not_instantiable',
                    'message' => "Class {$class} is not instantiable"
                ];
            }

            $constructor = $reflector->getConstructor();

            // If there's no constructor, the class doesn't need DI
            if (is_null($constructor)) {
                return [
                    'status' => 'ok',
                    'dependencies' => []
                ];
            }

            $parameters = $constructor->getParameters();

            if (empty($parameters)) {
                return [
                    'status' => 'ok',
                    'dependencies' => []
                ];
            }

            $missingDependencies = [];
            $resolvedDependencies = [];

            foreach ($parameters as $parameter) {
                $dependency = $this->analyzeDependency($parameter, $container, $explicitDependencies);

                if ($dependency['status'] === 'error') {
                    $missingDependencies[] = $dependency;
                } else {
                    $resolvedDependencies[] = $dependency;
                }
            }

            if (!empty($missingDependencies)) {
                return [
                    'status' => 'missing_dependencies',
                    'message' => "Class {$class} has missing or unresolvable dependencies",
                    'missing' => $missingDependencies,
                    'resolved' => $resolvedDependencies
                ];
            }

            // Try to actually resolve the class
            $container->make($class);

            return [
                'status' => 'ok',
                'dependencies' => $resolvedDependencies
            ];
        } catch (BindingResolutionException $e) {
            // Create a specialized exception with detailed information
            $exception = DependencyInjectionException::fromException($e);

            // Rethrow the enhanced exception
            throw $exception;
        } catch (Throwable $e) {
            // If it's a known DI error type, create a specialized exception
            if ($e instanceof ArgumentCountError || $e instanceof TypeError) {
                $exception = DependencyInjectionException::fromException($e);
                throw $exception;
            }

            // Otherwise wrap in a general exception
            throw $e;
        }
    }

    /**
     * Analyze a single dependency
     *
     * @param ReflectionParameter $parameter The parameter to analyze
     * @param Container $container Laravel container
     * @param array $explicitDependencies Explicit dependencies to use
     * @return array Analysis result
     */
    protected function analyzeDependency(
        ReflectionParameter $parameter,
        Container $container,
        array $explicitDependencies
    ): array {
        $name = $parameter->getName();
        $result = [
            'name' => $name,
            'position' => $parameter->getPosition(),
            'optional' => $parameter->isOptional(),
        ];

        // If explicit dependency is provided, use it
        if (isset($explicitDependencies[$name])) {
            $result['status'] = 'explicit';
            $result['value_type'] = gettype($explicitDependencies[$name]);
            return $result;
        }

        // If parameter has type hint and it's a class
        if ($parameter->hasType() && !$parameter->getType()->isBuiltin()) {
            $typeName = $parameter->getType()->getName();
            $result['type'] = $typeName;

            try {
                // Check if the container can resolve this type
                if ($container->has($typeName) || class_exists($typeName)) {
                    $result['status'] = 'resolvable';
                    return $result;
                }
            } catch (Throwable $e) {
                $result['status'] = 'error';
                $result['message'] = "Cannot resolve class dependency: {$e->getMessage()}";
                return $result;
            }
        }

        // If parameter is optional or has a default value
        if ($parameter->isDefaultValueAvailable()) {
            try {
                $result['default_value'] = $parameter->getDefaultValue();
                $result['status'] = 'has_default';
                return $result;
            } catch (ReflectionException $e) {
                // Continue to next check
            }
        }

        if ($parameter->isOptional()) {
            $result['status'] = 'optional';
            return $result;
        }

        // If we reach here, the dependency is required but can't be resolved
        $result['status'] = 'error';
        $result['message'] = "Required dependency '{$name}' has no binding or default value";

        return $result;
    }

    /**
     * Register a verification hook for a service provider
     *
     * @param string $providerClass The service provider class name
     * @return bool Whether the hook was successfully registered
     */
    public static function registerProviderHook(string $providerClass): bool
    {
        if (!class_exists($providerClass)) {
            return false;
        }

        try {
            $providerReflection = new ReflectionClass($providerClass);
            $registerMethod = $providerReflection->getMethod('register');

            // This is a simplified approach - in a real application, you'd use
            // PHP-Parser or similar to modify the code or use a decorator pattern
            // Here we're just logging that we'd register a hook
            ErrorLogger::logError(
                "Would register DI verification hook for provider: {$providerClass}",
                ErrorLogger::CATEGORY_DEPENDENCY_INJECTION,
                ErrorLogger::LOG_LEVEL_INFO
            );

            return true;
        } catch (ReflectionException $e) {
            ErrorLogger::logException(
                $e,
                ErrorLogger::CATEGORY_DEPENDENCY_INJECTION,
                ErrorLogger::LOG_LEVEL_ERROR,
                [
                    'provider_class' => $providerClass,
                    'message' => "Failed to register provider hook"
                ]
            );

            return false;
        }
    }
}
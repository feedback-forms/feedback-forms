<?php

namespace App\Exceptions;

use App\Services\ErrorLogger;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

/**
 * Central exception handling class for consistent error handling across the application
 *
 * This class provides a standardized way to handle exceptions throughout the application,
 * ensuring consistent logging, appropriate HTTP responses, and proper context collection.
 */
class ExceptionHandler
{
    /**
     * Handle any exception in a consistent way
     *
     * @param Throwable $exception The exception to handle
     * @param Request|null $request The current request (optional)
     * @param array $context Additional context data
     * @return mixed Response object or null if not handling HTTP context
     */
    public static function handle(Throwable $exception, ?Request $request = null, array $context = [])
    {
        // Classify the exception and determine proper handling strategy
        $exceptionType = static::classifyException($exception);

        // Handle based on exception type
        return static::{'handle' . $exceptionType}($exception, $request, $context);
    }

    /**
     * Classify an exception to determine the handling strategy
     *
     * @param Throwable $exception The exception to classify
     * @return string The exception type/classification
     */
    protected static function classifyException(Throwable $exception): string
    {
        if ($exception instanceof ServiceException) {
            return 'ServiceException';
        } elseif ($exception instanceof SurveyNotAvailableException) {
            return 'SurveyNotAvailableException';
        } elseif ($exception instanceof InvalidAccessKeyException) {
            return 'InvalidAccessKeyException';
        } elseif ($exception instanceof DependencyInjectionException) {
            return 'DependencyInjectionException';
        } else {
            return 'GenericException';
        }
    }

    /**
     * Handle ServiceException instances
     *
     * @param ServiceException $exception The exception to handle
     * @param Request|null $request The current request
     * @param array $context Additional context data
     * @return mixed
     */
    protected static function handleServiceException(ServiceException $exception, ?Request $request, array $context)
    {
        // ServiceException already logs itself on construction, no need to log again

        // If we're in an HTTP context, return an appropriate response
        if ($request) {
            // Determine HTTP status code based on exception category
            $statusCode = static::mapCategoryToStatusCode($exception->getCategory());

            // Create response based on request format expectations
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => true,
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode() ?: $statusCode
                ], $statusCode);
            } else {
                // For web requests, redirect with flash message or show error view
                if ($statusCode >= 500) {
                    // Server errors show generic error page
                    return response()->view('errors.500', [
                        'message' => config('app.debug') ? $exception->getMessage() : __('errors.server_error')
                    ], $statusCode);
                } else {
                    // Client errors redirect with flash message
                    return redirect()->back()
                        ->withInput()
                        ->with('error', $exception->getMessage());
                }
            }
        }

        return null;
    }

    /**
     * Handle SurveyNotAvailableException instances
     *
     * @param SurveyNotAvailableException $exception The exception to handle
     * @param Request|null $request The current request
     * @param array $context Additional context data
     * @return mixed
     */
    protected static function handleSurveyNotAvailableException(SurveyNotAvailableException $exception, ?Request $request, array $context)
    {
        // Ensure exception is logged (this exception doesn't log itself)
        ErrorLogger::logException(
            $exception,
            ErrorLogger::CATEGORY_USER_INPUT,
            ErrorLogger::LOG_LEVEL_WARNING,
            array_merge(['message' => $exception->getMessage()], $exception->getContext())
        );

        // Use the built-in render method for HTTP context
        if ($request) {
            return $exception->render($request);
        }

        return null;
    }

    /**
     * Handle InvalidAccessKeyException instances
     *
     * @param InvalidAccessKeyException $exception The exception to handle
     * @param Request|null $request The current request
     * @param array $context Additional context data
     * @return mixed
     */
    protected static function handleInvalidAccessKeyException(InvalidAccessKeyException $exception, ?Request $request, array $context)
    {
        // This exception already logs itself in the constructor

        // Use the built-in render method for HTTP context
        if ($request) {
            return $exception->render($request);
        }

        return null;
    }

    /**
     * Handle DependencyInjectionException instances
     *
     * @param DependencyInjectionException $exception The exception to handle
     * @param Request|null $request The current request
     * @param array $context Additional context data
     * @return mixed
     */
    protected static function handleDependencyInjectionException(DependencyInjectionException $exception, ?Request $request, array $context)
    {
        // DependencyInjectionException is a subclass of ServiceException and logs itself

        // For HTTP context, always treat as a server error
        if ($request) {
            return response()->view('errors.500', [
                'message' => config('app.debug')
                    ? "Dependency error: " . $exception->getMessage()
                    : __('errors.server_error')
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return null;
    }

    /**
     * Handle generic (unclassified) exceptions
     *
     * @param Throwable $exception The exception to handle
     * @param Request|null $request The current request
     * @param array $context Additional context data
     * @return mixed
     */
    protected static function handleGenericException(Throwable $exception, ?Request $request, array $context)
    {
        // Wrap in ServiceException for consistent logging
        $wrappedException = ServiceException::fromException(
            $exception,
            ErrorLogger::CATEGORY_UNEXPECTED,
            $context
        );

        // For HTTP context, determine appropriate response
        if ($request) {
            // Default to server error for unknown exceptions
            return response()->view('errors.500', [
                'message' => config('app.debug')
                    ? $exception->getMessage()
                    : __('errors.server_error')
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return null;
    }

    /**
     * Map exception category to HTTP status code
     *
     * @param string $category The exception category
     * @return int HTTP status code
     */
    protected static function mapCategoryToStatusCode(string $category): int
    {
        $categoryToStatusMap = [
            ErrorLogger::CATEGORY_VALIDATION => Response::HTTP_BAD_REQUEST,
            ErrorLogger::CATEGORY_BUSINESS_LOGIC => Response::HTTP_UNPROCESSABLE_ENTITY,
            ErrorLogger::CATEGORY_SECURITY => Response::HTTP_FORBIDDEN,
            ErrorLogger::CATEGORY_USER_INPUT => Response::HTTP_BAD_REQUEST,
            ErrorLogger::CATEGORY_DATABASE => Response::HTTP_INTERNAL_SERVER_ERROR,
            ErrorLogger::CATEGORY_EXTERNAL_SERVICE => Response::HTTP_SERVICE_UNAVAILABLE,
            ErrorLogger::CATEGORY_DEPENDENCY_INJECTION => Response::HTTP_INTERNAL_SERVER_ERROR,
            ErrorLogger::CATEGORY_UNEXPECTED => Response::HTTP_INTERNAL_SERVER_ERROR,
        ];

        return $categoryToStatusMap[$category] ?? Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    /**
     * Try executing a callback and handle any exceptions
     *
     * @param callable $callback The callback to execute
     * @param string $category The error category to use if an exception occurs
     * @param array $context Additional context data
     * @param Request|null $request The current request (for HTTP context)
     * @return mixed The callback result or error response
     */
    public static function tryExecute(callable $callback, string $category = ErrorLogger::CATEGORY_UNEXPECTED, array $context = [], ?Request $request = null)
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            // If it's already a categorized exception, don't rewrap it
            if ($exception instanceof ServiceException) {
                return static::handle($exception, $request, $context);
            }

            // Wrap generic exceptions with the specified category
            $wrappedException = ServiceException::fromException(
                $exception,
                $category,
                $context
            );

            return static::handle($wrappedException, $request, $context);
        }
    }
}
# Error Handling Guidelines

This document outlines the standardized approach to error handling in the Feedback Forms application.

## Error Handling Architecture

The application uses a consistent error handling approach with these core components:

1. **LoggableException Trait**: Provides standardized logging behavior for all exceptions
2. **BaseException**: Abstract base class for all application exceptions
3. **ExceptionHandler**: Central utility for handling exceptions with consistent patterns
4. **Specialized Exceptions**: Domain-specific exceptions that extend BaseException

## Exception Hierarchy

```
Exception (PHP)
└── BaseException (abstract)
    ├── ServiceException
    ├── InvalidAccessKeyException
    ├── SurveyNotAvailableException
    └── DependencyInjectionException
```

## Using the Error Handling System

### Throwing Exceptions

Always use named static factory methods to throw exceptions:

```php
// ✅ Good: Use static factory methods for better readability
throw ServiceException::database("Database connection failed", [
    'connection' => 'mysql',
    'database' => 'feedback'
]);

// ✅ Good: Use category-specific exceptions
throw SurveyNotAvailableException::expired([
    'survey_id' => $survey->id,
    'expire_date' => $survey->expire_date
]);

// ❌ Bad: Direct instantiation with less context
throw new ServiceException("Database connection failed");
```

### Error Categories

Use the predefined error categories from `ErrorLogger`:

- `CATEGORY_DATABASE`: Database-related errors
- `CATEGORY_VALIDATION`: Input validation errors
- `CATEGORY_BUSINESS_LOGIC`: Business rule violations
- `CATEGORY_EXTERNAL_SERVICE`: Errors from external services
- `CATEGORY_SECURITY`: Security-related issues
- `CATEGORY_USER_INPUT`: Invalid user input
- `CATEGORY_DEPENDENCY_INJECTION`: DI configuration issues
- `CATEGORY_UNEXPECTED`: Unexpected/uncategorized errors

### Catching and Handling Exceptions

Use the `ExceptionHandler` to handle exceptions consistently:

```php
// Wrap potentially throwing code
return ExceptionHandler::tryExecute(
    fn() => $this->someOperationThatMightFail(),
    ErrorLogger::CATEGORY_BUSINESS_LOGIC,
    ['context' => 'additional data'],
    $request
);
```

### Adding Context to Exceptions

Always include meaningful context data when throwing exceptions:

```php
throw ServiceException::validation("Invalid input", [
    'input' => $request->all(),
    'errors' => $validator->errors()->toArray()
]);
```

## Best Practices

1. **Be Specific**: Use the most specific exception type for the situation
2. **Add Context**: Always include relevant context data
3. **Use Factory Methods**: Use static factory methods instead of constructors
4. **Handle Appropriately**: Use `ExceptionHandler` for consistent handling
5. **Don't Swallow**: Don't catch exceptions without handling or re-throwing
6. **Log Once**: Exceptions should generally be logged only once

## Creating New Exception Types

To create a new exception type:

1. Extend `BaseException` or a more specific parent
2. Use the `LoggableException` trait if extending directly from Exception
3. Add static factory methods for common use cases
4. Set appropriate default category and log level

Example:

```php
class PaymentFailedException extends BaseException
{
    public static function insufficientFunds(array $context = []): self
    {
        return static::forCategory(
            "Insufficient funds for payment",
            ErrorLogger::CATEGORY_BUSINESS_LOGIC,
            $context
        );
    }
}
```

## HTTP Error Responses

The `ExceptionHandler` automatically maps exception categories to HTTP status codes:

- Validation errors: 400 Bad Request
- Business logic violations: 422 Unprocessable Entity
- Security issues: 403 Forbidden
- Database errors: 500 Internal Server Error
- External service errors: 503 Service Unavailable
- Unexpected errors: 500 Internal Server Error
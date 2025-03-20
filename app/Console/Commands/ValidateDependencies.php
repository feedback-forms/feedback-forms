<?php

namespace App\Console\Commands;

use App\Services\DependencyInjectionMonitor;
use App\Services\ErrorLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use Throwable;

class ValidateDependencies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:validate-dependencies
                           {--class= : Validate a specific class}
                           {--path= : Path to scan for classes (relative to app/)}
                           {--all : Validate all classes in the app directory}
                           {--log-only : Only log issues without displaying them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate dependency injection across the application';

    /**
     * The dependency injection monitor service
     *
     * @var DependencyInjectionMonitor
     */
    protected $monitor;

    /**
     * Execute the console command.
     */
    public function handle(DependencyInjectionMonitor $monitor)
    {
        $this->monitor = $monitor;

        $class = $this->option('class');
        $path = $this->option('path');
        $all = $this->option('all');
        $logOnly = $this->option('log-only');

        if ($class) {
            $this->validateClass($class, $logOnly);
        } elseif ($path) {
            $this->validatePath($path, $logOnly);
        } elseif ($all) {
            $this->validateAll($logOnly);
        } else {
            $this->validateHighRiskComponents($logOnly);
        }

        return Command::SUCCESS;
    }

    /**
     * Validate a specific class
     *
     * @param string $class The class name or FQN
     * @param bool $logOnly Whether to only log issues
     * @return void
     */
    protected function validateClass(string $class, bool $logOnly = false): void
    {
        // Add namespace if not provided
        if (!Str::startsWith($class, 'App\\')) {
            $class = 'App\\' . $class;
        }

        if (!class_exists($class)) {
            $this->error("Class {$class} not found.");
            return;
        }

        $this->info("Validating class: {$class}");

        try {
            $result = $this->monitor->verifyBinding($class);

            if (!$logOnly) {
                if ($result['status'] === 'ok') {
                    $this->info("✓ {$class}: No dependency issues found");

                    if (!empty($result['dependencies'])) {
                        $this->table(
                            ['Dependency', 'Type', 'Status'],
                            $this->formatDependencies($result['dependencies'])
                        );
                    }
                } else {
                    $this->error("✗ {$class}: " . ($result['message'] ?? 'Unknown issue'));

                    if (isset($result['missing']) && !empty($result['missing'])) {
                        $this->warn("Missing dependencies:");
                        $this->table(
                            ['Name', 'Position', 'Type', 'Message'],
                            $this->formatMissingDependencies($result['missing'])
                        );
                    }
                }
            }
        } catch (Throwable $e) {
            $this->error("Error validating {$class}: {$e->getMessage()}");

            // Log the error
            ErrorLogger::logDependencyInjectionError(
                $class,
                "Validation failed during command execution: {$e->getMessage()}",
                [],
                ['exception' => $e]
            );
        }
    }

    /**
     * Validate all classes in a specific path
     *
     * @param string $path Path relative to app/
     * @param bool $logOnly Whether to only log issues
     * @return void
     */
    protected function validatePath(string $path, bool $logOnly = false): void
    {
        $fullPath = app_path($path);

        if (!File::isDirectory($fullPath)) {
            $this->error("Path not found: {$fullPath}");
            return;
        }

        $this->info("Scanning path: {$fullPath}");

        $files = File::allFiles($fullPath);
        $classes = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $content = File::get($file->getPathname());

                // Extract namespace and class name
                if (preg_match('/namespace\s+([^;]+)/i', $content, $namespaceMatch) &&
                    preg_match('/class\s+(\w+)/i', $content, $classMatch)) {
                    $namespace = $namespaceMatch[1];
                    $className = $classMatch[1];
                    $fullClassName = $namespace . '\\' . $className;

                    if (class_exists($fullClassName)) {
                        $classes[] = $fullClassName;
                    }
                }
            }
        }

        $this->info("Found " . count($classes) . " classes to validate");

        $issues = 0;
        foreach ($classes as $class) {
            try {
                $result = $this->monitor->verifyBinding($class);

                if ($result['status'] !== 'ok') {
                    $issues++;

                    if (!$logOnly) {
                        $this->error("✗ {$class}: " . ($result['message'] ?? 'Unknown issue'));
                    }

                    // Log the issue
                    ErrorLogger::logError(
                        "Dependency validation issue in {$class}: " . ($result['message'] ?? 'Unknown issue'),
                        ErrorLogger::CATEGORY_DEPENDENCY_INJECTION,
                        ErrorLogger::LOG_LEVEL_WARNING,
                        ['result' => $result]
                    );
                } elseif (!$logOnly) {
                    $this->line("✓ {$class}");
                }
            } catch (Throwable $e) {
                $issues++;

                if (!$logOnly) {
                    $this->error("Error validating {$class}: {$e->getMessage()}");
                }

                // Log the error
                ErrorLogger::logDependencyInjectionError(
                    $class,
                    "Validation failed during command execution: {$e->getMessage()}",
                    [],
                    ['exception' => $e]
                );
            }
        }

        if (!$logOnly) {
            if ($issues > 0) {
                $this->warn("Found {$issues} classes with dependency issues");
            } else {
                $this->info("All dependencies validated successfully!");
            }
        }
    }

    /**
     * Validate all classes in the application
     *
     * @param bool $logOnly Whether to only log issues
     * @return void
     */
    protected function validateAll(bool $logOnly = false): void
    {
        $this->validatePath('', $logOnly);
    }

    /**
     * Validate high-risk components likely to have DI issues
     *
     * @param bool $logOnly Whether to only log issues
     * @return void
     */
    protected function validateHighRiskComponents(bool $logOnly = false): void
    {
        $highRiskPaths = [
            'Services',
            'Repositories',
            'Http/Controllers',
            'Livewire',
            'Providers',
        ];

        $this->info("Validating high-risk components...");

        $totalIssues = 0;

        foreach ($highRiskPaths as $path) {
            if (!$logOnly) {
                $this->line("\n<fg=yellow>Checking {$path}</>:");
            }

            $fullPath = app_path($path);

            if (!File::isDirectory($fullPath)) {
                if (!$logOnly) {
                    $this->line("  <fg=gray>Path not found, skipping</>");
                }
                continue;
            }

            $files = File::allFiles($fullPath);
            $classes = [];

            foreach ($files as $file) {
                if ($file->getExtension() === 'php') {
                    $content = File::get($file->getPathname());

                    // Extract namespace and class name
                    if (preg_match('/namespace\s+([^;]+)/i', $content, $namespaceMatch) &&
                        preg_match('/class\s+(\w+)/i', $content, $classMatch)) {
                        $namespace = $namespaceMatch[1];
                        $className = $classMatch[1];
                        $fullClassName = $namespace . '\\' . $className;

                        if (class_exists($fullClassName)) {
                            $classes[] = $fullClassName;
                        }
                    }
                }
            }

            $pathIssues = 0;

            foreach ($classes as $class) {
                try {
                    // Skip abstract classes and interfaces
                    $reflection = new ReflectionClass($class);
                    if ($reflection->isAbstract() || $reflection->isInterface()) {
                        continue;
                    }

                    $result = $this->monitor->verifyBinding($class);

                    if ($result['status'] !== 'ok') {
                        $pathIssues++;
                        $totalIssues++;

                        if (!$logOnly) {
                            $this->error("  ✗ {$class}: " . ($result['message'] ?? 'Unknown issue'));
                        }

                        // Log the issue
                        ErrorLogger::logError(
                            "Dependency validation issue in {$class}: " . ($result['message'] ?? 'Unknown issue'),
                            ErrorLogger::CATEGORY_DEPENDENCY_INJECTION,
                            ErrorLogger::LOG_LEVEL_WARNING,
                            ['result' => $result]
                        );
                    } elseif (!$logOnly) {
                        $this->line("  <fg=green>✓</> {$class}");
                    }
                } catch (Throwable $e) {
                    $pathIssues++;
                    $totalIssues++;

                    if (!$logOnly) {
                        $this->error("  ✗ {$class}: {$e->getMessage()}");
                    }

                    // Log the error
                    ErrorLogger::logDependencyInjectionError(
                        $class,
                        "Validation failed during command execution: {$e->getMessage()}",
                        [],
                        ['exception' => $e]
                    );
                }
            }

            if (!$logOnly) {
                if ($pathIssues > 0) {
                    $this->warn("  Found {$pathIssues} issues in {$path}");
                } else {
                    $this->info("  No issues found in {$path}");
                }
            }
        }

        if (!$logOnly) {
            if ($totalIssues > 0) {
                $this->newLine();
                $this->warn("Found a total of {$totalIssues} dependency issues");
                $this->line("Run with specific paths for more details or fix issues in the service providers");
            } else {
                $this->newLine();
                $this->info("All high-risk components validated successfully!");
            }
        }
    }

    /**
     * Format dependencies for table output
     *
     * @param array $dependencies
     * @return array
     */
    protected function formatDependencies(array $dependencies): array
    {
        $formatted = [];

        foreach ($dependencies as $dependency) {
            $formatted[] = [
                $dependency['name'] ?? 'Unknown',
                $dependency['type'] ?? 'N/A',
                $dependency['status'] ?? 'Unknown',
            ];
        }

        return $formatted;
    }

    /**
     * Format missing dependencies for table output
     *
     * @param array $dependencies
     * @return array
     */
    protected function formatMissingDependencies(array $dependencies): array
    {
        $formatted = [];

        foreach ($dependencies as $dependency) {
            $formatted[] = [
                $dependency['name'] ?? 'Unknown',
                $dependency['position'] ?? 'N/A',
                $dependency['type'] ?? 'N/A',
                $dependency['message'] ?? 'No details available',
            ];
        }

        return $formatted;
    }
}
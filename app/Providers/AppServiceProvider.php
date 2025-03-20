<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Feedback;
use App\Policies\FeedbackPolicy;
use App\Repositories\FeedbackRepository;
use App\Services\DependencyInjectionMonitor;
use App\Services\ErrorLogger;
use App\Services\SurveyAccessService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the DependencyInjectionMonitor service
        $this->app->singleton(DependencyInjectionMonitor::class, function ($app) {
            return new DependencyInjectionMonitor();
        });

        // Register SurveyAccessService first since FeedbackRepository depends on it
        $this->app->singleton(SurveyAccessService::class, function ($app) {
            return new SurveyAccessService();
        });

        // Register repositories with improved error handling
        try {
            $this->app->singleton(FeedbackRepository::class, function ($app) {
                try {
                    return new FeedbackRepository(
                        $app->make(Feedback::class),
                        $app->make(SurveyAccessService::class)
                    );
                } catch (Throwable $e) {
                    // Log the error using our specialized error logger
                    ErrorLogger::logDependencyInjectionError(
                        FeedbackRepository::class,
                        "Failed to instantiate FeedbackRepository: {$e->getMessage()}",
                        [],
                        ['exception' => $e]
                    );
                    throw $e;
                }
            });

            // Use DependencyInjectionMonitor to validate the binding
            $monitor = $this->app->make(DependencyInjectionMonitor::class);
            $monitor->monitor(FeedbackRepository::class);

            // Only run validation in non-production environments
            if (app()->environment('local', 'testing', 'development')) {
                try {
                    $monitor->verifyBinding(FeedbackRepository::class);
                } catch (Throwable $e) {
                    // Just log the error, don't rethrow to avoid breaking the application
                    ErrorLogger::logDependencyInjectionError(
                        FeedbackRepository::class,
                        "Binding verification failed: {$e->getMessage()}",
                        [],
                        ['exception' => $e]
                    );
                }
            }
        } catch (Throwable $e) {
            // Log the error using our specialized error logger
            ErrorLogger::logDependencyInjectionError(
                FeedbackRepository::class,
                "Failed to register FeedbackRepository binding: {$e->getMessage()}",
                [],
                ['exception' => $e]
            );

            // Rethrow the exception to maintain Laravel's error handling flow
            throw $e;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin', function (User $user) {
            return $user->is_admin;
        });

        // Register the owns-survey gate - kept for backward compatibility
        // but should use authorize('view', $survey) etc. going forward
        Gate::define('owns-survey', [FeedbackPolicy::class, 'ownsSurvey']);

        // Register the feedback policy
        Gate::policy(Feedback::class, FeedbackPolicy::class);
    }
}

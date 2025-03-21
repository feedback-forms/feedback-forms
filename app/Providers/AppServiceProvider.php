<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Feedback;
use App\Policies\FeedbackPolicy;
use App\Repositories\FeedbackRepository;
use App\Services\DependencyInjectionMonitor;
use App\Services\ErrorLogger;
use App\Services\SurveyAccessService;
use App\Services\SurveyValidationService;
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

        // Register services
        $this->app->singleton(SurveyAccessService::class, function ($app) {
            return new SurveyAccessService();
        });

        $this->app->singleton(SurveyValidationService::class, function ($app) {
            return new SurveyValidationService();
        });

        // Register FeedbackRepository
        $this->registerFeedbackRepository();
    }

    /**
     * Register the feedback repository with error handling
     */
    private function registerFeedbackRepository(): void
    {
        try {
            $this->app->singleton(FeedbackRepository::class, function ($app) {
                try {
                    $model = $app->make(Feedback::class);
                    return new FeedbackRepository($model);
                } catch (Throwable $e) {
                    ErrorLogger::logDependencyInjectionError(
                        FeedbackRepository::class,
                        "Failed to instantiate FeedbackRepository: {$e->getMessage()}",
                        [],
                        ['exception' => $e]
                    );
                    throw $e;
                }
            });

            // Monitor binding
            $monitor = $this->app->make(DependencyInjectionMonitor::class);
            $monitor->monitor(FeedbackRepository::class);

            if (app()->environment('local', 'testing', 'development')) {
                try {
                    $monitor->verifyBinding(FeedbackRepository::class);
                } catch (Throwable $e) {
                    ErrorLogger::logDependencyInjectionError(
                        FeedbackRepository::class,
                        "Binding verification failed: {$e->getMessage()}",
                        [],
                        ['exception' => $e]
                    );
                }
            }
        } catch (Throwable $e) {
            ErrorLogger::logDependencyInjectionError(
                FeedbackRepository::class,
                "Failed to register FeedbackRepository binding: {$e->getMessage()}",
                [],
                ['exception' => $e]
            );
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
        Gate::define('owns-survey', [FeedbackPolicy::class, 'ownsSurvey']);

        // Register the feedback policy
        Gate::policy(Feedback::class, FeedbackPolicy::class);
    }
}

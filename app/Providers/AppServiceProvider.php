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
use App\Services\SurveyCreationService;
use App\Services\SurveyResponseHandlerService;
use App\Services\SurveyStatisticsService;
use App\Services\StatisticsService;
use App\Services\CacheService;
use App\Services\Templates\TemplateStrategyFactory;
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

        // Register CacheService
        $this->app->singleton(CacheService::class, function ($app) {
            return new CacheService();
        });

        // Register StatisticsService with CacheService dependency
        $this->app->singleton(StatisticsService::class, function ($app) {
            return new StatisticsService(
                $app->make(CacheService::class)
            );
        });

        // Register services
        $this->app->singleton(SurveyAccessService::class, function ($app) {
            return new SurveyAccessService();
        });

        $this->app->singleton(SurveyValidationService::class, function ($app) {
            return new SurveyValidationService();
        });

        $this->app->singleton(SurveyCreationService::class, function ($app) {
            return new SurveyCreationService(
                $app->make(TemplateStrategyFactory::class),
                $app->make(FeedbackRepository::class),
                $app->make(SurveyAccessService::class)
            );
        });

        $this->app->singleton(SurveyStatisticsService::class, function ($app) {
            return new SurveyStatisticsService(
                $app->make(StatisticsService::class),
                $app->make(CacheService::class)
            );
        });

        $this->app->singleton(SurveyResponseHandlerService::class, function ($app) {
            return new SurveyResponseHandlerService(
                $app->make(SurveyResponseService::class)
            );
        });

        $this->app->singleton(SurveyService::class, function ($app) {
            return new SurveyService(
                $app->make(SurveyCreationService::class),
                $app->make(SurveyResponseHandlerService::class),
                $app->make(SurveyStatisticsService::class),
                $app->make(SurveyValidationService::class)
            );
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

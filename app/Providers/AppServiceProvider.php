<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Feedback;
use App\Policies\FeedbackPolicy;
use App\Repositories\FeedbackRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register repositories
        $this->app->singleton(FeedbackRepository::class, function ($app) {
            return new FeedbackRepository($app->make(Feedback::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin', function (User $user) {
            return $user->is_admin;
        });

        // Register the owns-survey gate
        Gate::define('owns-survey', [FeedbackPolicy::class, 'ownsSurvey']);
    }
}

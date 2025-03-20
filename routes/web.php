<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{WelcomeController, SurveyController, SurveyResponseController};
use Livewire\Volt\Volt;

// Root route for welcome page and survey access
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
Route::post('/', [WelcomeController::class, 'accessSurvey'])->name('surveys.access.submit');
    // ------ TEMPLATE  ------
Route::get('/templates', App\Livewire\Templates\Overview::class)
    ->name('templates.index');

// Direct QR code access route
Route::get('/survey/scan', [WelcomeController::class, 'scanQrAccess'])->name('surveys.scan');

Route::middleware(['auth'])->group(function () {
    Route::view('profile', 'profile')->name('profile');
    Route::get('/admin-panel', App\Livewire\Admin\Panel::class)->name('admin.panel');
    Route::get('/admin/users', App\Livewire\Admin\Users::class)->name('admin.users');

    // Survey statistics route
    Route::get('/surveys/{survey}/statistics', [App\Http\Controllers\SurveyStatisticsController::class, 'show'])
        ->name('surveys.statistics');
});

Route::controller(SurveyController::class)->group(function (){
    Route::get('feedback/smiley', 'showSmiley');

    Route::get('feedback/table', 'showTable');

    Route::get('feedback/target', 'showTarget');

    Route::post('feedback/smiley', 'retrieveSmiley');
});

// ------ SURVEY ------ \\
Route::prefix('survey')->group(function() {
    Route::get('/survey/scan', [WelcomeController::class, 'scanQrAccess'])->name('surveys.scan');

    // The thank-you route needs to be defined before the dynamic {accesskey} route to avoid conflicts
    Route::get('/survey/thank-you', [SurveyResponseController::class, 'showThankYou'])->name('surveys.thank-you');

    Route::post('/survey/{accesskey}/submit', [SurveyResponseController::class, 'submitResponses'])->name('surveys.submit');
});

Route::middleware(['auth'])->group(function () {
    // ------ SURVEYS ------ \\
    Route::prefix('surveys')->group(function() {
        // Survey management routes - use Livewire component
        Route::get('/', App\Livewire\Surveys\Overview::class)
            ->name('surveys.index');

        // Use Livewire for the edit functionality
        Route::get('/{id}/edit', App\Livewire\Surveys\Edit::class)
            ->name('surveys.edit');

        // Route to create a survey from a template
        Route::get('/create/from-template/{template}', [SurveyController::class, 'create'])
            ->name('surveys.create.from-template');

        // Use resource route but exclude 'edit' and 'index' to avoid conflicts with Livewire
        Route::resource('/', SurveyController::class)->except(['edit', 'index']);

        // Survey statistics route
        Route::get('/{survey}/statistics', [App\Http\Controllers\SurveyStatisticsController::class, 'show'])
            ->name('surveys.statistics');

        // Add the missing store route
        Route::post('/', [SurveyController::class, 'store'])
            ->name('surveys.store');

        // Add the preview and create routes
        Route::get('/preview/{template}', [SurveyController::class, 'preview'])->name('surveys.preview');
        Route::get('/create/{template?}', [SurveyController::class, 'create'])->name('surveys.create');
    });

    // ------ ADMIN ------ \\
    Route::prefix('admin')->group(function() {
        Route::get('/', App\Livewire\Admin\Panel::class)
            ->middleware(['can:admin'])
            ->name('admin.panel');

        Route::get('/users', App\Livewire\Admin\Users::class)
            ->middleware(['can:admin'])
            ->name('admin.users');

        Route::get('/options', App\Livewire\Admin\Option::class)
            ->middleware(['can:admin'])
            ->name('admin.options');

        Route::get('/invite-token', App\Livewire\Admin\InviteToken::class)
            ->middleware(['can:admin'])
            ->name('admin.invite-token');

        // Survey Aggregation Dashboard
        Route::get('/survey-aggregation', App\Livewire\Admin\SurveyAggregation::class)
            ->middleware(['can:admin'])
            ->name('admin.survey-aggregation');
    });


    // ------ PROFILE  ------ \\
    Route::view('profile', 'profile')->name('profile');
});

// Test routes for debugging
Route::get('/test-translation', function () {
    return view('test-translation');
});

Route::get('/test-aggregation', [\App\Http\Controllers\TestController::class, 'testAggregation']);
Route::get('/test-question-categories', [\App\Http\Controllers\TestController::class, 'questionCategories']);
Route::get('/test-tab-categories', [\App\Http\Controllers\TestController::class, 'testTabCategories']);

require __DIR__.'/auth.php';

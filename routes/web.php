<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{WelcomeController, SurveyController, SurveyResponseController};
use Livewire\Volt\Volt;

Route::resource('/', WelcomeController::class);

Route::get('feedback/smiley', [SurveyController::class, 'showSmiley']);
Route::get('feedback/table', [SurveyController::class, 'showTable']);
Route::get('feedback/target', [SurveyController::class, 'showTarget']);

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/admin-panel', App\Livewire\Admin\Panel::class)
    ->middleware(['auth'])
    ->name('admin.panel');

Route::get('/admin/users', App\Livewire\Admin\Users::class)
    ->middleware(['auth'])
    ->name('admin.users');

// Survey response routes (for students)
Route::get('/survey', [SurveyResponseController::class, 'showAccessForm'])
    ->name('surveys.access');
Route::post('/survey/access', [SurveyResponseController::class, 'accessSurvey'])
    ->name('surveys.access.submit');
Route::post('/survey/{accesskey}/submit', [SurveyResponseController::class, 'submitResponses'])
    ->name('surveys.submit');
Route::get('/survey/thank-you', [SurveyResponseController::class, 'showThankYou'])
    ->name('surveys.thank-you');

Route::middleware(['auth'])->group(function () {
    // Survey management routes - use Livewire component
    Route::get('/surveys', App\Livewire\Surveys\Overview::class)
        ->name('surveys.index');

    // Use Livewire for the edit functionality
    Route::get('/surveys/{id}/edit', App\Livewire\Surveys\Edit::class)
        ->name('surveys.edit');

    // Templates routes
    Route::get('/templates', App\Livewire\Templates\Overview::class)
        ->name('templates.index');

    // Route to create a survey from a template
    Route::get('/surveys/create/from-template/{template}', [SurveyController::class, 'create'])
        ->name('surveys.create.from-template');

    // Use resource route but exclude 'edit' and 'index' to avoid conflicts with Livewire
    Route::resource('surveys', SurveyController::class)->except(['edit', 'index']);
});

require __DIR__.'/auth.php';

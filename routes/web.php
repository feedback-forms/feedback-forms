<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{WelcomeController, SurveyController};
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

require __DIR__.'/auth.php';

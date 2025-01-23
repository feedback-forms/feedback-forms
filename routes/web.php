<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{WelcomeController, SurveyController};
use Livewire\Volt\Volt;

Route::resource('/', WelcomeController::class);

Route::get('feedback/smiley', [SurveyController::class, 'showSmiley']);
Route::get('feedback/table', [SurveyController::class, 'showTable']);

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

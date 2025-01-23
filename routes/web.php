<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{WelcomeController, SurveyController};
use Livewire\Volt\Volt;

Route::get('/', [WelcomeController::class, 'render']);
Route::post('/', [WelcomeController::class, 'validate']);

Route::get('feedback/smiley', [SurveyController::class, 'showSmiley']);



Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

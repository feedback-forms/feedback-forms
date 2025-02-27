<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{WelcomeController, SurveyController};
use Livewire\Volt\Volt;

Route::resource('/', WelcomeController::class);

Route::get('feedback/smiley', [SurveyController::class, 'showSmiley']);
Route::get('feedback/table', [SurveyController::class, 'showTable']);
Route::get('feedback/target', [SurveyController::class, 'showTarget']);

Route::view('dashboard', 'dashboard')
    ->middleware(['auth'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/admin-panel', App\Livewire\Admin\Panel::class)
    ->middleware(['auth', 'can:admin'])
    ->name('admin.panel');

Route::get('/admin/users', App\Livewire\Admin\Users::class)
    ->middleware(['auth', 'can:admin'])
    ->name('admin.users');

require __DIR__.'/auth.php';

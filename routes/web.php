<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{WelcomeController, SurveyController};
use Livewire\Volt\Volt;

Route::resource('/', WelcomeController::class);

Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')
        ->middleware(['verified'])->name('dashboard');
    
    Route::view('profile', 'profile')->name('profile');
    
    Route::get('/admin-panel', App\Livewire\Admin\Panel::class)->name('admin.panel');
    
    Route::get('/admin/users', App\Livewire\Admin\Users::class)->name('admin.users');
});

Route::controller(SurveyController::class)->group(function (){
    Route::get('feedback/smiley', 'showSmiley');
    Route::get('feedback/table', 'showTable');
    Route::get('feedback/target', 'showTarget');
    Route::post('/feedback/smiley', 'retrieveSmiley');
});

require __DIR__.'/auth.php';

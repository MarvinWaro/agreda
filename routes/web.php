<?php

use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::get('/book', [BookingController::class, 'create'])->name('booking.create');
Route::post('/book', [BookingController::class, 'store'])->name('booking.store');
Route::get('/book/{booking}/done', [BookingController::class, 'done'])->name('booking.done');
Route::get('/api/availability', [BookingController::class, 'slots'])->name('availability');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

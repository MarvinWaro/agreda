<?php

use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
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

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
        Route::patch('/bookings/{booking}/confirm', [AdminBookingController::class, 'confirm'])->name('bookings.confirm');
        Route::patch('/bookings/{booking}/decline', [AdminBookingController::class, 'decline'])->name('bookings.decline');
    });

require __DIR__.'/settings.php';

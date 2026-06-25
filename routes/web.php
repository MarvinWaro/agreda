<?php

use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\SportController as AdminSportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PricingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/facilities', [FacilityController::class, 'index'])->name('facilities');
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
Route::get('/faqs', [FaqController::class, 'index'])->name('faqs');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');

Route::get('/book', [BookingController::class, 'create'])->name('booking.create');
Route::post('/book', [BookingController::class, 'store'])->name('booking.store');
Route::get('/book/{booking}/done', [BookingController::class, 'done'])->name('booking.done');
Route::get('/api/availability', [BookingController::class, 'slots'])->name('availability');

Route::middleware(['auth', 'verified'])->group(function () {
    // Authenticated accounts are admin/owner/staff; send them to the admin
    // area (the starter's generic dashboard is no longer used).
    Route::get('dashboard', fn () => auth()->user()?->can('admin.access')
        ? redirect()->route('admin.dashboard')
        : redirect()->route('home'))->name('dashboard');
});

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
        Route::patch('/bookings/{booking}/confirm', [AdminBookingController::class, 'confirm'])->name('bookings.confirm');
        Route::patch('/bookings/{booking}/decline', [AdminBookingController::class, 'decline'])->name('bookings.decline');

        Route::get('/sports', [AdminSportController::class, 'index'])->name('sports.index');
        Route::patch('/sports/{sport}', [AdminSportController::class, 'update'])->name('sports.update');

        Route::get('/settings', [AdminSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');

        Route::middleware('permission:users.manage')->group(function () {
            Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
            Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
            Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
            Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        });

        Route::middleware('permission:roles.manage')->group(function () {
            Route::get('/roles', [AdminRoleController::class, 'index'])->name('roles.index');
            Route::post('/roles', [AdminRoleController::class, 'store'])->name('roles.store');
            Route::put('/roles/{role}', [AdminRoleController::class, 'update'])->name('roles.update');
            Route::delete('/roles/{role}', [AdminRoleController::class, 'destroy'])->name('roles.destroy');
        });

        Route::middleware('permission:content.manage')->group(function () {
            Route::get('/faqs', [AdminFaqController::class, 'index'])->name('faqs.index');
            Route::post('/faqs', [AdminFaqController::class, 'store'])->name('faqs.store');
            Route::put('/faqs/{faq}', [AdminFaqController::class, 'update'])->name('faqs.update');
            Route::delete('/faqs/{faq}', [AdminFaqController::class, 'destroy'])->name('faqs.destroy');

            Route::get('/pages', [AdminPageController::class, 'index'])->name('pages.index');
            Route::put('/pages/{page}', [AdminPageController::class, 'update'])->name('pages.update');
        });
    });

require __DIR__.'/settings.php';

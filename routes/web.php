<?php

use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\ClubController as AdminClubController;
use App\Http\Controllers\Admin\ClubMemberController as AdminClubMemberController;
use App\Http\Controllers\Admin\ClubRoleController as AdminClubRoleController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\SlideController as AdminSlideController;
use App\Http\Controllers\Admin\SportController as AdminSportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\ClubMembershipController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MembershipController;
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

Route::get('/clubs', [ClubController::class, 'index'])->name('clubs.index');
Route::get('/clubs/{club:slug}/join', [ClubMembershipController::class, 'create'])->name('club.join.create');
Route::post('/clubs/{club:slug}/join', [ClubMembershipController::class, 'store'])->name('club.join.store');
Route::get('/club-members/{member}/done', [ClubMembershipController::class, 'done'])->name('club.join.done');

Route::middleware(['auth', 'verified'])->group(function () {
    // Admin/owner/staff get redirected to the admin area; club members see
    // their application status on the "My membership" page.
    Route::get('dashboard', [MembershipController::class, 'index'])->name('dashboard');
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

            Route::get('/slides', [AdminSlideController::class, 'index'])->name('slides.index');
            Route::post('/slides', [AdminSlideController::class, 'store'])->name('slides.store');
            Route::put('/slides/{slide}', [AdminSlideController::class, 'update'])->name('slides.update');
            Route::delete('/slides/{slide}', [AdminSlideController::class, 'destroy'])->name('slides.destroy');

            Route::get('/events', [AdminEventController::class, 'index'])->name('events.index');
            Route::post('/events', [AdminEventController::class, 'store'])->name('events.store');
            Route::put('/events/{event}', [AdminEventController::class, 'update'])->name('events.update');
            Route::delete('/events/{event}', [AdminEventController::class, 'destroy'])->name('events.destroy');
        });

        Route::middleware('permission:clubs.manage')->group(function () {
            Route::get('/clubs', [AdminClubController::class, 'index'])->name('clubs.index');
            Route::post('/clubs', [AdminClubController::class, 'store'])->name('clubs.store');
            Route::get('/clubs/{club}', [AdminClubController::class, 'show'])->name('clubs.show');
            Route::put('/clubs/{club}', [AdminClubController::class, 'update'])->name('clubs.update');
            Route::delete('/clubs/{club}', [AdminClubController::class, 'destroy'])->name('clubs.destroy');

            Route::post('/clubs/{club}/roles', [AdminClubRoleController::class, 'store'])->name('clubs.roles.store');
            Route::put('/clubs/{club}/roles/{role}', [AdminClubRoleController::class, 'update'])->name('clubs.roles.update');
            Route::delete('/clubs/{club}/roles/{role}', [AdminClubRoleController::class, 'destroy'])->name('clubs.roles.destroy');

            Route::get('/club-members', [AdminClubMemberController::class, 'index'])->name('club-members.index');
            Route::patch('/club-members/{member}/approve', [AdminClubMemberController::class, 'approve'])->name('club-members.approve');
            Route::patch('/club-members/{member}/decline', [AdminClubMemberController::class, 'decline'])->name('club-members.decline');
            Route::put('/club-members/{member}', [AdminClubMemberController::class, 'update'])->name('club-members.update');
        });
    });

require __DIR__.'/settings.php';

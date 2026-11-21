<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LaptopController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceTrackingController; 
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\ServisController;
use App\Http\Controllers\ServiceUpdateController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PembayaranPenilaianController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => view('dashboard'))->name('dashboard');
    Route::view('/style-guide', 'style-guide')->name('style-guide');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/pembayaran', [PembayaranPenilaianController::class, 'pembayaranIndex'])->name('pembayaran.index');
    Route::post('/pembayaran', [PembayaranPenilaianController::class, 'storePembayaran'])->name('pembayaran.store');
    Route::patch('/pembayaran/{pembayaran}/status', [PembayaranPenilaianController::class, 'updateStatusPembayaran'])->name('pembayaran.status');

    Route::get('/penilaian', [PembayaranPenilaianController::class, 'penilaianIndex'])->name('penilaian.index');
    Route::post('/penilaian', [PembayaranPenilaianController::class, 'storePenilaian'])->name('penilaian.store');
    Route::put('/penilaian/{penilaian}', [PembayaranPenilaianController::class, 'updatePenilaian'])->name('penilaian.update');
    Route::delete('/penilaian/{penilaian}', [PembayaranPenilaianController::class, 'destroyPenilaian'])->name('penilaian.destroy');

    // Every logged-in user can only see/edit their OWN profile.
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::view('/bookings', 'bookings.index')->name('bookings.index');
    Route::view('/bookings/create', 'bookings.create')->name('bookings.create');
    Route::view('/bookings/detail', 'bookings.show')->name('bookings.show');

    
    // Service progress per laptop: customers see their own laptops, admin sees all.
    Route::middleware('role:customer,admin')->group(function () {
        Route::get('/laptops', [LaptopController::class, 'index'])->name('laptops.index');
        Route::get('/service-tracking', [ServiceTrackingController::class, 'index'])->name('tracking.index');
        Route::get('/laptops/{laptop}', [LaptopController::class, 'show'])->whereNumber('laptop')->name('laptops.show');
    });

    // Customers register and maintain their own laptops (specs, photo).
    // Admin is a super user: acts as a customer too, and can manage any laptop.
    Route::middleware('role:customer,admin')->group(function () {
        Route::get('/laptops/create', [LaptopController::class, 'create'])->name('laptops.create');
        Route::post('/laptops', [LaptopController::class, 'store'])->name('laptops.store');
        Route::get('/laptops/{laptop}/edit', [LaptopController::class, 'edit'])->whereNumber('laptop')->name('laptops.edit');
        Route::put('/laptops/{laptop}', [LaptopController::class, 'update'])->whereNumber('laptop')->name('laptops.update');
        Route::delete('/laptops/{laptop}', [LaptopController::class, 'destroy'])->whereNumber('laptop')->name('laptops.destroy');
    });

    // Admin only: add / edit last / delete last progress update.
    Route::middleware('role:admin')->prefix('bookings/{booking}/updates')->scopeBindings()->group(function () {
        Route::post('/', [ServiceUpdateController::class, 'store'])->name('updates.store');
        Route::put('/{update}', [ServiceUpdateController::class, 'update'])->name('updates.update');
        Route::delete('/{update}', [ServiceUpdateController::class, 'destroy'])->name('updates.destroy');
    });

    // Admin only: manage all users.
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except('show');
    });
});

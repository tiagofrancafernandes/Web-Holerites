<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RedirectController;

RedirectController::routes();

Route::get('/', [RedirectController::class, 'adminDashboard'])->name('home');
Route::get('/login', [RedirectController::class, 'adminLogin'])->name('login');

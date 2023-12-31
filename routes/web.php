<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\Core\StorageFilesController;

RedirectController::routes();

Route::get('/', [RedirectController::class, 'adminDashboard'])->name('home');
Route::get('/login', [RedirectController::class, 'adminLogin'])->name('login');

StorageFilesController::routes();

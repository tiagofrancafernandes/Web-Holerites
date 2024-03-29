<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RedirectController extends Controller
{
    public function adminDashboard(Request $request)
    {
        return redirect()->route('filament.admin.pages.dashboard');
    }

    public function adminLogin(Request $request)
    {
        return redirect()->route('filament.admin.auth.login');
    }

    public static function routes()
    {
        Route::name('redirect.')->group(function () {
            Route::get('_', [static::class, 'adminDashboard'])->name('toAdmin');
            Route::get('/login', [RedirectController::class, 'adminLogin'])->name('toLogin');
        });
    }
}

<?php

namespace App\Filament\Extended\Filament\Pages\Auth;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use App\Http\Controllers\Global\MultiRedirectController;

class CustomLoginResponse implements LoginResponse
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        // return redirect()->intended(Filament::getUrl());
        return app(MultiRedirectController::class)->toAdminWithTryTenant($request);
    }
}

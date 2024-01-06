<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use App\Filament\Resources\Extended;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->bindReplaces();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->httpsLoad();
    }

    public function httpsLoad(): void
    {
        if (app()->isProduction() || config('app.force_https')) {
            ($this->{'app'}['request'] ?? null)?->server?->set('HTTPS', 'on');
            URL::forceScheme('https');
        }
    }

    protected function bindReplaces()
    {
        $replaces = [
            // abstract => [concrete, shared]

            // \Filament\Resources\Resource::class => Extended\ExtendedResourceBase::class,
            \Filament\Forms\Components\Select::class => Extended\Forms\Components\ExtendedSelect::class,
            \Filament\Forms\Components\DatePicker::class => Extended\Forms\Components\ExtendedDatePicker::class,
            \Filament\Forms\Components\DateTimePicker::class => Extended\Forms\Components\ExtendedDateTimePicker::class,
        ];

        foreach ($replaces as $abstract => $replacer) {
            $replacer = \Arr::wrap($replacer);
            $concrete = $replacer[0] ?? null;
            $shared = $replacer[1] ?? null;

            $this->app->bind($abstract, $concrete, $shared);
        }
    }
}

<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Hasnayeen\Themes\ThemesPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            // ->passwordReset() // Reset de senha ao fazer login (pela 1a vez?)
            ->sidebarCollapsibleOnDesktop()
            ->databaseNotifications()
            ->favicon(url('favicon.ico'))
            ->registration(
                false,
            )
            ->globalSearch(false)
            ->breadcrumbs(true)
            ->profile()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \Hasnayeen\Themes\Http\Middleware\SetTheme::class,
            ])
            ->tenantMiddleware([
                \Hasnayeen\Themes\Http\Middleware\SetTheme::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->darkMode(true)
            ->collapsibleNavigationGroups(true)
            ->sidebarCollapsibleOnDesktop(true)
            ->maxContentWidth('full')
            ->topNavigation(boolval(static::prefersGet('topNavigation', false)))
            ->brandName(config('app.name', 'Meu PDI'))
            ->plugin(
                ThemesPlugin::make()
                    ->canViewThemesPage(
                        fn () => true
                        // fn () => auth()->user()?->is_admin
                    ),
            );
    }

    public static function prefersGet(string $key, mixed $defaultValue = null): mixed
    {
        return \Cookie::get($key, $defaultValue);
    }
}

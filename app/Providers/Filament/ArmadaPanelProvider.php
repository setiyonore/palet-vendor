<?php

namespace App\Providers\Filament;

use App\Filament\Resources\UserResource;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin; // <-- Pastikan ini di-import
use BezhanSalleh\PanelSwitch\PanelSwitchPlugin;
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

class ArmadaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('armada')
            ->path('armada')
            ->login()
            ->colors([
                'primary' => Color::Green,
            ])
            // 1. Hanya memuat Resource khusus untuk Armada
            ->discoverResources(in: app_path('Filament/Armada/Resources'), for: 'App\\Filament\\Armada\\Resources')

            // 2. Memuat Pages khusus untuk Armada
            ->discoverPages(in: app_path('Filament/Armada/Pages'), for: 'App\\Filament\\Armada\\Pages')

            // 3. Mendaftarkan UserResource secara manual agar tetap tampil di panel ini
            ->resources([
                UserResource::class,
            ])

            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Armada/Widgets'), for: 'App\\Filament\\Armada\\Widgets')
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                // --- MENGAKTIFKAN KEMBALI SHIELD DENGAN KONFIGURASI TAMPILAN ---
                FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 2 // Menampilkan resource dalam 2 kolom agar rapi
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 2,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
                // ---------------------------------------------------------------

                // PanelSwitchPlugin::make(),
            ]);
    }
}

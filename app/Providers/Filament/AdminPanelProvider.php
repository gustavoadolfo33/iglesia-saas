<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
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
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

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
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\Dashboard\Filters\GlobalDashboardFilters::class,
                Widgets\AccountWidget::class,
                \App\Filament\Widgets\Dashboard\President\PresidentExecutiveStats::class,
                \App\Filament\Widgets\Dashboard\Treasurer\GlobalFinanceStats::class,
                \App\Filament\Widgets\Dashboard\Presbyter\PastoralOverviewStats::class,
                \App\Filament\Widgets\Dashboard\President\FinanceByChurchChart::class,
                \App\Filament\Widgets\Dashboard\President\AttendanceByChurchChart::class,
                \App\Filament\Widgets\Dashboard\Treasurer\IncomeVsExpenseChart::class,
                \App\Filament\Widgets\PeopleByStatusChart::class,
                \App\Filament\Widgets\Dashboard\Presbyter\FollowUpStatusChart::class,
                \App\Filament\Widgets\Dashboard\President\LowAttendanceChurchesTable::class,
                \App\Filament\Widgets\Dashboard\Presbyter\PersonsWithoutLeaderTable::class,
                \App\Filament\Widgets\Dashboard\Presbyter\PeopleNeedingCareTable::class,
                \App\Filament\Widgets\Dashboard\Treasurer\LargeMovementsTable::class,
                Widgets\FilamentInfoWidget::class,
                \App\Filament\Widgets\MeetingsStats::class,
                \App\Filament\Widgets\MeetingsByTypeChart::class,
                \App\Filament\Widgets\LatestMeetings::class,
                \App\Filament\Widgets\AttendanceByMonthChart::class,
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
                \App\Http\Middleware\EnsureFilamentRoleAccess::class,
            ]);

    }
}

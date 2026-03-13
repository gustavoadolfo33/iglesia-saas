<?php

namespace App\Filament\Widgets\Dashboard\Filters;

use App\Models\Church;
use App\Support\Dashboard\DashboardContext;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;

class GlobalDashboardFilters extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.dashboard.filters.global-dashboard-filters';

    protected int|string|array $columnSpan = 'full';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(DashboardContext::resolve());
    }

    public static function canView(): bool
    {
        return auth()->check();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('church_id')
                    ->label('Iglesia')
                    ->placeholder('Todas las iglesias')
                    ->options(fn() => Church::query()->orderBy('name')->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->visible(fn() => auth()->user()?->hasRole('super-admin') || auth()->user()?->isGlobalUser())
                    ->live()
                    ->afterStateUpdated(fn() => $this->persistFilters()),
                Select::make('date_preset')
                    ->label('Periodo')
                    ->options([
                        'today' => 'Hoy',
                        'week' => 'Esta semana',
                        'month' => 'Este mes',
                        'year' => 'Este año',
                        'custom' => 'Personalizado',
                    ])
                    ->required()
                    ->default('month')
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                        if ($state !== 'custom') {
                            $resolved = DashboardContext::store($this->data);
                            $set('date_from', $resolved['date_from']);
                            $set('date_to', $resolved['date_to']);
                        }

                        $this->persistFilters();
                    }),
                DatePicker::make('date_from')
                    ->label('Desde')
                    ->visible(fn(Get $get) => $get('date_preset') === 'custom')
                    ->live()
                    ->afterStateUpdated(fn() => $this->persistFilters()),
                DatePicker::make('date_to')
                    ->label('Hasta')
                    ->visible(fn(Get $get) => $get('date_preset') === 'custom')
                    ->live()
                    ->afterStateUpdated(fn() => $this->persistFilters()),
            ])
            ->columns([
                'default' => 1,
                'md' => 4,
            ])
            ->statePath('data');
    }

    protected function persistFilters(): void
    {
        $resolved = DashboardContext::store($this->data);
        $this->form->fill($resolved);
    }
}

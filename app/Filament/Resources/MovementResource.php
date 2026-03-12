<?php

namespace App\Filament\Resources;

use App\Exports\MovementsMonthlyExport;
use App\Filament\Resources\MovementResource\Pages;
use App\Models\Church;
use App\Models\Movement;
use App\Models\MovementCategory;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class MovementResource extends Resource
{
    protected static ?string $model = Movement::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finanzas';
    protected static ?string $navigationLabel = 'Movimientos';
    protected static ?int $navigationSort = 21;
    protected static ?string $modelLabel = 'movimiento';
    protected static ?string $pluralModelLabel = 'movimientos';

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canViewFinanceModule() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canManageFinanceModule() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->canManageFinanceModule() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->canManageFinanceModule() ?? false;
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('date')
                ->label('Fecha')
                ->required(),

            Forms\Components\Select::make('type')
                ->label('Tipo')
                ->options([
                    'income' => 'Ingreso',
                    'expense' => 'Egreso',
                ])
                ->live()
                ->required(),

            Forms\Components\Select::make('category_id')
                ->label('Categoría')
                ->options(function (callable $get) {
                    $type = $get('type');

                    if (!$type) {
                        return [];
                    }

                    return MovementCategory::query()
                        ->where('type', $type)
                        ->where('active', true)
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->required(),

            Forms\Components\TextInput::make('amount')
                ->label('Monto')
                ->numeric()
                ->required(),

            Forms\Components\TextInput::make('reference')
                ->label('Referencia')
                ->maxLength(80),

            Forms\Components\Textarea::make('description')
                ->label('Descripción')
                ->maxLength(255),

            Forms\Components\Select::make('church_id')
                ->label('Iglesia')
                ->options(fn() => \App\Models\Church::pluck('name', 'id')->toArray())
                ->searchable()
                ->preload()
                ->required(fn() => auth()->user()->isGlobalUser())
                ->visible(fn() => auth()->user()->isGlobalUser()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->colors([
                        'success' => 'income',
                        'danger' => 'expense',
                    ]),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('BOB'),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Registrado por'),
            ])
            ->defaultSort('date', 'desc')

            // Exportes van aquí (acciones globales)
            ->headerActions([
                Action::make('export_excel')
                    ->label('Exportar Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn() => auth()->user()?->canExportReports() ?? false)
                    ->form([
                        Select::make('month')
                            ->label('Mes')
                            ->options(collect(range(1, 12))->mapWithKeys(fn($m) => [$m => $m])->toArray())
                            ->default((int) now()->format('n'))
                            ->required(),

                        TextInput::make('year')
                            ->label('Año')
                            ->numeric()
                            ->default((int) now()->format('Y'))
                            ->required(),

                        Select::make('church_id')
                            ->label('Iglesia')
                            ->options(fn() => Church::pluck('name', 'id')->toArray())
                            ->visible(fn() => auth()->user()->isGlobalUser()),
                    ])
                    ->action(function (array $data) {
                        return Excel::download(
                            new MovementsMonthlyExport(
                                (int) $data['year'],
                                (int) $data['month'],
                                $data['church_id'] ?? null
                            ),
                            'movimientos_' . $data['year'] . '_' . $data['month'] . '.xlsx'
                        );
                    }),

                Action::make('export_pdf')
                    ->label('Exportar PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->visible(fn() => auth()->user()?->canExportReports() ?? false)
                    ->form([
                        Select::make('month')
                            ->label('Mes')
                            ->options(collect(range(1, 12))->mapWithKeys(fn($m) => [$m => $m])->toArray())
                            ->default((int) now()->format('n'))
                            ->required(),

                        TextInput::make('year')
                            ->label('Año')
                            ->numeric()
                            ->default((int) now()->format('Y'))
                            ->required(),

                        Select::make('church_id')
                            ->label('Iglesia')
                            ->options(fn() => Church::pluck('name', 'id')->toArray())
                            ->visible(fn() => auth()->user()->isGlobalUser()),
                    ])
                    ->action(function (array $data) {
                        $query = Movement::query()
                            ->whereYear('date', (int) $data['year'])
                            ->whereMonth('date', (int) $data['month']);

                        // Global puede filtrar por iglesia
                        if (!empty($data['church_id'])) {
                            $query->where('church_id', (int) $data['church_id']);
                        }

                        // Tenant ya queda filtrado automáticamente por el scope
                        $movements = $query->orderBy('date')->get();

                        $totalIncome = $movements->where('type', 'income')->sum('amount');
                        $totalExpense = $movements->where('type', 'expense')->sum('amount');

                        // ✅ RESUMEN POR CATEGORÍA (AQUÍ SE CREA)
                        $byCategory = $movements
                            ->groupBy(fn($m) => optional($m->category)->name ?? 'Sin categoría')
                            ->map(fn($group) => $group->sum('amount'));

                        $church = null;

                        if (!empty($data['church_id'])) {
                            $church = Church::find($data['church_id']);
                        } elseif (auth()->user()->isTenantUser()) {
                            $church = auth()->user()->currentChurch;
                        }


                        $pdf = Pdf::loadView('exports.movements_pdf', [
                            'movements' => $movements,
                            'year' => (int) $data['year'],
                            'month' => (int) $data['month'],
                            'church' => $church ?? null,
                            'totalIncome' => $totalIncome,
                            'totalExpense' => $totalExpense,
                            'balance' => $totalIncome - $totalExpense,

                            // 🔥 ESTA ES LA LÍNEA QUE PREGUNTASTE
                            'byCategory' => $byCategory,
                        ]);

                        return response()->streamDownload(
                            fn() => print ($pdf->output()),
                            'movimientos_' . $data['year'] . '_' . $data['month'] . '.pdf'
                        );
                    }),
            ])

            // ✅ acciones por fila (editar)
            ->actions([
                Tables\Actions\EditAction::make(),
            ])

            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMovements::route('/'),
            'create' => Pages\CreateMovement::route('/create'),
            'edit' => Pages\EditMovement::route('/{record}/edit'),
        ];
    }
}

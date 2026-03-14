<?php

namespace App\Filament\Resources\FormationTrackResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use App\Models\FormationLesson;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    protected static ?string $recordTitleAttribute = 'title';
    protected static ?string $title = 'Clases / lecciones';
    protected static ?string $modelLabel = 'clase';
    protected static ?string $pluralModelLabel = 'clases';

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->canViewFormationModule() ?? false;
    }

    public function isReadOnly(): bool
    {
        return !(auth()->user()?->canManageFormationCourses() ?? false);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->label('Titulo de la clase')
                ->required()
                ->maxLength(150)
                ->live(onBlur: true)
                ->afterStateUpdated(function (?string $state, Forms\Set $set, Forms\Get $get): void {
                    if (filled($get('slug'))) {
                        return;
                    }

                    $set('slug', str($state)->slug()->toString());
                }),
            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->maxLength(180),
            Forms\Components\Textarea::make('description')
                ->label('Descripcion')
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\Select::make('content_type')
                ->label('Tipo de contenido')
                ->options([
                    'text' => 'Texto',
                    'pdf' => 'PDF',
                    'video' => 'Video',
                    'link' => 'Enlace',
                ])
                ->required()
                ->live(),
            Forms\Components\Textarea::make('content_body')
                ->label('Contenido principal')
                ->rows(6)
                ->required(fn(Forms\Get $get) => $get('content_type') === 'text')
                ->helperText('Usa este campo principalmente para lecciones tipo texto.')
                ->visible(fn(Forms\Get $get) => in_array($get('content_type'), ['text', 'pdf'], true))
                ->columnSpanFull(),
            Forms\Components\TextInput::make('content_url')
                ->label('URL del recurso')
                ->url()
                ->required(fn(Forms\Get $get) => in_array($get('content_type'), ['pdf', 'video', 'link'], true))
                ->helperText('Requerido para PDF, video y enlace externo.')
                ->visible(fn(Forms\Get $get) => in_array($get('content_type'), ['video', 'link'], true)),
            Forms\Components\TextInput::make('sort_order')
                ->label('Orden')
                ->numeric()
                ->default(1)
                ->required(),
            Forms\Components\TextInput::make('estimated_minutes')
                ->label('Minutos estimados')
                ->numeric(),
            Forms\Components\Toggle::make('is_required')
                ->label('Obligatoria')
                ->default(true),
            Forms\Components\Toggle::make('is_active')
                ->label('Activa')
                ->default(true),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Clase')
                    ->searchable(),
                Tables\Columns\TextColumn::make('content_type')
                    ->label('Tipo')
                    ->badge(),
                Tables\Columns\TextColumn::make('estimated_minutes')
                    ->label('Minutos')
                    ->placeholder('—'),
                Tables\Columns\IconColumn::make('is_required')
                    ->label('Obligatoria')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (($data['content_type'] ?? null) !== 'text') {
                            $data['content_body'] = blank($data['content_body'] ?? null) ? null : $data['content_body'];
                        }

                        if (!in_array(($data['content_type'] ?? null), ['pdf', 'video', 'link'], true)) {
                            $data['content_url'] = null;
                        }

                        if (($data['content_type'] ?? null) === 'text' && blank($data['content_body'] ?? null)) {
                            throw ValidationException::withMessages(['content_body' => 'El contenido es obligatorio para lecciones tipo texto.']);
                        }

                        if (in_array(($data['content_type'] ?? null), ['pdf', 'video', 'link'], true) && blank($data['content_url'] ?? null)) {
                            throw ValidationException::withMessages(['content_url' => 'La URL es obligatoria para este tipo de contenido.']);
                        }

                        $exists = FormationLesson::query()
                            ->where('formation_track_id', $this->getOwnerRecord()->id)
                            ->where('slug', $data['slug'])
                            ->exists();

                        if ($exists) {
                            throw ValidationException::withMessages(['slug' => 'Ya existe una lección con este slug dentro de la ruta.']);
                        }

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (($data['content_type'] ?? null) !== 'text') {
                            $data['content_body'] = blank($data['content_body'] ?? null) ? null : $data['content_body'];
                        }

                        if (!in_array(($data['content_type'] ?? null), ['pdf', 'video', 'link'], true)) {
                            $data['content_url'] = null;
                        }

                        if (($data['content_type'] ?? null) === 'text' && blank($data['content_body'] ?? null)) {
                            throw ValidationException::withMessages(['content_body' => 'El contenido es obligatorio para lecciones tipo texto.']);
                        }

                        if (in_array(($data['content_type'] ?? null), ['pdf', 'video', 'link'], true) && blank($data['content_url'] ?? null)) {
                            throw ValidationException::withMessages(['content_url' => 'La URL es obligatoria para este tipo de contenido.']);
                        }

                        $exists = FormationLesson::query()
                            ->where('formation_track_id', $this->getOwnerRecord()->id)
                            ->where('slug', $data['slug'])
                            ->whereKeyNot($this->getMountedTableActionRecord()->getKey())
                            ->exists();

                        if ($exists) {
                            throw ValidationException::withMessages(['slug' => 'Ya existe una lección con este slug dentro de la ruta.']);
                        }

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}

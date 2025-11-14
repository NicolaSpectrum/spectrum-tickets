<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CelebrationResource\Pages;
use App\Models\Celebration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class CelebrationResource extends Resource
{
    protected static ?string $model = Celebration::class;
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Celebrations';
    protected static ?string $modelLabel = 'Celebration';   
    protected static ?string $pluralModelLabel = 'Celebrations';


    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Select::make('agency_id')
                ->label('Agency')
                ->relationship('agency', 'name')
                ->required()
                ->default(function () {
                    $user = auth()->user();
                    return $user->hasRole('organizer') ? $user->agency_id : null;
                })
                ->disabled(fn () => auth()->user()->hasRole('organizer'))
                ->dehydrated(true) // necesario para que sí se envíe el valor aunque esté disabled
                ->searchable(),

            Forms\Components\TextInput::make('name')
                ->label('Nombre Evento')
                ->required(),

            Forms\Components\Textarea::make('description')
                ->label('Descripcion')
                ->nullable(),

            Forms\Components\TextInput::make('location')
                ->label('Direccion')
                ->nullable(),

            Forms\Components\DateTimePicker::make('start_date')
                ->label('Fecha Inicio'),

            Forms\Components\DateTimePicker::make('end_date')
                ->label('Fecha Fin'),

            Forms\Components\TextInput::make('max_tickets')
                ->label('Entradas Maximas')
                ->numeric()
                ->default(0),

            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'draft' => 'Draft',
                    'pending_approval' => 'Pending Approval',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])
                ->disabled(fn () => !auth()->user()->hasRole('admin')),

            Forms\Components\Hidden::make('created_by')
                ->default(fn () => auth()->id()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre Evento')
                    ->searchable(),

                Tables\Columns\TextColumn::make('agency.name')
                    ->label('Agencia'),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Creado Por'),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Fecha Inicio')
                    ->dateTime(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fecha Fin')
                    ->dateTime(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'pending_approval',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Draft',
                        'pending_approval' => 'Pending Approval',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    }),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    // -------------------------------------------------------------------------
    // RESTRICCIÓN DE QUERIES SEGÚN ROL
    // -------------------------------------------------------------------------
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // Los organizers solo ven lo que ellos crearon
        if ($user->hasRole('organizer')) {
            return $query->where('created_by', $user->id);
        }

        return $query;
    }

   
    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->hasRole(['admin', 'organizer']);
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        return $user->hasRole(['admin', 'organizer']);
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) return true;

        // Organizer solo puede editar sus borradores o pendientes
        return $record->created_by === $user->id &&
            in_array($record->status, ['draft', 'pending_approval']);
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) return true;

        // Organizer solo puede eliminar borradores
        return $record->created_by === $user->id &&
            $record->status === 'draft';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCelebrations::route('/'),
            'create' => Pages\CreateCelebration::route('/create'),
            'edit' => Pages\EditCelebration::route('/{record}/edit'),
        ];
    }
}

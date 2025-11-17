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

            
            Forms\Components\Section::make('Información del Evento')
                ->description('Datos principales del evento.')
                ->schema([

                    Forms\Components\Select::make('agency_id')
                        ->label('Agencia')
                        ->relationship('agency', 'name')
                        ->required()
                        ->default(function () {
                            $user = auth()->user();
                            return $user->hasRole('organizer') ? $user->agency_id : null;
                        })
                        ->disabled(fn () => auth()->user()->hasRole('organizer'))
                        ->dehydrated(true)
                        ->searchable()
                        ->columnSpan(6),

                    Forms\Components\TextInput::make('name')
                        ->label('Nombre del Evento')
                        ->placeholder('Ej: Fiesta Anual, Concierto, Boda, etc.')
                        ->required()
                        ->columnSpan(6),

                    Forms\Components\Textarea::make('description')
                        ->label('Descripción')
                        ->placeholder('Describe brevemente el propósito o temática del evento.')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(12)
                ->collapsible(),

            // ===========================
            // 📍 UBICACIÓN Y FECHAS
            // ===========================
            Forms\Components\Section::make('Ubicación & Fechas')
                ->description('Información logística del evento.')
                ->schema([

                    Forms\Components\TextInput::make('location')
                        ->label('Dirección o Locación')
                        ->placeholder('Ej: Calle 123 #45-67, Salón XYZ...')
                        ->columnSpanFull(),

                    Forms\Components\DateTimePicker::make('start_date')
                        ->label('Fecha y Hora de Inicio')
                        ->required()
                        ->columnSpan(6),

                    Forms\Components\DateTimePicker::make('end_date')
                        ->label('Fecha y Hora de Finalización')
                        ->columnSpan(6),
                ])
                ->columns(12)
                ->collapsible(),

            Forms\Components\Section::make('Configuración de Entradas')
                ->description('Define los tipos de ubicación o zonas disponibles en este evento.')
                
                ->schema([

                    Forms\Components\TextInput::make('max_tickets')
                        ->label('Cantidad Máxima de Entradas')
                        ->numeric()
                        ->minValue(0)
                        ->required()
                        ->placeholder('Ej: 1000')
                        ->columnSpan(4),
                    Forms\Components\Toggle::make('has_seating')
                        ->label('Este evento tiene ubicación/asientos?')
                        ->helperText('Activa para asignar VIP, General, Mesas, Sillas, etc.')
                        ->default(false)
                        ->reactive(),
                    
                    Forms\Components\Repeater::make('ticket_types')
                        ->label('Tipos de Ubicación o Zonas')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Nombre de la Zona')
                                ->placeholder('VIP, General, Palco, Backstage...')
                                ->required(),
                        ])
                        ->visible(fn (callable $get) => $get('has_seating') === true)
                        ->addActionLabel('Agregar nueva zona')
                        ->reorderable()
                        ->collapsed()
                        ->default([])
                        ->columnSpanFull(),

                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->options([
                            'draft'             => 'Borrador',
                            'pending_approval'  => 'Pendiente de Aprobación',
                            'approved'          => 'Aprobado',
                            'rejected'          => 'Rechazado',
                        ])
                        ->disabled(fn () => !auth()->user()->hasRole('admin'))
                        ->visible(fn () => auth()->user()->hasRole('admin'))
                        ->columnSpan(4),
                ])
                ->columns(8)
                ->collapsible(),

            Forms\Components\Hidden::make('created_by')
                ->default(fn () => auth()->id()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

             
                Tables\Columns\TextColumn::make('name')
                    ->label('Evento')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->location) // ubicación en miniatura
                    ->icon('heroicon-o-sparkles')
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'gray'    => 'draft',
                        'warning' => 'pending_approval',
                        'success' => 'approved',
                        'danger'  => 'rejected',
                        'info' => 'completed',
                    ])
                    ->icons([
                        'heroicon-o-pencil'       => 'draft',
                        'heroicon-o-clock'        => 'pending_approval',
                        'heroicon-o-check-circle' => 'approved',
                        'heroicon-o-x-circle'     => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft'             => 'Borrador',
                        'pending_approval'  => 'Pendiente',
                        'approved'          => 'Aprobado',
                        'rejected'          => 'Rechazado',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('agency.name')
                    ->label('Agencia')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-building-office')
                    ->visible(fn () => auth()->user()->hasRole('admin')),
                
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Creador')
                    ->icon('heroicon-o-user-circle'),

                
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Inicio')
                    ->dateTime('d M Y - H:i')
                    ->sortable()
                    ->tooltip(fn ($record) => $record->start_date)
                    ->icon('heroicon-o-calendar-days'),

                
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->dateTime('d M Y - H:i')
                    ->sortable()
                    ->icon('heroicon-o-clock'),

               
                
            ])

            
            ->defaultSort('start_date', 'desc')
            ->striped() // filas alternadas visualmente
            ->paginationPageOptions([10, 25, 50])

            
            ->actions([
                //Tables\Actions\Action::make('viewRegistrations')
                //    ->label('Ver Asistentes')
                //    ->color('info')
                //    ->icon('heroicon-o-users')
                //    ->url(fn ($record) => route('filament.admin.resources.registrations.index', [
                //        'celebration_id' => $record->id,
                //    ]))
                //    ->visible(fn ($record) => $record->status === 'approved'),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) =>
                        auth()->user()->hasRole('admin') ||
                        ($record->created_by === auth()->id() && $record->status === 'draft')
                    ),
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

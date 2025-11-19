<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistrationResource\Pages;
use App\Models\Registration;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Builder;

class RegistrationResource extends Resource
{
    protected static ?string $model = Registration::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Celebrations';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Datos del Evento')
                ->description('Selecciona la celebración aprobada y asigna la ubicación.')
                ->icon('heroicon-o-sparkles')
                ->schema([
                    Forms\Components\Select::make('celebration_id')
                        ->relationship(
                            name: 'celebration',
                            titleAttribute: 'name',
                            modifyQueryUsing: function ($query) {
                                $user = auth()->user();
                                $query->where('status', 'approved');
                                if ($user->hasRole('organizer')) {
                                    $query->where('agency_id', $user->agency_id);
                                }
                                return $query;
                            }
                        )
                        ->label('Celebración')
                        ->required()
                        ->preload()
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $set('seat_type', null);
                            $set('seat_number', null);
                        })
                        ->default(request()->query('celebration_id')),
                ])
                ->collapsible(),

            Forms\Components\Section::make('Información del Asistente')
                ->description('Datos básicos para generar el ticket y evitar duplicados.')
                ->icon('heroicon-o-user')
                ->schema([

                    Forms\Components\TextInput::make('name')
                        ->label('Nombre del Asistente')
                        ->required()
                        ->placeholder('Ej: Juan Pérez'),

                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->placeholder('ejemplo@gmail.com'),

                    Forms\Components\Select::make('id_type')
                        ->label('Tipo de Identificación')
                        ->options([
                            'cc' => 'Cédula de Ciudadanía',
                            'ce' => 'Cédula de Extranjería',
                            'passport' => 'Pasaporte',
                            'other' => 'Otro',
                        ])
                        ->required()
                        ->default('cc')
                        ->native(false),

                    Forms\Components\TextInput::make('id_number')
                        ->label('Número de Identificación')
                        ->required()
                        ->reactive()
                        ->placeholder('Ej: 123456789')
                        ->rules([
                            fn (callable $get) => function (string $attribute, $value, \Closure $fail) use ($get) {

                                $celebrationId = $get('celebration_id');
                                $recordId = request()->route('record'); 

                                if (!$celebrationId) return;

                                $exists = \App\Models\Registration::where('celebration_id', $celebrationId)
                                    ->where('id_number', $value)
                                    ->when($recordId, fn ($q) => $q->where('id', '!=', $recordId))
                                    ->exists();

                                if ($exists) {
                                    $fail("Este asistente ya está registrado para esta celebración.");
                                }
                            }
                        ]),
                ])
                ->columns(2)
                ->collapsible(),

            Forms\Components\Section::make('Asignación de Asiento')
                ->description('Selecciona el tipo de ubicación disponible y especifica un número de asiento opcional.')
                ->icon('heroicon-o-map-pin')
                ->schema([

                    Forms\Components\Select::make('seat_type')
                        ->label('Tipo de Ubicación')
                        ->options(function (callable $get) {
                            $celebrationId = $get('celebration_id');

                            if (!$celebrationId) return [];

                            $celebration = \App\Models\Celebration::find($celebrationId);

                            // Si el evento NO tiene posicionamiento → NO mostrar opciones
                            if (!$celebration || empty($celebration->ticket_types)) {
                                return [];
                            }

                            return collect($celebration->ticket_types)
                                ->pluck('name', 'name')
                                ->toArray();
                        })
                        ->visible(fn (callable $get) =>            // Mostrar solo si la celebración tiene tipos
                            ($celebrationId = $get('celebration_id')) &&
                            optional(\App\Models\Celebration::find($celebrationId))->ticket_types
                        )
                        ->required(fn (callable $get) =>           // Requerido SOLO si hay tipos de ticket
                            ($celebrationId = $get('celebration_id')) &&
                            !empty(optional(\App\Models\Celebration::find($celebrationId))->ticket_types)
                        )
                        ->reactive()
                        ->placeholder('Seleccione un tipo de ticket'),

                    Forms\Components\TextInput::make('seat_number')
                        ->label('Número de Asiento / Posición')
                        ->placeholder('Ej: A12, Mesa 3, Silla 20...')
                        ->visible(fn (callable $get) =>            // Visible SOLO si hay un tipo seleccionado
                            ($celebrationId = $get('celebration_id')) &&
                            !empty(optional(\App\Models\Celebration::find($celebrationId))->ticket_types) &&
                            $get('seat_type')
                        )
                        ->nullable()
                        ->rules([
                            fn (callable $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                if (!$value) return;

                                $celebrationId = $get('celebration_id');
                                if (!$celebrationId) return;

                                $recordId = request()->route('record'); 

                                $exists = \App\Models\Registration::where('celebration_id', $celebrationId)
                                    ->where('seat_number', $value)
                                    ->when($recordId, fn ($q) => $q->where('id', '!=', $recordId))
                                    ->exists();

                                if ($exists) {
                                    $fail("El asiento '{$value}' ya está asignado dentro de esta celebración.");
                                }
                            }
                        ])
                        ->columnSpanFull(),

                ])
                ->columns(2)
                ->collapsible(),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->label('Asistente')
                    ->icon('heroicon-o-user')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\IconColumn::make('checked_in')
                    ->label('Check-In')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('celebration.name')
                    ->label('Celebración')
                    ->description(fn ($record) => $record->celebration->start_date)
                    ->icon('heroicon-o-sparkles')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('id_type')
                    ->label('ID Tipo')
                    ->formatStateUsing(fn ($state) => strtoupper($state))
                    ->colors([
                        'info' => 'cc',
                        'warning' => 'ce',
                        'success' => 'passport',
                        'danger' => 'other',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('id_number')
                    ->label('Identificación')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Copiado'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->icon('heroicon-o-envelope')
                    ->toggleable(),
                
                Tables\Columns\BadgeColumn::make('seat_type')
                    ->label('Ubicación')
                    ->colors([
                        'info' => fn ($state) => $state === 'General',
                        'success' => fn ($state) => $state === 'VIP',
                        'warning' => fn ($state) => $state !== 'VIP' && $state !== 'General',
                    ])
                    ->icons([
                        'heroicon-o-map-pin' => fn ($state) => $state !== null,
                    ])
                    ->sortable(),


                Tables\Columns\TextColumn::make('seat_number')
                    ->label('Asiento')
                    ->placeholder('—')
                    ->sortable(),
            ])

            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginationPageOptions([10, 25, 50])

            ->actions([
              //  Tables\Actions\Action::make('checkIn')
              //      ->label('Marcar Check-In')
              //      ->icon('heroicon-o-check')
              //      ->color('success')
              //      ->visible(fn ($record) => !$record->checked_in)
              //      ->requiresConfirmation()
              //      ->action(function ($record) {
              //          $record->update([
              //              'checked_in' => true,
              //              'checked_in_at' => now(),
              //              'verified_by' => auth()->id(),
              //          ]);
              //      }),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->hasRole('admin')),
        ]);
    }

    /**
     * 🔐 FILTRA registros según la agencia del organizador
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // Solo organizadores → ver solo asistencias de celebraciones de su agencia
        if ($user->hasRole('organizer')) {
            return $query->whereHas('celebration', function ($q) use ($user) {
                $q->where('agency_id', $user->agency_id);
            });
        }

        return $query;
    }

     public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->hasRole(['admin', 'organizer']);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrations::route('/'),
            'create' => Pages\CreateRegistration::route('/create'),
            'edit' => Pages\EditRegistration::route('/{record}/edit'),
        ];
    }
}

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

            Forms\Components\Select::make('celebration_id')
                ->relationship(
                    name: 'celebration',
                    titleAttribute: 'name',
                    modifyQueryUsing: function ($query) {
                        $user = auth()->user();
                        $query->where('status', 'approved');
                        // Solo ver celebraciones de su agencia
                        if ($user->hasRole('organizer')) {
                            $query->where('agency_id', $user->agency_id);
                        }

                        return $query;
                    }
                )
                ->label('Celebration')
                ->required()
                ->preload()
                ->searchable()
                ->default(request()->query('celebration_id')),

            Forms\Components\TextInput::make('name')
                ->label('Nombre Asistente')
                ->required(),

            Forms\Components\TextInput::make('email')
                ->email()
                ->required(),

            Forms\Components\Select::make('id_type')
                ->label('Tipo de Indentificación')
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
                ->label('Numero de Identificacion')
                ->required()
                ->reactive()
                ->rules([
                    fn (callable $get) => function (string $attribute, $value, \Closure $fail) use ($get) {

                        $celebrationId = $get('celebration_id');
                        $recordId = request()->route('record'); // null en create

                        if (!$celebrationId) {
                            return;
                        }

                        $exists = \App\Models\Registration::where('celebration_id', $celebrationId)
                            ->where('id_number', $value)
                            ->when($recordId, fn ($q) => $q->where('id', '!=', $recordId))
                            ->exists();

                        if ($exists) {
                            $fail("Este asistente ya está registrado para esta celebración.");
                        }
                    }
                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('email')->searchable(),
            Tables\Columns\TextColumn::make('id_type')->label('ID Type')->sortable(),
            Tables\Columns\TextColumn::make('id_number')->label('ID Number')->sortable(),
            Tables\Columns\TextColumn::make('celebration.name')->label('Celebration'),
            Tables\Columns\IconColumn::make('checked_in')
                ->boolean()
                ->label('Checked In'),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrations::route('/'),
            'create' => Pages\CreateRegistration::route('/create'),
            'edit' => Pages\EditRegistration::route('/{record}/edit'),
        ];
    }
}

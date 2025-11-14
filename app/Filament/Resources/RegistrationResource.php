<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistrationResource\Pages;
use App\Models\Registration;
use App\Models\Celebration;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use App\Mail\RegistrationQrMail;
use Illuminate\Support\Facades\Mail;

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
                        
                        // Organizer → solo celebraciones de su agencia
                        if ($user->hasRole('organizer')) {
                            return $query->where('agency_id', $user->agency_id);
                        }
                        
                        // Admin → todas las celebraciones
                        return $query;
                    }
                )
                ->label('Celebration')
                ->required()
                ->preload()
                ->searchable()
                ->default(request()->query('celebration_id')),

            Forms\Components\TextInput::make('name')
                ->required(),

            Forms\Components\TextInput::make('email')
                ->email()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name'),
            Tables\Columns\TextColumn::make('email'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrations::route('/'),
            'create' => Pages\CreateRegistration::route('/create'),
            'edit' => Pages\EditRegistration::route('/{record}/edit'),
        ];
    }
}

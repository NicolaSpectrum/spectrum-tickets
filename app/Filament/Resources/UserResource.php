<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Agency;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Management';
    protected static ?string $modelLabel = 'User';
    protected static ?string $pluralModelLabel = 'Users';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('email')
                ->email()
                ->unique(ignoreRecord: true)
                ->required(),
            Forms\Components\Select::make('roles')
                ->label('Roles')
                ->multiple()
                ->relationship('roles', 'name')
                ->preload()
                ->required()   
                ->getOptionLabelFromRecordUsing(fn ($record) => ucfirst($record->name)),
            Forms\Components\Select::make('agency_id')
                ->label('Agency')
                ->relationship('agency', 'name')
                ->preload()
                ->placeholder('No agency')
                ->searchable(),
            Forms\Components\TextInput::make('password')
                ->password()
                ->required(fn(string $operation): bool => $operation === 'create')
                ->dehydrateStateUsing(fn($state) => bcrypt($state))
                ->label('Password'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\BadgeColumn::make('roles.name')
                    ->label('Rol')
                    ->colors([
                        'success' => fn ($state): bool => $state === 'admin',
                        'warning' => fn ($state): bool => $state === 'organizer',
                        'info'    => fn ($state): bool => $state === 'verifier',
                        'gray'    => fn ($state): bool => $state === 'attender',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->separator(', '),
                Tables\Columns\TextColumn::make('agency.name')->label('Agency'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    # --- 🔐 Control de acceso por roles ---
    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole('admin');
    }

    public static function canView($record): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole('admin');
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole('admin');
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole('admin');
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole('admin');
    }

    public static function canDeleteAny(): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole('admin');
    }
}

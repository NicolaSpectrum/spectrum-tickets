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
            Forms\Components\Select::make('role')
                ->options([
                    'admin' => 'Admin',
                    'organizer' => 'Organizer',
                    'verifier' => 'Verifier',
                    'attender' => 'Attender',
                ])
                ->required(),
            Forms\Components\Select::make('agency_id')
                ->label('Agency')
                ->options(Agency::pluck('name', 'id'))
                ->searchable()
                ->visible(fn ($get) => in_array($get('role'), ['organizer', 'verifier'])),
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
                Tables\Columns\BadgeColumn::make('role')
                    ->colors([
                        'success' => 'admin',
                        'warning' => 'organizer',
                        'info' => 'verifier',
                        'gray' => 'attender',
                    ]),
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

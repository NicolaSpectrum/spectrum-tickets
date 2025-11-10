<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgencyResource\Pages;
use App\Filament\Resources\AgencyResource\RelationManagers;
use App\Models\Agency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class AgencyResource extends Resource
{
    protected static ?string $model = Agency::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Management';
    protected static ?string $modelLabel = 'Agency';
    protected static ?string $pluralModelLabel = 'Agencies';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label('Agency Name'),

            Forms\Components\TextInput::make('contact_name')
                ->maxLength(255)
                ->label('Contact Person'),

            Forms\Components\TextInput::make('email')
                ->email()
                ->maxLength(255)
                ->label('Contact Email'),

            Forms\Components\TextInput::make('phone')
                ->tel()
                ->maxLength(50)
                ->label('Phone Number'),

            Forms\Components\Textarea::make('address')
                ->rows(2)
                ->label('Address'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('contact_name')
                ->label('Contact'),

            Tables\Columns\TextColumn::make('email')
                ->label('Email'),

            Tables\Columns\TextColumn::make('phone')
                ->label('Phone'),

            Tables\Columns\TextColumn::make('address')
                ->label('Address')
                ->limit(30),
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
            'index' => Pages\ListAgencies::route('/'),
            'create' => Pages\CreateAgency::route('/create'),
            'edit' => Pages\EditAgency::route('/{record}/edit'),
        ];
    }

    # --- 🔐 Control de acceso ---
    public static function canViewAny(): bool
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
}
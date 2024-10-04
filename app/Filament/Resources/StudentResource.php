<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('level')
                                    ->options([
                                        'TK' => 'TK',
                                        'SD' => 'SD',
                                        'SMP' => 'SMP',
                                        'SMA' => 'SMA',
                                    ])
                                    ->native(false)
                                    ->required(),
                                Forms\Components\TextInput::make('grade')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('price')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('fee')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\ToggleButtons::make('is_active')
                                    ->default(true),
                            ])
                    ])

            ]);
    }

    public static function table(Table $table): Table
    {
        /** @var \App\Models\User */
        $User = Auth::user();
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('level')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grade')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->numeric()
                    ->prefix('Rp. ')
                    ->visible(fn(): bool => $User->hasRole('super_admin')),
                Tables\Columns\TextColumn::make('fee')
                    ->prefix('Rp. ')
                    ->numeric(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->visible(fn(): bool => $User->hasRole('super_admin'))
                    ->label('Active')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active', 'Active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Not Active',
                    ])
                    ->label('Status')
                    ->placeholder('Select an option')
                    ->default('1'),

            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                ->color('warning'),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        // return students with is_active = true
        return static::getModel()::where('is_active', true)->count();
    }
}

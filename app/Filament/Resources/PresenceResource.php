<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PresenceResource\Pages;
use App\Filament\Resources\PresenceResource\RelationManagers;
use App\Models\Classes;
use App\Models\Presence;
use App\Models\Student;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Container\Attributes\Auth as AttributesAuth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class PresenceResource extends Resource
{
    protected static ?string $model = Presence::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Hidden::make('user_id'),
                                Forms\Components\Select::make('student_id')
                                    ->options(Student::where('is_active', true) // Only show active students
                                        ->orderBy('name') // Sort by name
                                        ->pluck('name', 'id')), // Get name and id
                                Forms\Components\DatePicker::make('date')
                                    ->required(),
                                Forms\Components\TimePicker::make('time')
                                    ->required(),
                                Forms\Components\FileUpload::make('image_doc')
                                    ->label('Documentation Image')
                                    ->image()
                                    ->required(),
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        /** @var \App\Models\User */
        $User = Auth::user();
        return $table
            ->groups([
                'date',
                'student.name',
            ])
            ->modifyQueryUsing(function (Builder $query) {
                /** @var \App\Models\User */
                $User = Auth::user();
                $is_super_admin = $User->hasRole('super_admin');

                if (!$is_super_admin) {
                    $query->where('user_id', Auth::user()->id);
                }
            })
            ->columns([
                // Display the name of the user (tutor) instead of user_id
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Tutor Name')
                    ->sortable()
                    ->searchable()
                    ->visible(fn(): bool => $User->hasRole('super_admin')),
                // Display the name of the student instead of student_id
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student Name')
                    ->sortable()
                    ->searchable()
                    ->description(fn(Presence $record): string => $record->student->level),
                Tables\Columns\TextColumn::make('date')
                    ->label('Date Time')
                    ->sortable()
                    ->searchable()
                    ->date()
                    ->description(fn(Presence $record): string => $record->time),
                Tables\Columns\TextColumn::make('student.price')
                    ->label('Class Price')
                    ->numeric()
                    ->prefix('Rp. ')
                    ->visible(fn(): bool => $User->hasRole('super_admin')),
                Tables\Columns\TextColumn::make('price_fee_difference')
                    ->label('Tutor Fee')
                    ->numeric()
                    ->getStateUsing(fn($record) => $record->student->price - $record->student->fee) // Calculate the difference
                    ->prefix('Rp. '),
                Tables\Columns\TextColumn::make('student.fee')
                    ->label('Fee')
                    ->numeric()
                    ->prefix('Rp. ')
                    ->visible(fn(): bool => $User->hasRole('super_admin')),
                Tables\Columns\ImageColumn::make('image_doc')
                    ->circular(),
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
                DateRangeFilter::make('date', 'Presence Date')
                    ->startDate(Carbon::now()->startOfWeek(Carbon::MONDAY)) // Start of the week (Monday)
                    ->endDate(Carbon::now()) // Current date as end date
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('warning'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPresences::route('/'),
            'create' => Pages\CreatePresence::route('/create'),
            'edit' => Pages\EditPresence::route('/{record}/edit'),
        ];
    }
}

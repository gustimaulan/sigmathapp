<?php

namespace App\Filament\Pages;

use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class AdminDashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        // Determine the number of columns based on the user's role
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        $columns = $currentUser->hasRole('super_admin') ? 3 : 2;

        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('user_id')
                            ->label('Tutor')
                            ->options(User::all()->pluck('name', 'id'))
                            ->visible(function () {
                                /** @var \App\Models\User $currentUser */
                                $currentUser = Auth::user();
                                return $currentUser->hasRole('super_admin');
                            }),
                        DatePicker::make('startDate')
                            ->default(now()->startOfWeek(Carbon::MONDAY)) // Set default to this week's Monday
                            ->maxDate(fn(Get $get) => $get('endDate') ?: now()),
                        DatePicker::make('endDate')
                            ->default(now()) // Set default to today
                            ->minDate(fn(Get $get) => $get('startDate') ?: now())
                            ->maxDate(now()),
                    ])
                    ->columns($columns),
            ]);
    }
}

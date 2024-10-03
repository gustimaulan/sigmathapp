<?php

namespace App\Filament\Widgets;

use App\Models\Presence;
use App\Models\Student;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        // Get the authenticated user
        /** @var \App\Models\User */
        $user = Auth::user();

        // Date filter logic
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? now(); // Default to today's date if not set

        // Parse dates if not null
        $startDate = !is_null($startDate) ? Carbon::parse($startDate) : null;
        $endDate = Carbon::parse($endDate);

        // Build the query for the Presence records
        $queryPresence = Presence::query();

        // Apply date filters first if available
        if ($startDate) {
            $queryPresence->whereBetween('date', [$startDate, $endDate]);
        }

        // Apply user role filtering
        if (!$user->hasRole('super_admin')) {
            // For Tutor role, show only their own records
            $queryPresence->where('user_id', $user->id);
        }

        // Get the count of filtered records
        $totalRecords = $queryPresence->count() ?? 0;

        // Sum the fees for each presence record (not just unique students)
        $totalPrice = $queryPresence->with('student') // Eager load the related student
            ->get() // Get the presences
            ->sum(fn($presence) => $presence->student->price ?? 0); // Sum student fees per presence

        // Sum the fees for each presence record (not just unique students)
        $totalFee = $queryPresence->with('student') // Eager load the related student
            ->get() // Get the presences
            ->sum(fn($presence) => $presence->student->fee ?? 0); // Sum student fees per presence

        $totalProfit = $totalPrice - $totalFee;
        // Get the count of unique students
        $totalStudents = Student::whereIn('id', $queryPresence->pluck('student_id'))->count();

        // Build the stats array dynamically
        $stats = [];

        // Conditional logic for adding the 'Total Fees' or 'Total Profit' stat
        if (!$user->hasRole('super_admin')) {
            $stats[] = Stat::make('Estimated Fees', $totalFee)
                ->description('Rp. ')
                ->color('success')
                ->descriptionIcon('heroicon-s-wallet');
        } else {
            $stats[] = Stat::make('Estimated Profit', $totalProfit)
                ->description('Rp. ')
                ->color('success')
                ->descriptionIcon('heroicon-s-currency-dollar');
        }

        // Other stats (Total Presences, Total Unique Students)
        $stats[] = Stat::make('Total Presences', $totalRecords)
            ->description('Presences')
            ->color('primary')
            ->descriptionIcon('heroicon-s-rectangle-stack');

        $stats[] = Stat::make('Total Unique Students', $totalStudents)
            ->description('Students')
            ->color('primary')
            ->descriptionIcon('heroicon-s-user-group');


        // Return a stats overview card with the total record count
        return $stats;
    }
}

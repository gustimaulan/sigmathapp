<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            // add import action only visible to super_admin
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->color("success")
                ->visible(fn (): bool => Auth::user()->hasRole('super_admin')),

        ];
    }
}

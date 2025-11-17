<?php

namespace App\Filament\Resources\TournamentResource\Pages;

use App\Filament\Resources\TournamentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTournaments extends ListRecords
{
    protected static string $resource = TournamentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    /**
     * Optimize query by eager loading counts
     */
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->withCount([
                'registrations as registrations_count' => function ($q) {
                    $q->whereIn('status', ['registered', 'checked_in']);
                }
            ]);
    }
}

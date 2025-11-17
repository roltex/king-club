<?php

namespace App\Filament\Resources\PlayerResource\Pages;

use App\Filament\Resources\PlayerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPlayers extends ListRecords
{
    protected static string $resource = PlayerResource::class;

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
                'registrations as total_tournaments',
                'registrations as active_tournaments' => function ($q) {
                    $q->whereIn('status', ['registered', 'checked_in']);
                }
            ]);
    }
}

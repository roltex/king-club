<?php

namespace App\Filament\Resources\CashGameResource\Pages;

use App\Filament\Resources\CashGameResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCashGames extends ListRecords
{
    protected static string $resource = CashGameResource::class;

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
                'seats as active_seats_count' => function ($q) {
                    $q->whereIn('status', ['seated', 'playing', 'away']);
                }
            ]);
    }
}


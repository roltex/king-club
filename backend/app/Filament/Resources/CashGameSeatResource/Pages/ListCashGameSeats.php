<?php

namespace App\Filament\Resources\CashGameSeatResource\Pages;

use App\Filament\Resources\CashGameSeatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCashGameSeats extends ListRecords
{
    protected static string $resource = CashGameSeatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    /**
     * Optimize query by eager loading relationships
     */
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->with(['cashGame:id,name', 'player:id,first_name,last_name,email']);
    }
}


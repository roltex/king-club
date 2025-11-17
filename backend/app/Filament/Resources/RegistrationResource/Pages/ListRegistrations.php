<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\RegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListRegistrations extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

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
            ->with(['tournament:id,name', 'player:id,first_name,last_name,email']);
    }
}

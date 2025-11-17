<?php

namespace App\Filament\Resources\CashGameSeatResource\Pages;

use App\Filament\Resources\CashGameSeatResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCashGameSeat extends ViewRecord
{
    protected static string $resource = CashGameSeatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}


<?php

namespace App\Filament\Resources\CashGameResource\Pages;

use App\Filament\Resources\CashGameResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCashGame extends EditRecord
{
    protected static string $resource = CashGameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
    
    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }
    
    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return false;
    }
}


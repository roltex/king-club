<?php

namespace App\Filament\Resources\CashGameResource\Pages;

use App\Filament\Resources\CashGameResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCashGame extends CreateRecord
{
    protected static string $resource = CashGameResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }
    
    public function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }
    
    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return false;
    }
}


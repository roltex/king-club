<?php

namespace App\Filament\Resources\CashGameSeatResource\Pages;

use App\Filament\Resources\CashGameSeatResource;
use App\Models\CashGame;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditCashGameSeat extends EditRecord
{
    protected static string $resource = CashGameSeatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Auto-assign seat if not manually set and status is not waiting
        if (empty($data['seat_number']) && $data['status'] !== 'waiting') {
            $cashGame = $this->record->cashGame ?? CashGame::find($data['cash_game_id'] ?? $this->record->cash_game_id);
            
            if ($cashGame) {
                $seatNumber = $this->findAvailableSeat($cashGame, $this->record->id ?? null);
                
                if ($seatNumber) {
                    $data['seat_number'] = $seatNumber;
                    
                    Notification::make()
                        ->title('Seat Auto-Assigned')
                        ->body("Seat {$seatNumber} has been automatically assigned")
                        ->success()
                        ->send();
                } else {
                    // No seats available, move to waiting list if enabled
                    if ($cashGame->enable_waiting_list && $data['status'] !== 'waiting') {
                        $data['status'] = 'waiting';
                        $data['seat_number'] = null;
                        
                        // Calculate waiting position
                        $waitingCount = $cashGame->seats()
                            ->where('status', 'waiting')
                            ->where('id', '!=', $this->record->id ?? null)
                            ->count();
                        $data['waiting_position'] = $waitingCount + 1;
                        
                        Notification::make()
                            ->title('Cash Game Full')
                            ->body('Player moved to waiting list')
                            ->warning()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('No Seats Available')
                            ->body('Cash game is full')
                            ->warning()
                            ->send();
                    }
                }
            }
        }

        // If status changed to waiting, clear seat_number
        if ($data['status'] === 'waiting' && !empty($data['seat_number'])) {
            $data['seat_number'] = null;
        }

        // If status changed from waiting to active, ensure seat_number is set
        if (in_array($data['status'], ['seated', 'playing', 'away']) && empty($data['seat_number'])) {
            $cashGame = $this->record->cashGame ?? CashGame::find($data['cash_game_id'] ?? $this->record->cash_game_id);
            
            if ($cashGame) {
                $seatNumber = $this->findAvailableSeat($cashGame, $this->record->id ?? null);
                
                if ($seatNumber) {
                    $data['seat_number'] = $seatNumber;
                }
            }
        }

        return $data;
    }

    private function findAvailableSeat(CashGame $cashGame, ?string $excludeSeatId = null): ?int
    {
        // Get all occupied seat numbers (filter out NULL values)
        $query = $cashGame->seats()
            ->whereIn('status', ['seated', 'playing', 'away'])
            ->whereNotNull('seat_number');
        
        // Exclude current seat if editing
        if ($excludeSeatId) {
            $query->where('id', '!=', $excludeSeatId);
        }
        
        $occupiedSeats = $query->pluck('seat_number')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Find available seats by comparing with all possible seat numbers
        $allSeats = range(1, $cashGame->seats_per_table);
        $availableSeats = array_diff($allSeats, $occupiedSeats);

        if (empty($availableSeats)) {
            return null;
        }

        // Return the first available seat number
        return reset($availableSeats);
    }
}


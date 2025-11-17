<?php

namespace App\Filament\Resources\CashGameSeatResource\Pages;

use App\Filament\Resources\CashGameSeatResource;
use App\Models\CashGame;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateCashGameSeat extends CreateRecord
{
    protected static string $resource = CashGameSeatResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-assign seat if not manually set and status is not waiting
        if (empty($data['seat_number']) && !empty($data['cash_game_id']) && $data['status'] !== 'waiting') {
            $cashGame = CashGame::find($data['cash_game_id']);
            
            if ($cashGame) {
                $seatNumber = $this->findAvailableSeat($cashGame);
                
                if ($seatNumber) {
                    $data['seat_number'] = $seatNumber;
                    
                    Notification::make()
                        ->title('Seat Auto-Assigned')
                        ->body("Seat {$seatNumber} has been automatically assigned")
                        ->success()
                        ->send();
                } else {
                    // No seats available, move to waiting list if enabled
                    if ($cashGame->enable_waiting_list) {
                        $data['status'] = 'waiting';
                        $data['seat_number'] = null;
                        
                        // Calculate waiting position
                        $waitingCount = $cashGame->seats()
                            ->where('status', 'waiting')
                            ->count();
                        $data['waiting_position'] = $waitingCount + 1;
                        
                        Notification::make()
                            ->title('Cash Game Full')
                            ->body('Player added to waiting list')
                            ->warning()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('No Seats Available')
                            ->body('Cash game is full and waiting list is not enabled')
                            ->danger()
                            ->send();
                    }
                }
            }
        }

        // Set sat_down_at if not set
        if (empty($data['sat_down_at'])) {
            $data['sat_down_at'] = now();
        }

        // Set current_stack to buy_in_amount if not set
        if (empty($data['current_stack']) && !empty($data['buy_in_amount'])) {
            $data['current_stack'] = $data['buy_in_amount'];
        }

        return $data;
    }

    private function findAvailableSeat(CashGame $cashGame): ?int
    {
        // Get all occupied seat numbers (filter out NULL values)
        $occupiedSeats = $cashGame->seats()
            ->whereIn('status', ['seated', 'playing', 'away'])
            ->whereNotNull('seat_number')
            ->pluck('seat_number')
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


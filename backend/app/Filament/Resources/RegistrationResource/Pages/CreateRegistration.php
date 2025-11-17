<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\RegistrationResource;
use App\Models\Tournament;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateRegistration extends CreateRecord
{
    protected static string $resource = RegistrationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-assign seat if not manually set and status is registered
        if (in_array($data['status'], ['registered', 'checked_in'])) {
            if (empty($data['table_number']) || empty($data['seat_number'])) {
                $tournament = Tournament::find($data['tournament_id']);
                
                if ($tournament && $tournament->auto_seat_assignment) {
                    // Try to find an available seat
                    $seat = $this->findAvailableSeat($tournament);
                    
                    if ($seat) {
                        $data['table_number'] = $seat['table'];
                        $data['seat_number'] = $seat['seat'];
                        
                        Notification::make()
                            ->title('Seat Auto-Assigned')
                            ->body("Table {$seat['table']}, Seat {$seat['seat']}")
                            ->success()
                            ->send();
                    } else {
                        // No seats available, move to waiting list
                        $data['status'] = 'waiting';
                        $data['table_number'] = null;
                        $data['seat_number'] = null;
                        
                        // Calculate waiting position
                        $waitingCount = $tournament->registrations()
                            ->where('status', 'waiting')
                            ->count();
                        $data['waiting_position'] = $waitingCount + 1;
                        
                        Notification::make()
                            ->title('Tournament Full')
                            ->body('Player added to waiting list')
                            ->warning()
                            ->send();
                    }
                }
            }
        }

        // Generate QR code data
        if ($data['status'] !== 'cancelled' && empty($data['qr_code'])) {
            $frontendUrl = config('app.url', 'http://localhost:5173');
            // QR code will be generated after record is created
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $registration = $this->record;
        
        // Generate QR code after creation (needs ID)
        if (empty($registration->qr_code)) {
            $frontendUrl = config('app.url', 'http://localhost:5173');
            $qrData = "{$frontendUrl}/checkin?id={$registration->id}";
            $checksum = hash('sha256', $registration->id . $registration->phone);
            
            $registration->update([
                'qr_code' => $qrData,
                'qr_checksum' => $checksum,
            ]);
        }

        // Update tournament prize pool
        if ($registration->tournament) {
            $registration->tournament->updatePrizePool();
        }
    }

    private function findAvailableSeat(Tournament $tournament): ?array
    {
        $maxAttempts = 100;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $tableNumber = rand(1, $tournament->total_tables);
            $seatNumber = rand(1, $tournament->seats_per_table);

            // Check if seat is available
            $exists = $tournament->registrations()
                ->where('table_number', $tableNumber)
                ->where('seat_number', $seatNumber)
                ->whereIn('status', ['registered', 'checked_in'])
                ->exists();

            if (!$exists) {
                return [
                    'table' => $tableNumber,
                    'seat' => $seatNumber,
                ];
            }

            $attempt++;
        }

        return null;
    }
}

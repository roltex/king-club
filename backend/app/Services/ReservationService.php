<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Tournament;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Exception;

class ReservationService
{
    /**
     * Create a new reservation for a tournament
     */
    public function createReservation(array $data, ?string $tournamentId = null): Reservation
    {
        // Get tournament or use legacy config
        if ($tournamentId) {
            $tournament = Tournament::findOrFail($tournamentId);
            
            if (!$tournament->canRegister()) {
                throw new Exception('Registration is not open for this tournament');
            }
            
            $data['tournament_id'] = $tournamentId;
            return $this->createTournamentReservation($data, $tournament);
        }
        
        // Legacy mode: use config values (for backwards compatibility)
        return $this->createLegacyReservation($data);
    }

    /**
     * Create tournament-specific reservation
     */
    private function createTournamentReservation(array $data, Tournament $tournament): Reservation
    {
        // Check available seats in this tournament
        $occupiedSeats = $tournament->reservations()
            ->whereIn('status', ['reserved', 'checked_in'])
            ->count();

        if ($occupiedSeats >= $tournament->total_seats) {
            if ($tournament->waiting_list_enabled) {
                return $this->createWaitingListEntry($data, $tournament);
            }
            throw new Exception('Tournament is full and waiting list is not enabled');
        }

        if ($tournament->auto_seat_assignment) {
            return $this->assignSeat($data, $tournament);
        }

        // Manual seat assignment
        return $this->createPendingReservation($data, $tournament);
    }

    /**
     * Create legacy reservation (backwards compatibility)
     */
    private function createLegacyReservation(array $data): Reservation
    {
        $totalTables = config('tournament.total_tables', 6);
        $seatsPerTable = config('tournament.seats_per_table', 9);
        $totalSeats = config('tournament.total_seats', 54);

        // Check available seats (global, no tournament filter)
        $occupiedSeats = Reservation::whereNull('tournament_id')
            ->whereIn('status', ['reserved', 'checked_in'])
            ->count();

        if ($occupiedSeats >= $totalSeats) {
            return $this->createLegacyWaitingListEntry($data);
        }

        return $this->assignLegacySeat($data, $totalTables, $seatsPerTable);
    }

    /**
     * Assign a random available seat in tournament
     */
    private function assignSeat(array $data, Tournament $tournament): Reservation
    {
        $maxAttempts = 100;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $tableNumber = rand(1, $tournament->total_tables);
            $seatNumber = rand(1, $tournament->seats_per_table);

            // Check if seat is available in this tournament
            $existingSeat = $tournament->reservations()
                ->where('table_number', $tableNumber)
                ->where('seat_number', $seatNumber)
                ->whereIn('status', ['reserved', 'checked_in'])
                ->exists();

            if (!$existingSeat) {
                $reservation = Reservation::create([
                    'tournament_id' => $tournament->id,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'phone' => $data['phone'],
                    'email' => $data['email'] ?? null,
                    'status' => 'reserved',
                    'table_number' => $tableNumber,
                    'seat_number' => $seatNumber,
                ]);

                // Generate QR code
                $this->generateQrCode($reservation);

                // Update tournament prize pool
                $tournament->updatePrizePool();

                return $reservation->fresh();
            }

            $attempt++;
        }

        // If we couldn't find a seat after max attempts, add to waiting list
        if ($tournament->waiting_list_enabled) {
            return $this->createWaitingListEntry($data, $tournament);
        }

        throw new Exception('No available seats found');
    }

    /**
     * Assign legacy seat (backwards compatibility)
     */
    private function assignLegacySeat(array $data, int $totalTables, int $seatsPerTable): Reservation
    {
        $maxAttempts = 100;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $tableNumber = rand(1, $totalTables);
            $seatNumber = rand(1, $seatsPerTable);

            $existingSeat = Reservation::whereNull('tournament_id')
                ->where('table_number', $tableNumber)
                ->where('seat_number', $seatNumber)
                ->whereIn('status', ['reserved', 'checked_in'])
                ->exists();

            if (!$existingSeat) {
                $reservation = Reservation::create([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'phone' => $data['phone'],
                    'email' => $data['email'] ?? null,
                    'status' => 'reserved',
                    'table_number' => $tableNumber,
                    'seat_number' => $seatNumber,
                ]);

                $this->generateQrCode($reservation);
                return $reservation->fresh();
            }

            $attempt++;
        }

        return $this->createLegacyWaitingListEntry($data);
    }

    /**
     * Create waiting list entry for tournament
     */
    private function createWaitingListEntry(array $data, Tournament $tournament): Reservation
    {
        $waitingCount = $tournament->reservations()
            ->where('status', 'waiting')
            ->count();

        if ($tournament->max_waiting_list && $waitingCount >= $tournament->max_waiting_list) {
            throw new Exception('Waiting list is full');
        }

        $waitingPosition = $waitingCount + 1;

        return Reservation::create([
            'tournament_id' => $tournament->id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'status' => 'waiting',
            'waiting_position' => $waitingPosition,
        ]);
    }

    /**
     * Create legacy waiting list entry
     */
    private function createLegacyWaitingListEntry(array $data): Reservation
    {
        $waitingCount = Reservation::whereNull('tournament_id')
            ->where('status', 'waiting')
            ->count();
        $waitingPosition = $waitingCount + 1;

        return Reservation::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'status' => 'waiting',
            'waiting_position' => $waitingPosition,
        ]);
    }

    /**
     * Create pending reservation (manual seat assignment)
     */
    private function createPendingReservation(array $data, Tournament $tournament): Reservation
    {
        return Reservation::create([
            'tournament_id' => $tournament->id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'status' => 'reserved',
        ]);
    }

    /**
     * Generate QR code for reservation
     */
    private function generateQrCode(Reservation $reservation): void
    {
        $frontendUrl = config('tournament.frontend_url', 'http://localhost:5173');
        $qrData = "{$frontendUrl}/checkin?id={$reservation->id}";
        $checksum = hash('sha256', $reservation->id . $reservation->phone);

        $reservation->update([
            'qr_code' => $qrData,
            'qr_checksum' => $checksum,
        ]);
    }

    /**
     * Process check-in
     */
    public function checkIn(string $reservationId): array
    {
        $reservation = Reservation::with('tournament')->find($reservationId);

        if (!$reservation) {
            return [
                'success' => false,
                'message' => 'Reservation not found',
            ];
        }

        if ($reservation->status === 'waiting') {
            return [
                'success' => false,
                'message' => 'Cannot check in from waiting list',
            ];
        }

        if ($reservation->status === 'cancelled') {
            return [
                'success' => false,
                'message' => 'This reservation has been cancelled',
            ];
        }

        if ($reservation->status === 'checked_in') {
            return [
                'success' => false,
                'message' => 'Already checked in at ' . $reservation->checkin_time->format('Y-m-d H:i:s'),
            ];
        }

        // Check if tournament is using QR check-in
        if ($reservation->tournament && !$reservation->tournament->enable_qr_checkin) {
            return [
                'success' => false,
                'message' => 'QR check-in is not enabled for this tournament',
            ];
        }

        $reservation->update([
            'status' => 'checked_in',
            'checkin_time' => now(),
        ]);

        return [
            'success' => true,
            'user' => $reservation->full_name,
            'table' => $reservation->table_number,
            'seat' => $reservation->seat_number,
            'tournament' => $reservation->tournament?->name,
            'checkin_time' => $reservation->checkin_time->toIso8601String(),
        ];
    }

    /**
     * Cancel reservation
     */
    public function cancelReservation(string $reservationId): bool
    {
        $reservation = Reservation::with('tournament')->find($reservationId);

        if (!$reservation || $reservation->status === 'checked_in') {
            return false;
        }

        $tournament = $reservation->tournament;

        $reservation->update(['status' => 'cancelled']);

        // Try to promote someone from waiting list
        if ($tournament) {
            $this->promoteFromWaitingList($tournament);
            $tournament->updatePrizePool();
        } else {
            $this->promoteLegacyWaitingList();
        }

        return true;
    }

    /**
     * Promote first person from waiting list to available seat
     */
    public function promoteFromWaitingList(Tournament $tournament): ?Reservation
    {
        $waitingPerson = $tournament->reservations()
            ->where('status', 'waiting')
            ->orderBy('waiting_position')
            ->first();

        if (!$waitingPerson) {
            return null;
        }

        $occupiedSeats = $tournament->reservations()
            ->whereIn('status', ['reserved', 'checked_in'])
            ->count();

        if ($occupiedSeats >= $tournament->total_seats) {
            return null;
        }

        // Assign seat to waiting person
        $maxAttempts = 100;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $tableNumber = rand(1, $tournament->total_tables);
            $seatNumber = rand(1, $tournament->seats_per_table);

            $existingSeat = $tournament->reservations()
                ->where('table_number', $tableNumber)
                ->where('seat_number', $seatNumber)
                ->whereIn('status', ['reserved', 'checked_in'])
                ->exists();

            if (!$existingSeat) {
                $waitingPerson->update([
                    'status' => 'reserved',
                    'table_number' => $tableNumber,
                    'seat_number' => $seatNumber,
                    'waiting_position' => null,
                ]);

                $this->generateQrCode($waitingPerson);
                $this->reorderWaitingList($tournament);
                $tournament->updatePrizePool();

                return $waitingPerson->fresh();
            }

            $attempt++;
        }

        return null;
    }

    /**
     * Promote from legacy waiting list
     */
    private function promoteLegacyWaitingList(): ?Reservation
    {
        $totalTables = config('tournament.total_tables', 6);
        $seatsPerTable = config('tournament.seats_per_table', 9);
        $totalSeats = config('tournament.total_seats', 54);

        $waitingPerson = Reservation::whereNull('tournament_id')
            ->where('status', 'waiting')
            ->orderBy('waiting_position')
            ->first();

        if (!$waitingPerson) {
            return null;
        }

        $occupiedSeats = Reservation::whereNull('tournament_id')
            ->whereIn('status', ['reserved', 'checked_in'])
            ->count();

        if ($occupiedSeats >= $totalSeats) {
            return null;
        }

        $maxAttempts = 100;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $tableNumber = rand(1, $totalTables);
            $seatNumber = rand(1, $seatsPerTable);

            $existingSeat = Reservation::whereNull('tournament_id')
                ->where('table_number', $tableNumber)
                ->where('seat_number', $seatNumber)
                ->whereIn('status', ['reserved', 'checked_in'])
                ->exists();

            if (!$existingSeat) {
                $waitingPerson->update([
                    'status' => 'reserved',
                    'table_number' => $tableNumber,
                    'seat_number' => $seatNumber,
                    'waiting_position' => null,
                ]);

                $this->generateQrCode($waitingPerson);
                $this->reorderLegacyWaitingList();

                return $waitingPerson->fresh();
            }

            $attempt++;
        }

        return null;
    }

    /**
     * Reorder waiting list positions for tournament
     */
    private function reorderWaitingList(Tournament $tournament): void
    {
        $waitingList = $tournament->reservations()
            ->where('status', 'waiting')
            ->orderBy('waiting_position')
            ->get();

        $position = 1;
        foreach ($waitingList as $waiting) {
            $waiting->update(['waiting_position' => $position++]);
        }
    }

    /**
     * Reorder legacy waiting list
     */
    private function reorderLegacyWaitingList(): void
    {
        $waitingList = Reservation::whereNull('tournament_id')
            ->where('status', 'waiting')
            ->orderBy('waiting_position')
            ->get();

        $position = 1;
        foreach ($waitingList as $waiting) {
            $waiting->update(['waiting_position' => $position++]);
        }
    }

    /**
     * Get tournament statistics
     */
    public function getStatistics(?string $tournamentId = null): array
    {
        $query = Reservation::query();

        if ($tournamentId) {
            $tournament = Tournament::find($tournamentId);
            if (!$tournament) {
                throw new Exception('Tournament not found');
            }
            $query->where('tournament_id', $tournamentId);
            $totalSeats = $tournament->total_seats;
        } else {
            // Legacy mode
            $query->whereNull('tournament_id');
            $totalSeats = config('tournament.total_seats', 54);
        }

        $reserved = $query->clone()->where('status', 'reserved')->count();
        $checkedIn = $query->clone()->where('status', 'checked_in')->count();
        $waiting = $query->clone()->where('status', 'waiting')->count();
        $cancelled = $query->clone()->where('status', 'cancelled')->count();

        $occupied = $reserved + $checkedIn;
        $available = $totalSeats - $occupied;

        return [
            'total_seats' => $totalSeats,
            'occupied_seats' => $occupied,
            'available_seats' => max(0, $available),
            'reserved' => $reserved,
            'checked_in' => $checkedIn,
            'waiting_list' => $waiting,
            'cancelled' => $cancelled,
        ];
    }

    /**
     * Get table layout
     */
    public function getTableLayout(?string $tournamentId = null): array
    {
        if ($tournamentId) {
            $tournament = Tournament::findOrFail($tournamentId);
            $totalTables = $tournament->total_tables;
            $seatsPerTable = $tournament->seats_per_table;
            $query = $tournament->reservations();
        } else {
            // Legacy mode
            $totalTables = config('tournament.total_tables', 6);
            $seatsPerTable = config('tournament.seats_per_table', 9);
            $query = Reservation::whereNull('tournament_id');
        }

        $tables = [];

        for ($tableNum = 1; $tableNum <= $totalTables; $tableNum++) {
            $seats = [];

            for ($seatNum = 1; $seatNum <= $seatsPerTable; $seatNum++) {
                $reservation = $query->clone()
                    ->where('table_number', $tableNum)
                    ->where('seat_number', $seatNum)
                    ->whereIn('status', ['reserved', 'checked_in'])
                    ->first();

                $seats[] = [
                    'seat_number' => $seatNum,
                    'occupied' => $reservation !== null,
                    'status' => $reservation?->status,
                    'player' => $reservation ? [
                        'name' => $reservation->full_name,
                        'phone' => $reservation->phone,
                        'checked_in' => $reservation->isCheckedIn(),
                    ] : null,
                ];
            }

            $tables[] = [
                'table_number' => $tableNum,
                'seats' => $seats,
            ];
        }

        return $tables;
    }
}

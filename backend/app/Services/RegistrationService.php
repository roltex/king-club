<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\Tournament;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Exception;

class RegistrationService
{
    /**
     * Register a player for a tournament
     *
     * @param array $data Player data (player_id required, optionally first_name, last_name, phone, email)
     * @param string $tournamentId Tournament UUID
     * @return Registration
     * @throws Exception
     */
    public function register(array $data, string $tournamentId): Registration
    {
        $tournament = Tournament::findOrFail($tournamentId);

        // Check if registration is open
        if (!$tournament->canRegister()) {
            throw new Exception('Registration is not open for this tournament');
        }

        // Check if player_id is provided (required for new auth system)
        if (!isset($data['player_id'])) {
            throw new Exception('Player ID is required. Please login first.');
        }

        // Check for duplicate registration by player_id
        $existing = $tournament->registrations()
            ->where('player_id', $data['player_id'])
            ->whereIn('status', ['registered', 'waiting', 'checked_in'])
            ->first();

        if ($existing) {
            throw new Exception('You are already registered for this tournament');
        }

        $data['tournament_id'] = $tournamentId;

        // Check available seats
        $occupiedSeats = $tournament->registrations()
            ->whereIn('status', ['registered', 'checked_in'])
            ->count();

        if ($occupiedSeats >= $tournament->total_seats) {
            // Tournament full - add to waiting list if enabled
            if ($tournament->waiting_list_enabled) {
                return $this->addToWaitingList($data, $tournament);
            }
            throw new Exception('Tournament is full and waiting list is not enabled');
        }

        // Register player
        if ($tournament->auto_seat_assignment) {
            return $this->registerWithSeatAssignment($data, $tournament);
        }

        // Manual seat assignment (by admin later)
        return $this->registerWithoutSeat($data, $tournament);
    }

    /**
     * Register player with automatic seat assignment
     */
    private function registerWithSeatAssignment(array $data, Tournament $tournament): Registration
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
                $registration = Registration::create([
                    'tournament_id' => $tournament->id,
                    'player_id' => $data['player_id'],
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'phone' => $data['phone'],
                    'email' => $data['email'] ?? null,
                    'status' => 'registered',
                    'table_number' => $tableNumber,
                    'seat_number' => $seatNumber,
                ]);

                // Generate QR code for check-in
                if ($tournament->enable_qr_checkin) {
                    $this->generateQrCode($registration);
                }

                // Update tournament prize pool
                $tournament->updatePrizePool();

                return $registration->fresh();
            }

            $attempt++;
        }

        // If no seat found after max attempts, add to waiting list
        if ($tournament->waiting_list_enabled) {
            return $this->addToWaitingList($data, $tournament);
        }

        throw new Exception('Unable to assign seat. Tournament may be full.');
    }

    /**
     * Register player without seat (manual assignment by admin)
     */
    private function registerWithoutSeat(array $data, Tournament $tournament): Registration
    {
        $registration = Registration::create([
            'tournament_id' => $tournament->id,
            'player_id' => $data['player_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'status' => 'registered',
        ]);

        if ($tournament->enable_qr_checkin) {
            $this->generateQrCode($registration);
        }

        $tournament->updatePrizePool();

        return $registration->fresh();
    }

    /**
     * Add player to waiting list
     */
    private function addToWaitingList(array $data, Tournament $tournament): Registration
    {
        // Check waiting list limit
        $waitingCount = $tournament->registrations()
            ->where('status', 'waiting')
            ->count();

        if ($tournament->max_waiting_list && $waitingCount >= $tournament->max_waiting_list) {
            throw new Exception('Waiting list is full');
        }

        $waitingPosition = $waitingCount + 1;

        return Registration::create([
            'tournament_id' => $tournament->id,
            'player_id' => $data['player_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'status' => 'waiting',
            'waiting_position' => $waitingPosition,
        ]);
    }

    /**
     * Generate QR code for registration
     */
    private function generateQrCode(Registration $registration): void
    {
        $frontendUrl = config('tournament.frontend_url', 'http://localhost:5173');
        $qrData = "{$frontendUrl}/checkin?id={$registration->id}";
        $checksum = hash('sha256', $registration->id . $registration->phone);

        $registration->update([
            'qr_code' => $qrData,
            'qr_checksum' => $checksum,
        ]);
    }

    /**
     * Check in a registered player
     */
    public function checkIn(string $registrationId): array
    {
        $registration = Registration::with('tournament')->find($registrationId);

        if (!$registration) {
            return [
                'success' => false,
                'message' => 'Registration not found',
            ];
        }

        if ($registration->status === 'waiting') {
            return [
                'success' => false,
                'message' => 'Cannot check in from waiting list. Please wait for a seat assignment.',
            ];
        }

        if ($registration->status === 'cancelled') {
            return [
                'success' => false,
                'message' => 'This registration has been cancelled',
            ];
        }

        if ($registration->status === 'checked_in') {
            return [
                'success' => false,
                'message' => 'Already checked in at ' . $registration->checkin_time->format('Y-m-d H:i:s'),
            ];
        }

        // Check if QR check-in is enabled
        if ($registration->tournament && !$registration->tournament->enable_qr_checkin) {
            return [
                'success' => false,
                'message' => 'QR check-in is not enabled for this tournament',
            ];
        }

        $registration->update([
            'status' => 'checked_in',
            'checkin_time' => now(),
        ]);

        return [
            'success' => true,
            'player' => $registration->full_name,
            'table' => $registration->table_number,
            'seat' => $registration->seat_number,
            'tournament' => $registration->tournament?->name,
            'checkin_time' => $registration->checkin_time->toIso8601String(),
        ];
    }

    /**
     * Cancel a registration
     */
    public function cancel(string $registrationId): bool
    {
        $registration = Registration::with('tournament')->find($registrationId);

        if (!$registration || $registration->status === 'checked_in') {
            return false;
        }

        $tournament = $registration->tournament;
        $wasRegistered = $registration->status === 'registered';

        $registration->update(['status' => 'cancelled']);

        // If was registered, try to promote from waiting list
        if ($tournament && $wasRegistered) {
            $this->promoteFromWaitingList($tournament);
            $tournament->updatePrizePool();
        }

        return true;
    }

    /**
     * Promote first person from waiting list
     */
    public function promoteFromWaitingList(Tournament $tournament): ?Registration
    {
        $waitingPerson = $tournament->registrations()
            ->where('status', 'waiting')
            ->orderBy('waiting_position')
            ->first();

        if (!$waitingPerson) {
            return null;
        }

        $occupiedSeats = $tournament->registrations()
            ->whereIn('status', ['registered', 'checked_in'])
            ->count();

        if ($occupiedSeats >= $tournament->total_seats) {
            return null;
        }

        // Assign seat if auto-assignment enabled
        if ($tournament->auto_seat_assignment) {
            $maxAttempts = 100;
            $attempt = 0;

            while ($attempt < $maxAttempts) {
                $tableNumber = rand(1, $tournament->total_tables);
                $seatNumber = rand(1, $tournament->seats_per_table);

                $exists = $tournament->registrations()
                    ->where('table_number', $tableNumber)
                    ->where('seat_number', $seatNumber)
                    ->whereIn('status', ['registered', 'checked_in'])
                    ->exists();

                if (!$exists) {
                    $waitingPerson->update([
                        'status' => 'registered',
                        'table_number' => $tableNumber,
                        'seat_number' => $seatNumber,
                        'waiting_position' => null,
                    ]);

                    if ($tournament->enable_qr_checkin) {
                        $this->generateQrCode($waitingPerson);
                    }

                    $this->reorderWaitingList($tournament);
                    $tournament->updatePrizePool();

                    return $waitingPerson->fresh();
                }

                $attempt++;
            }
        } else {
            // Manual seat assignment - just change status
            $waitingPerson->update([
                'status' => 'registered',
                'waiting_position' => null,
            ]);

            if ($tournament->enable_qr_checkin) {
                $this->generateQrCode($waitingPerson);
            }

            $this->reorderWaitingList($tournament);
            $tournament->updatePrizePool();

            return $waitingPerson->fresh();
        }

        return null;
    }

    /**
     * Reorder waiting list positions
     */
    private function reorderWaitingList(Tournament $tournament): void
    {
        $waitingList = $tournament->registrations()
            ->where('status', 'waiting')
            ->orderBy('waiting_position')
            ->get();

        $position = 1;
        foreach ($waitingList as $waiting) {
            $waiting->update(['waiting_position' => $position++]);
        }
    }

    /**
     * Get tournament registration statistics
     */
    public function getStatistics(string $tournamentId): array
    {
        $tournament = Tournament::findOrFail($tournamentId);

        $registered = $tournament->registrations()->where('status', 'registered')->count();
        $checkedIn = $tournament->registrations()->where('status', 'checked_in')->count();
        $waiting = $tournament->registrations()->where('status', 'waiting')->count();
        $cancelled = $tournament->registrations()->where('status', 'cancelled')->count();

        $occupied = $registered + $checkedIn;
        $available = $tournament->total_seats - $occupied;

        return [
            'total_seats' => $tournament->total_seats,
            'occupied_seats' => $occupied,
            'available_seats' => max(0, $available),
            'registered' => $registered,
            'checked_in' => $checkedIn,
            'waiting_list' => $waiting,
            'cancelled' => $cancelled,
        ];
    }

    /**
     * Get table layout for tournament
     */
    public function getTableLayout(string $tournamentId): array
    {
        $tournament = Tournament::findOrFail($tournamentId);
        $tables = [];

        for ($tableNum = 1; $tableNum <= $tournament->total_tables; $tableNum++) {
            $seats = [];

            for ($seatNum = 1; $seatNum <= $tournament->seats_per_table; $seatNum++) {
                $registration = $tournament->registrations()
                    ->where('table_number', $tableNum)
                    ->where('seat_number', $seatNum)
                    ->whereIn('status', ['registered', 'checked_in'])
                    ->first();

                $seats[] = [
                    'seat_number' => $seatNum,
                    'occupied' => $registration !== null,
                    'status' => $registration?->status,
                    'player' => $registration ? [
                        'name' => $registration->full_name,
                        'phone' => $registration->phone,
                        'checked_in' => $registration->isCheckedIn(),
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


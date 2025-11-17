<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Tournament;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function __construct(
        private ReservationService $reservationService
    ) {}

    /**
     * Create a new reservation
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tournament_id' => 'nullable|uuid|exists:tournaments,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $tournamentId = $data['tournament_id'] ?? null;

        // Check for duplicate phone in same tournament
        $existingQuery = Reservation::where('phone', $data['phone'])
            ->whereIn('status', ['reserved', 'waiting', 'checked_in']);
        
        if ($tournamentId) {
            $existingQuery->where('tournament_id', $tournamentId);
        } else {
            $existingQuery->whereNull('tournament_id');
        }

        if ($existingQuery->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'A reservation already exists with this phone number for this tournament',
            ], 422);
        }

        try {
            $reservation = $this->reservationService->createReservation($data, $tournamentId);

            // Load tournament info if present
            $reservation->load('tournament');

            $response = [
                'status' => $reservation->status,
                'reservation_id' => $reservation->id,
            ];

            if ($reservation->tournament) {
                $response['tournament'] = [
                    'id' => $reservation->tournament->id,
                    'name' => $reservation->tournament->name,
                    'start_date' => $reservation->tournament->start_date->toIso8601String(),
                ];
            }

            if ($reservation->isWaiting()) {
                $response['waiting_number'] = $reservation->waiting_position;
                $response['message'] = 'All seats are full. You have been added to the waiting list.';
            } else {
                $response['table'] = $reservation->table_number;
                $response['seat'] = $reservation->seat_number;
                $response['qr'] = $reservation->qr_code;
                $response['message'] = 'Your seat has been reserved successfully!';
            }

            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to create reservation. Please try again.',
            ], 500);
        }
    }

    /**
     * Check in with QR code
     */
    public function checkIn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reservation_id' => 'required|uuid|exists:reservations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reservation ID',
            ], 422);
        }

        $result = $this->reservationService->checkIn($request->reservation_id);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get reservation by phone number
     */
    public function getByPhone(Request $request, string $phone): JsonResponse
    {
        // Optional tournament filter
        $tournamentId = $request->query('tournament_id');

        $query = Reservation::with('tournament')
            ->where('phone', $phone)
            ->whereIn('status', ['reserved', 'waiting', 'checked_in']);

        if ($tournamentId) {
            $query->where('tournament_id', $tournamentId);
        }

        $reservations = $query->get();

        if ($reservations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No reservation found for this phone number',
            ], 404);
        }

        // If multiple reservations, return all
        if ($reservations->count() > 1) {
            return response()->json([
                'reservations' => $reservations->map(function($reservation) {
                    return $this->formatReservation($reservation);
                })
            ]);
        }

        // Single reservation
        $reservation = $reservations->first();
        return response()->json($this->formatReservation($reservation));
    }

    /**
     * Get reservation by ID
     */
    public function show(string $id): JsonResponse
    {
        $reservation = Reservation::with('tournament')->find($id);

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found',
            ], 404);
        }

        return response()->json($this->formatReservation($reservation));
    }

    /**
     * Format reservation response
     */
    private function formatReservation(Reservation $reservation): array
    {
        $data = [
            'id' => $reservation->id,
            'first_name' => $reservation->first_name,
            'last_name' => $reservation->last_name,
            'phone' => $reservation->phone,
            'email' => $reservation->email,
            'status' => $reservation->status,
            'created_at' => $reservation->created_at->toIso8601String(),
        ];

        // Tournament info
        if ($reservation->tournament) {
            $data['tournament'] = [
                'id' => $reservation->tournament->id,
                'name' => $reservation->tournament->name,
                'slug' => $reservation->tournament->slug,
                'start_date' => $reservation->tournament->start_date->toIso8601String(),
                'venue_name' => $reservation->tournament->venue_name,
                'city' => $reservation->tournament->city,
                'game_type' => $reservation->tournament->game_type,
                'tournament_type' => $reservation->tournament->tournament_type,
                'buy_in' => $reservation->tournament->buy_in,
            ];
        }

        // Status-specific data
        if ($reservation->status === 'waiting') {
            $data['waiting_position'] = $reservation->waiting_position;
        } else {
            $data['table'] = $reservation->table_number;
            $data['seat'] = $reservation->seat_number;
            $data['qr'] = $reservation->qr_code;
            
            if ($reservation->isCheckedIn()) {
                $data['checkin_time'] = $reservation->checkin_time->toIso8601String();
            }
        }

        return $data;
    }

    /**
     * Cancel reservation
     */
    public function cancel(string $id): JsonResponse
    {
        $success = $this->reservationService->cancelReservation($id);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel this reservation',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reservation cancelled successfully',
        ]);
    }

    /**
     * Get tournament statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $tournamentId = $request->query('tournament_id');
        
        try {
            $stats = $this->reservationService->getStatistics($tournamentId);
            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get table layout
     */
    public function tableLayout(Request $request): JsonResponse
    {
        $tournamentId = $request->query('tournament_id');
        
        try {
            $layout = $this->reservationService->getTableLayout($tournamentId);
            
            $response = ['tables' => $layout];
            
            if ($tournamentId) {
                $tournament = Tournament::find($tournamentId);
                $response['tournament'] = [
                    'id' => $tournament->id,
                    'name' => $tournament->name,
                    'total_tables' => $tournament->total_tables,
                    'seats_per_table' => $tournament->seats_per_table,
                ];
            }
            
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get waiting list
     */
    public function waitingList(Request $request): JsonResponse
    {
        $tournamentId = $request->query('tournament_id');

        $query = Reservation::query();
        
        if ($tournamentId) {
            $query->where('tournament_id', $tournamentId);
        } else {
            $query->whereNull('tournament_id');
        }

        $waitingList = $query->where('status', 'waiting')
            ->orderBy('waiting_position')
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'name' => $reservation->full_name,
                    'phone' => $reservation->phone,
                    'position' => $reservation->waiting_position,
                    'created_at' => $reservation->created_at->toIso8601String(),
                ];
            });

        return response()->json(['waiting_list' => $waitingList]);
    }
}

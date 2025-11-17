<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Tournament;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegistrationController extends Controller
{
    public function __construct(
        private RegistrationService $registrationService
    ) {}

    /**
     * Register a player for a tournament (requires authentication)
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tournament_id' => 'required|uuid|exists:tournaments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Get authenticated player
        $player = $request->user();
        
        $tournamentId = $request->tournament_id;

        // Build data from authenticated player
        $data = [
            'player_id' => $player->id,
            'first_name' => $player->first_name,
            'last_name' => $player->last_name,
            'phone' => $player->phone,
            'email' => $player->email,
        ];

        try {
            $registration = $this->registrationService->register($data, $tournamentId);
            $registration->load('tournament', 'player');

            $response = [
                'success' => true,
                'status' => $registration->status,
                'registration_id' => $registration->id,
                'tournament' => [
                    'id' => $registration->tournament->id,
                    'name' => $registration->tournament->name,
                    'start_date' => $registration->tournament->start_date->toIso8601String(),
                    'venue_name' => $registration->tournament->venue_name,
                ],
            ];

            if ($registration->isWaiting()) {
                $response['waiting_position'] = $registration->waiting_position;
                $response['message'] = 'Tournament is full. You have been added to the waiting list at position ' . $registration->waiting_position;
            } else {
                $response['table'] = $registration->table_number;
                $response['seat'] = $registration->seat_number;
                $response['qr_code'] = $registration->qr_code;
                $response['message'] = 'Registration confirmed! You are registered for ' . $registration->tournament->name;
            }

            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Check in with QR code
     */
    public function checkIn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'registration_id' => 'required|uuid|exists:registrations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid registration ID',
            ], 422);
        }

        $result = $this->registrationService->checkIn($request->registration_id);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get registration by phone number
     */
    public function getByPhone(Request $request, string $phone): JsonResponse
    {
        $tournamentId = $request->query('tournament_id');

        $query = Registration::with('tournament')
            ->where('phone', $phone)
            ->whereIn('status', ['registered', 'waiting', 'checked_in']);

        if ($tournamentId) {
            $query->where('tournament_id', $tournamentId);
        }

        $registrations = $query->orderBy('created_at', 'desc')->get();

        if ($registrations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No registration found for this phone number',
            ], 404);
        }

        // Return all registrations for this phone
        return response()->json([
            'success' => true,
            'registrations' => $registrations->map(function($registration) {
                return $this->formatRegistration($registration);
            })
        ]);
    }

    /**
     * Get registration by ID
     */
    public function show(string $id): JsonResponse
    {
        $registration = Registration::with('tournament')->find($id);

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'Registration not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'registration' => $this->formatRegistration($registration)
        ]);
    }

    /**
     * Format registration response
     */
    private function formatRegistration(Registration $registration): array
    {
        $data = [
            'id' => $registration->id,
            'first_name' => $registration->first_name,
            'last_name' => $registration->last_name,
            'full_name' => $registration->full_name,
            'phone' => $registration->phone,
            'email' => $registration->email,
            'status' => $registration->status,
            'created_at' => $registration->created_at->toIso8601String(),
        ];

        // Tournament info
        if ($registration->tournament) {
            $data['tournament'] = [
                'id' => $registration->tournament->id,
                'name' => $registration->tournament->name,
                'slug' => $registration->tournament->slug,
                'start_date' => $registration->tournament->start_date->toIso8601String(),
                'venue_name' => $registration->tournament->venue_name,
                'city' => $registration->tournament->city,
                'game_type' => $registration->tournament->game_type,
                'tournament_type' => $registration->tournament->tournament_type,
                'buy_in' => $registration->tournament->buy_in,
            ];
        }

        // Status-specific data
        if ($registration->status === 'waiting') {
            $data['waiting_position'] = $registration->waiting_position;
        } else {
            $data['table'] = $registration->table_number;
            $data['seat'] = $registration->seat_number;
            $data['qr_code'] = $registration->qr_code;
            
            if ($registration->isCheckedIn()) {
                $data['checkin_time'] = $registration->checkin_time->toIso8601String();
            }
        }

        return $data;
    }

    /**
     * Cancel registration
     */
    public function cancel(string $id): JsonResponse
    {
        $success = $this->registrationService->cancel($id);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel this registration. It may be already checked in or not found.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Registration cancelled successfully',
        ]);
    }

    /**
     * Get tournament registration statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $tournamentId = $request->query('tournament_id');

        if (!$tournamentId) {
            return response()->json([
                'success' => false,
                'message' => 'tournament_id is required',
            ], 422);
        }

        try {
            $stats = $this->registrationService->getStatistics($tournamentId);
            
            $tournament = Tournament::find($tournamentId);
            $stats['tournament'] = [
                'id' => $tournament->id,
                'name' => $tournament->name,
                'start_date' => $tournament->start_date->toIso8601String(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get table layout for tournament
     */
    public function tableLayout(Request $request): JsonResponse
    {
        $tournamentId = $request->query('tournament_id');

        if (!$tournamentId) {
            return response()->json([
                'success' => false,
                'message' => 'tournament_id is required',
            ], 422);
        }

        try {
            $layout = $this->registrationService->getTableLayout($tournamentId);
            
            $tournament = Tournament::find($tournamentId);
            
            return response()->json([
                'tournament' => [
                    'id' => $tournament->id,
                    'name' => $tournament->name,
                    'total_tables' => $tournament->total_tables,
                    'seats_per_table' => $tournament->seats_per_table,
                ],
                'tables' => $layout,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update seat assignment (admin only)
     */
    public function updateSeat(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'table_number' => 'required|integer|min:1',
            'seat_number' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $registration = Registration::findOrFail($id);
            $tournament = $registration->tournament;

            // Validate table and seat numbers
            if ($request->table_number > $tournament->total_tables) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid table number',
                ], 400);
            }

            if ($request->seat_number > $tournament->seats_per_table) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid seat number',
                ], 400);
            }

            // Check if the target seat is already occupied
            $existingSeat = Registration::where('tournament_id', $tournament->id)
                ->where('table_number', $request->table_number)
                ->where('seat_number', $request->seat_number)
                ->whereIn('status', ['registered', 'checked_in'])
                ->where('id', '!=', $id)
                ->first();

            if ($existingSeat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seat is already occupied',
                ], 400);
            }

            // Update the seat assignment
            $registration->table_number = $request->table_number;
            $registration->seat_number = $request->seat_number;
            $registration->save();

            return response()->json([
                'success' => true,
                'message' => 'Seat assignment updated successfully',
                'registration' => [
                    'id' => $registration->id,
                    'table_number' => $registration->table_number,
                    'seat_number' => $registration->seat_number,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update registration status (admin only)
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:registered,checked_in,cancelled,waiting',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $registration = Registration::findOrFail($id);
            $oldStatus = $registration->status;
            $tournament = $registration->tournament;
            
            // If moving to waiting list, clear seat assignment and set waiting position
            if ($request->status === 'waiting') {
                // Get the next waiting position
                $maxWaitingPosition = Registration::where('tournament_id', $tournament->id)
                    ->where('status', 'waiting')
                    ->max('waiting_position') ?? 0;
                
                $registration->table_number = null;
                $registration->seat_number = null;
                $registration->waiting_position = $maxWaitingPosition + 1;
            } else {
                // If moving from waiting to another status, clear waiting position
                if ($oldStatus === 'waiting') {
                    $registration->waiting_position = null;
                }
            }
            
            $registration->status = $request->status;
            $registration->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'registration' => [
                    'id' => $registration->id,
                    'status' => $registration->status,
                    'old_status' => $oldStatus,
                    'waiting_position' => $registration->waiting_position,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Move player from waiting list to a seat
     */
    public function moveFromWaitingList(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'table_number' => 'required|integer|min:1',
            'seat_number' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $registration = Registration::findOrFail($id);
            $tournament = $registration->tournament;

            // Validate that the registration is in waiting status
            if ($registration->status !== 'waiting') {
                return response()->json([
                    'success' => false,
                    'message' => 'Player is not in waiting list',
                ], 400);
            }

            // Validate table and seat numbers
            if ($request->table_number > $tournament->total_tables) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid table number',
                ], 400);
            }

            if ($request->seat_number > $tournament->seats_per_table) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid seat number',
                ], 400);
            }

            // Check if the target seat is already occupied
            $existingSeat = Registration::where('tournament_id', $tournament->id)
                ->where('table_number', $request->table_number)
                ->where('seat_number', $request->seat_number)
                ->whereIn('status', ['registered', 'checked_in'])
                ->first();

            if ($existingSeat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seat is already occupied',
                ], 400);
            }

            // Move player to seat and change status to registered
            $registration->table_number = $request->table_number;
            $registration->seat_number = $request->seat_number;
            $registration->status = 'registered';
            $registration->waiting_position = null;
            $registration->save();

            return response()->json([
                'success' => true,
                'message' => 'Player moved from waiting list to seat successfully',
                'registration' => [
                    'id' => $registration->id,
                    'table_number' => $registration->table_number,
                    'seat_number' => $registration->seat_number,
                    'status' => $registration->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get waiting list for tournament
     */
    public function waitingList(Request $request): JsonResponse
    {
        $tournamentId = $request->query('tournament_id');

        if (!$tournamentId) {
            return response()->json([
                'success' => false,
                'message' => 'tournament_id is required',
            ], 422);
        }

        $waitingList = Registration::where('tournament_id', $tournamentId)
            ->where('status', 'waiting')
            ->orderBy('waiting_position')
            ->get()
            ->map(function ($registration) {
                return [
                    'id' => $registration->id,
                    'name' => $registration->full_name,
                    'phone' => $registration->phone,
                    'position' => $registration->waiting_position,
                    'created_at' => $registration->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'tournament_id' => $tournamentId,
            'waiting_list' => $waitingList,
            'count' => $waitingList->count(),
        ]);
    }

    /**
     * Get all registrations for a tournament
     */
    public function index(Request $request): JsonResponse
    {
        $tournamentId = $request->query('tournament_id');
        $status = $request->query('status');

        $query = Registration::with('tournament');

        if ($tournamentId) {
            $query->where('tournament_id', $tournamentId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $registrations = $query->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $registrations->items(),
            'meta' => [
                'current_page' => $registrations->currentPage(),
                'last_page' => $registrations->lastPage(),
                'per_page' => $registrations->perPage(),
                'total' => $registrations->total(),
            ],
        ]);
    }
}

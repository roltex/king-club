<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PlayerController extends Controller
{
    /**
     * Register a new player
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:players,phone',
            'email' => 'required|email|max:255|unique:players,email',
            'password' => 'required|string|min:6|confirmed',
            'date_of_birth' => 'nullable|date|before:today',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $player = Player::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'date_of_birth' => $request->date_of_birth,
                'city' => $request->city,
                'country' => $request->country ?? 'Georgia',
                'is_active' => true,
            ]);

            // Create API token
            $token = $player->createToken('player-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'player' => [
                    'id' => $player->id,
                    'first_name' => $player->first_name,
                    'last_name' => $player->last_name,
                    'full_name' => $player->full_name,
                    'phone' => $player->phone,
                    'email' => $player->email,
                    'city' => $player->city,
                    'country' => $player->country,
                ],
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login player
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $player = Player::where('email', $request->email)->first();

        if (!$player || !Hash::check($request->password, $player->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (!$player->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated',
            ], 403);
        }

        // Revoke old tokens
        $player->tokens()->delete();

        // Create new token
        $token = $player->createToken('player-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'player' => [
                'id' => $player->id,
                'first_name' => $player->first_name,
                'last_name' => $player->last_name,
                'full_name' => $player->full_name,
                'phone' => $player->phone,
                'email' => $player->email,
                'city' => $player->city,
                'country' => $player->country,
            ],
            'token' => $token,
        ], 200);
    }

    /**
     * Logout player
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ], 200);
    }

    /**
     * Get player profile
     */
    public function profile(Request $request): JsonResponse
    {
        $player = $request->user();

        return response()->json([
            'success' => true,
            'player' => [
                'id' => $player->id,
                'first_name' => $player->first_name,
                'last_name' => $player->last_name,
                'full_name' => $player->full_name,
                'phone' => $player->phone,
                'email' => $player->email,
                'date_of_birth' => $player->date_of_birth?->format('Y-m-d'),
                'city' => $player->city,
                'country' => $player->country,
                'profile_image' => $player->profile_image,
                'email_verified' => $player->email_verified,
                'statistics' => $player->getStatistics(),
                'created_at' => $player->created_at->toIso8601String(),
            ],
        ], 200);
    }

    /**
     * Update player profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $player = $request->user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20|unique:players,phone,' . $player->id,
            'email' => 'sometimes|required|email|max:255|unique:players,email,' . $player->id,
            'date_of_birth' => 'nullable|date|before:today',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $player->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'player' => [
                    'id' => $player->id,
                    'first_name' => $player->first_name,
                    'last_name' => $player->last_name,
                    'full_name' => $player->full_name,
                    'phone' => $player->phone,
                    'email' => $player->email,
                    'city' => $player->city,
                    'country' => $player->country,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $player = $request->user();

        if (!Hash::check($request->current_password, $player->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 401);
        }

        $player->update([
            'password' => Hash::make($request->new_password),
        ]);

        // Revoke all tokens and create a new one
        $player->tokens()->delete();
        $token = $player->createToken('player-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
            'token' => $token,
        ], 200);
    }

    /**
     * Get player's tournament history
     */
    public function tournamentHistory(Request $request): JsonResponse
    {
        $player = $request->user();

        $history = $player->tournamentHistory()
            ->with('tournament')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'history' => $history->map(function ($registration) {
                return [
                    'id' => $registration->id,
                    'tournament' => [
                        'id' => $registration->tournament->id,
                        'name' => $registration->tournament->name,
                        'start_date' => $registration->tournament->start_date->toIso8601String(),
                        'tournament_type' => $registration->tournament->tournament_type,
                        'game_type' => $registration->tournament->game_type,
                        'buy_in' => $registration->tournament->buy_in,
                    ],
                    'status' => $registration->status,
                    'table_number' => $registration->table_number,
                    'seat_number' => $registration->seat_number,
                    'registered_at' => $registration->created_at->toIso8601String(),
                    'checked_in_at' => $registration->checkin_time?->toIso8601String(),
                ];
            }),
            'pagination' => [
                'current_page' => $history->currentPage(),
                'last_page' => $history->lastPage(),
                'per_page' => $history->perPage(),
                'total' => $history->total(),
            ],
        ], 200);
    }
}


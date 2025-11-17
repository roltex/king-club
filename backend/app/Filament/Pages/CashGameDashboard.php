<?php

namespace App\Filament\Pages;

use App\Models\CashGame;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class CashGameDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    
    protected static string $view = 'filament.pages.cash-game-dashboard';
    
    protected static ?string $slug = 'cash-game-dashboard';
    
    protected static bool $shouldRegisterNavigation = false;
    
    public ?CashGame $cashGame = null;
    
    public $cashGameId;
    
    public $stats = [];
    public $table = [];
    public $seatedPlayers = [];
    public $waitingList = [];

    public function mount(?string $cash_game = null): void
    {
        $cashGameId = request()->query('cash_game', $cash_game);
        if ($cashGameId) {
            $this->cashGameId = $cashGameId;
            $this->loadCashGameData();
        }
    }

    public function loadCashGameData(): void
    {
        $this->cashGame = CashGame::with([
            'seats.player:id,first_name,last_name,email'
        ])->findOrFail($this->cashGameId);
        
        $this->stats = $this->getCashGameStats();
        $this->table = $this->getTableLayout();
        $this->seatedPlayers = $this->getSeatedPlayers();
        $this->waitingList = $this->getWaitingList();
    }

    private function getCashGameStats(): array
    {
        $seats = $this->cashGame->seats()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $seated = $seats['seated'] ?? 0;
        $playing = $seats['playing'] ?? 0;
        $away = $seats['away'] ?? 0;
        $sittingOut = $seats['sitting_out'] ?? 0;
        $waiting = $seats['waiting'] ?? 0;
        
        $occupied = $seated + $playing + $away;
        $available = max(0, $this->cashGame->seats_per_table - $occupied);
        $fillPercentage = $this->cashGame->seats_per_table > 0 
            ? round(($occupied / $this->cashGame->seats_per_table) * 100, 1) 
            : 0;

        // Calculate total pot (sum of all current stacks)
        $totalPot = $this->cashGame->seats()
            ->whereIn('status', ['seated', 'playing', 'away'])
            ->sum('current_stack');

        return [
            'seated' => $seated,
            'playing' => $playing,
            'away' => $away,
            'sitting_out' => $sittingOut,
            'waiting' => $waiting,
            'occupied_seats' => $occupied,
            'available_seats' => $available,
            'fill_percentage' => $fillPercentage,
            'total_pot' => $totalPot,
            'total_seats' => $this->cashGame->seats_per_table,
        ];
    }

    private function getTableLayout(): array
    {
        $seats = $this->cashGame->seats()
            ->with('player:id,first_name,last_name,email')
            ->whereIn('status', ['seated', 'playing', 'away', 'sitting_out'])
            ->get()
            ->keyBy('seat_number');

        $tableSeats = [];
        for ($seatNum = 1; $seatNum <= $this->cashGame->seats_per_table; $seatNum++) {
            $seat = $seats->get($seatNum);

            $tableSeats[] = [
                'seat_number' => $seatNum,
                'occupied' => $seat !== null,
                'status' => $seat?->status,
                'player_name' => $seat ? ($seat->player->first_name . ' ' . $seat->player->last_name) : null,
                'player_email' => $seat?->player?->email,
                'current_stack' => $seat?->current_stack ?? 0,
                'buy_in_amount' => $seat?->buy_in_amount ?? 0,
                'seat_id' => $seat?->id,
            ];
        }

        $occupiedCount = collect($tableSeats)->where('occupied', true)->count();
        
        return [
            'table_number' => $this->cashGame->table_number,
            'seats' => $tableSeats,
            'occupied_count' => $occupiedCount,
            'total_seats' => $this->cashGame->seats_per_table,
        ];
    }

    private function getSeatedPlayers(): array
    {
        return $this->cashGame->seats()
            ->with('player:id,first_name,last_name,email')
            ->whereIn('status', ['seated', 'playing', 'away', 'sitting_out'])
            ->orderBy('seat_number')
            ->get()
            ->map(function ($seat) {
                return [
                    'id' => $seat->id,
                    'name' => $seat->player->first_name . ' ' . $seat->player->last_name,
                    'email' => $seat->player->email,
                    'seat' => $seat->seat_number,
                    'status' => $seat->status,
                    'current_stack' => $seat->current_stack,
                    'buy_in_amount' => $seat->buy_in_amount,
                ];
            })
            ->toArray();
    }

    private function getWaitingList(): array
    {
        return $this->cashGame->seats()
            ->with('player:id,first_name,last_name,email')
            ->where('status', 'waiting')
            ->orderBy('waiting_position')
            ->orderBy('created_at')
            ->get()
            ->map(function ($seat) {
                return [
                    'id' => $seat->id,
                    'name' => $seat->player->first_name . ' ' . $seat->player->last_name,
                    'email' => $seat->player->email,
                    'position' => $seat->waiting_position,
                    'buy_in_amount' => $seat->buy_in_amount ?? $this->cashGame->default_buy_in ?? $this->cashGame->min_buy_in,
                    'joined_at' => $seat->created_at->format('M d, Y H:i'),
                ];
            })
            ->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action('loadCashGameData'),
            \Filament\Actions\Action::make('back')
                ->label('Back to Dashboard')
                ->icon('heroicon-o-arrow-left')
                ->url('/admin'),
        ];
    }
}


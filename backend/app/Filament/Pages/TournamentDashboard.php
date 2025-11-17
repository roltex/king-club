<?php

namespace App\Filament\Pages;

use App\Models\Tournament;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class TournamentDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    
    protected static string $view = 'filament.pages.tournament-dashboard';
    
    protected static ?string $slug = 'tournament-dashboard';
    
    protected static bool $shouldRegisterNavigation = false;
    
    public ?Tournament $tournament = null;
    
    public $tournamentId;
    
    public $stats = [];
    public $tables = [];
    public $registeredPlayers = [];
    public $waitingList = [];

    public function mount(?int $tournament = null): void
    {
        $tournamentId = request()->query('tournament', $tournament);
        if ($tournamentId) {
            $this->tournamentId = $tournamentId;
            $this->loadTournamentData();
        }
    }

    public function loadTournamentData(): void
    {
        $this->tournament = Tournament::with([
            'registrations.player:id,first_name,last_name,email'
        ])->findOrFail($this->tournamentId);
        
        $this->stats = $this->getTournamentStats();
        $this->tables = $this->getTableLayout();
        $this->registeredPlayers = $this->getRegisteredPlayers();
        $this->waitingList = $this->getWaitingList();
    }

    private function getTournamentStats(): array
    {
        $registrations = $this->tournament->registrations()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $registered = $registrations['registered'] ?? 0;
        $checkedIn = $registrations['checked_in'] ?? 0;
        $waiting = $registrations['waiting'] ?? 0;
        $cancelled = $registrations['cancelled'] ?? 0;
        
        $occupied = $registered + $checkedIn;
        $available = max(0, $this->tournament->total_seats - $occupied);
        $fillPercentage = $this->tournament->total_seats > 0 
            ? round(($occupied / $this->tournament->total_seats) * 100, 1) 
            : 0;

        return [
            'registered' => $registered,
            'checked_in' => $checkedIn,
            'waiting_list' => $waiting,
            'cancelled' => $cancelled,
            'occupied_seats' => $occupied,
            'available_seats' => $available,
            'fill_percentage' => $fillPercentage,
        ];
    }

    private function getTableLayout(): array
    {
        $registrations = $this->tournament->registrations()
            ->with('player:id,first_name,last_name,email')
            ->whereIn('status', ['registered', 'checked_in'])
            ->get()
            ->keyBy(function ($reg) {
                return "{$reg->table_number}-{$reg->seat_number}";
            });

        $tables = [];
        for ($tableNum = 1; $tableNum <= $this->tournament->total_tables; $tableNum++) {
            $seats = [];
            for ($seatNum = 1; $seatNum <= $this->tournament->seats_per_table; $seatNum++) {
                $key = "{$tableNum}-{$seatNum}";
                $registration = $registrations->get($key);

                $seats[] = [
                    'seat_number' => $seatNum,
                    'occupied' => $registration !== null,
                    'status' => $registration?->status,
                    'player_name' => $registration ? $registration->full_name : null,
                    'player_email' => $registration?->player?->email,
                    'checked_in' => $registration?->status === 'checked_in',
                    'registration_id' => $registration?->id,
                ];
            }

            $occupiedCount = collect($seats)->where('occupied', true)->count();
            
            $tables[] = [
                'table_number' => $tableNum,
                'seats' => $seats,
                'occupied_count' => $occupiedCount,
                'total_seats' => $this->tournament->seats_per_table,
            ];
        }

        return $tables;
    }

    private function getRegisteredPlayers(): array
    {
        return $this->tournament->registrations()
            ->with('player:id,first_name,last_name,email')
            ->whereIn('status', ['registered', 'checked_in'])
            ->orderBy('table_number')
            ->orderBy('seat_number')
            ->get()
            ->map(function ($reg) {
                return [
                    'id' => $reg->id,
                    'name' => $reg->full_name,
                    'email' => $reg->email ?? $reg->player?->email,
                    'phone' => $reg->phone,
                    'table' => $reg->table_number,
                    'seat' => $reg->seat_number,
                    'status' => $reg->status,
                    'checked_in' => $reg->status === 'checked_in',
                ];
            })
            ->toArray();
    }

    private function getWaitingList(): array
    {
        return $this->tournament->registrations()
            ->with('player:id,first_name,last_name,email')
            ->where('status', 'waiting')
            ->orderBy('waiting_position')
            ->orderBy('created_at')
            ->get()
            ->map(function ($reg) {
                return [
                    'id' => $reg->id,
                    'name' => $reg->full_name,
                    'email' => $reg->email ?? $reg->player?->email,
                    'phone' => $reg->phone,
                    'position' => $reg->waiting_position,
                    'joined_at' => $reg->created_at->format('M d, Y H:i'),
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
                ->action('loadTournamentData'),
            \Filament\Actions\Action::make('back')
                ->label('Back to Dashboard')
                ->icon('heroicon-o-arrow-left')
                ->url('/admin'),
        ];
    }
}


<?php

namespace App\Filament\Resources\TournamentResource\Pages;

use App\Filament\Resources\TournamentResource;
use App\Models\Tournament;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ViewEntry;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ViewTournament extends ViewRecord
{
    protected static string $resource = TournamentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $tournament = $this->record;
        
        // Get statistics
        $stats = $this->getTournamentStats($tournament);
        $tables = $this->getTableLayout($tournament);
        
        return $infolist
            ->schema([
                Section::make('Tournament Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                ImageEntry::make('image_url')
                                    ->label('Tournament Image')
                                    ->disk('public')
                                    ->defaultImageUrl(url('/images/tournament-default.png'))
                                    ->height('200px')
                                    ->width('100%')
                                    ->columnSpanFull(),
                                
                                TextEntry::make('name')
                                    ->label('Tournament Name')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->columnSpanFull(),
                                
                                TextEntry::make('start_date')
                                    ->label('Start Date')
                                    ->dateTime('M d, Y H:i')
                                    ->badge()
                                    ->color(fn ($record) => $record->start_date > now() ? 'success' : 'warning'),
                                
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'draft' => 'gray',
                                        'published' => 'info',
                                        'registration_open' => 'success',
                                        'registration_closed' => 'warning',
                                        'in_progress' => 'primary',
                                        'completed' => 'gray',
                                        'cancelled' => 'danger',
                                    })
                                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
                                
                                TextEntry::make('tournament_type')
                                    ->label('Type')
                                    ->badge()
                                    ->color('info')
                                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
                                
                                TextEntry::make('game_type')
                                    ->label('Game Type')
                                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
                                
                                TextEntry::make('buy_in')
                                    ->label('Buy-In')
                                    ->money('GEL'),
                                
                                TextEntry::make('prize_pool')
                                    ->label('Prize Pool')
                                    ->money('GEL')
                                    ->color('success')
                                    ->weight('bold'),
                            ]),
                    ])
                    ->collapsible(),
                
                Section::make('Statistics')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('total_seats')
                                    ->label('Total Seats')
                                    ->default($tournament->total_seats)
                                    ->badge()
                                    ->color('gray'),
                                
                                TextEntry::make('occupied_seats')
                                    ->label('Occupied Seats')
                                    ->default($stats['occupied_seats'])
                                    ->badge()
                                    ->color($stats['occupied_seats'] >= $tournament->total_seats ? 'danger' : 'success'),
                                
                                TextEntry::make('available_seats')
                                    ->label('Available Seats')
                                    ->default($stats['available_seats'])
                                    ->badge()
                                    ->color($stats['available_seats'] > 0 ? 'success' : 'danger'),
                                
                                TextEntry::make('registered_count')
                                    ->label('Registered')
                                    ->default($stats['registered'])
                                    ->badge()
                                    ->color('info'),
                                
                                TextEntry::make('checked_in_count')
                                    ->label('Checked In')
                                    ->default($stats['checked_in'])
                                    ->badge()
                                    ->color('success'),
                                
                                TextEntry::make('waiting_list_count')
                                    ->label('Waiting List')
                                    ->default($stats['waiting_list'])
                                    ->badge()
                                    ->color('warning'),
                                
                                TextEntry::make('cancelled_count')
                                    ->label('Cancelled')
                                    ->default($stats['cancelled'])
                                    ->badge()
                                    ->color('danger'),
                                
                                TextEntry::make('fill_percentage')
                                    ->label('Fill Rate')
                                    ->default($stats['fill_percentage'] . '%')
                                    ->badge()
                                    ->color(fn () => $stats['fill_percentage'] >= 100 ? 'danger' : ($stats['fill_percentage'] >= 80 ? 'warning' : 'success')),
                            ]),
                    ])
                    ->collapsible(),
                
                Section::make('Table Layout')
                    ->schema([
                        ViewEntry::make('tables')
                            ->view('filament.infolists.components.tournament-tables')
                            ->viewData(fn () => [
                                'tables' => $tables,
                                'tournament' => $tournament,
                            ]),
                    ])
                    ->collapsible(false),
                
                Section::make('Registered Players')
                    ->schema([
                        ViewEntry::make('registered_players')
                            ->view('filament.infolists.components.registered-players')
                            ->viewData(fn () => [
                                'tournament' => $tournament,
                            ]),
                    ])
                    ->collapsible(),
                
                Section::make('Waiting List')
                    ->schema([
                        ViewEntry::make('waiting_list')
                            ->view('filament.infolists.components.waiting-list')
                            ->viewData(fn () => [
                                'tournament' => $tournament,
                            ]),
                    ])
                    ->collapsible()
                    ->visible(fn () => $stats['waiting_list'] > 0),
            ]);
    }

    private function getTournamentStats(Tournament $tournament): array
    {
        $registrations = $tournament->registrations()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $registered = $registrations['registered'] ?? 0;
        $checkedIn = $registrations['checked_in'] ?? 0;
        $waiting = $registrations['waiting'] ?? 0;
        $cancelled = $registrations['cancelled'] ?? 0;
        
        $occupied = $registered + $checkedIn;
        $available = max(0, $tournament->total_seats - $occupied);
        $fillPercentage = $tournament->total_seats > 0 
            ? round(($occupied / $tournament->total_seats) * 100, 1) 
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

    private function getTableLayout(Tournament $tournament): array
    {
        $registrations = $tournament->registrations()
            ->with('player:id,first_name,last_name,email')
            ->whereIn('status', ['registered', 'checked_in'])
            ->get()
            ->keyBy(function ($reg) {
                return "{$reg->table_number}-{$reg->seat_number}";
            });

        $tables = [];
        for ($tableNum = 1; $tableNum <= $tournament->total_tables; $tableNum++) {
            $seats = [];
            for ($seatNum = 1; $seatNum <= $tournament->seats_per_table; $seatNum++) {
                $key = "{$tableNum}-{$seatNum}";
                $registration = $registrations->get($key);

                $seats[] = [
                    'seat_number' => $seatNum,
                    'occupied' => $registration !== null,
                    'status' => $registration?->status,
                    'player_name' => $registration ? $registration->full_name : null,
                    'player_email' => $registration?->player?->email,
                    'checked_in' => $registration?->status === 'checked_in',
                ];
            }

            $occupiedCount = collect($seats)->where('occupied', true)->count();
            
            $tables[] = [
                'table_number' => $tableNum,
                'seats' => $seats,
                'occupied_count' => $occupiedCount,
                'total_seats' => $tournament->seats_per_table,
            ];
        }

        return $tables;
    }
}


<?php

namespace App\Filament\Resources\CashGameResource\Pages;

use App\Filament\Resources\CashGameResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewCashGame extends ViewRecord
{
    protected static string $resource = CashGameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Cash Game Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('table_number')
                            ->badge()
                            ->color('primary'),
                        Infolists\Components\TextEntry::make('stakes_display')
                            ->label('Stakes')
                            ->badge()
                            ->color('success'),
                        Infolists\Components\TextEntry::make('game_type')
                            ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn ($state) => match($state) {
                                'active' => 'success',
                                'full' => 'warning',
                                'closed' => 'danger',
                                default => 'gray',
                            }),
                    ])->columns(3),
                
                Infolists\Components\Section::make('Buy-In Limits')
                    ->schema([
                        Infolists\Components\TextEntry::make('min_buy_in')
                            ->money('GEL')
                            ->label('Min Buy-In'),
                        Infolists\Components\TextEntry::make('max_buy_in')
                            ->money('GEL')
                            ->label('Max Buy-In'),
                        Infolists\Components\TextEntry::make('default_buy_in')
                            ->money('GEL')
                            ->label('Default Buy-In'),
                    ])->columns(3),
                
                Infolists\Components\Section::make('Statistics')
                    ->schema([
                        Infolists\Components\TextEntry::make('current_players')
                            ->label('Current Players')
                            ->formatStateUsing(fn ($state, $record) => $state . '/' . ($record->seats_per_table ?? 9)),
                        Infolists\Components\TextEntry::make('available_seats')
                            ->label('Available Seats'),
                        Infolists\Components\TextEntry::make('fill_percentage')
                            ->label('Fill Rate')
                            ->formatStateUsing(fn ($state) => number_format($state, 1) . '%'),
                        Infolists\Components\TextEntry::make('total_pot')
                            ->money('GEL')
                            ->label('Total Pot'),
                        Infolists\Components\TextEntry::make('total_rake')
                            ->money('GEL')
                            ->label('Total Rake'),
                        Infolists\Components\TextEntry::make('hands_played')
                            ->label('Hands Played'),
                    ])->columns(3),
                
                Infolists\Components\Section::make('Location')
                    ->schema([
                        Infolists\Components\TextEntry::make('venue_name'),
                        Infolists\Components\TextEntry::make('address'),
                        Infolists\Components\TextEntry::make('city'),
                        Infolists\Components\TextEntry::make('country'),
                    ])->columns(2),
            ]);
    }
}


<?php

namespace App\Filament\Resources\PlayerResource\Pages;

use App\Filament\Resources\PlayerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewPlayer extends ViewRecord
{
    protected static string $resource = PlayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $stats = $this->record->getStatistics();
        
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Player Information')
                    ->schema([
                        Infolists\Components\ImageEntry::make('profile_image')
                            ->disk('public')
                            ->circular()
                            ->defaultImageUrl(url('/images/default-avatar.png'))
                            ->columnSpan(1),
                        
                        Infolists\Components\TextEntry::make('full_name')
                            ->label('Full Name')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold')
                            ->columnSpan(2),

                        Infolists\Components\TextEntry::make('email')
                            ->icon('heroicon-o-envelope')
                            ->copyable()
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('phone')
                            ->icon('heroicon-o-phone')
                            ->copyable()
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('date_of_birth')
                            ->date('M d, Y')
                            ->icon('heroicon-o-cake')
                            ->placeholder('Not provided')
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('city')
                            ->icon('heroicon-o-map-pin')
                            ->placeholder('Not provided')
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('country')
                            ->icon('heroicon-o-globe-alt')
                            ->columnSpan(1),

                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Account Status')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger')
                            ->columnSpan(1),

                        Infolists\Components\IconEntry::make('email_verified')
                            ->label('Email Verified')
                            ->boolean()
                            ->columnSpan(1),
                    ])->columns(3),

                Infolists\Components\Section::make('Statistics')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_registrations')
                            ->label('Total Registrations')
                            ->state($stats['total_registrations'])
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('tournaments_played')
                            ->label('Tournaments Played')
                            ->state($stats['tournaments_played'])
                            ->badge()
                            ->color('success'),

                        Infolists\Components\TextEntry::make('waiting_list')
                            ->label('On Waiting List')
                            ->state($stats['waiting_list'])
                            ->badge()
                            ->color('warning'),

                        Infolists\Components\TextEntry::make('cancelled')
                            ->label('Cancelled')
                            ->state($stats['cancelled'])
                            ->badge()
                            ->color('danger'),
                    ])->columns(4),

                Infolists\Components\Section::make('Account Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Account Created')
                            ->dateTime('M d, Y H:i')
                            ->icon('heroicon-o-calendar'),

                        Infolists\Components\TextEntry::make('email_verified_at')
                            ->label('Email Verified At')
                            ->dateTime('M d, Y H:i')
                            ->placeholder('Not verified')
                            ->icon('heroicon-o-shield-check'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('M d, Y H:i')
                            ->icon('heroicon-o-clock'),
                    ])->columns(3)
                    ->collapsed(),

                Infolists\Components\Section::make('Notes')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->visible(fn ($record): bool => filled($record->notes)),
            ]);
    }
}


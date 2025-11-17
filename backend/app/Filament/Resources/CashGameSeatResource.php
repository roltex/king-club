<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashGameSeatResource\Pages;
use App\Models\CashGameSeat;
use App\Models\CashGame;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CashGameSeatResource extends Resource
{
    protected static ?string $model = CashGameSeat::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Cash Game Seats';

    protected static ?string $navigationGroup = 'Cash Game Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Player Selection')
                    ->schema([
                        Forms\Components\Select::make('cash_game_id')
                            ->label('Cash Game')
                            ->relationship('cashGame', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

                        Forms\Components\Select::make('player_id')
                            ->label('Select Player')
                            ->relationship('player', 'email', fn ($query) => $query->where('is_active', true)->orderBy('first_name'))
                            ->searchable(['first_name', 'last_name', 'email', 'phone'])
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->full_name} ({$record->email})")
                            ->preload()
                            ->required()
                            ->live()
                            ->columnSpan(1)
                            ->helperText('Search by name, email, or phone number'),
                    ])->columns(2)
                    ->description('Select a player account to join the cash game.'),

                Forms\Components\Section::make('Seat Assignment')
                    ->schema([
                        Forms\Components\Placeholder::make('seat_info')
                            ->label('')
                            ->content('💡 Leave empty for automatic seat assignment, or manually set seat number.')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('seat_number')
                            ->label('Seat Number')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Auto-assigned if empty')
                            ->helperText('Optional: Set manually or leave empty for auto-assignment')
                            ->columnSpan(1),
                    ])->columns(2)
                    ->description('Manually set seat or leave empty for automatic assignment'),

                Forms\Components\Section::make('Buy-In & Stack')
                    ->schema([
                        Forms\Components\TextInput::make('buy_in_amount')
                            ->label('Buy-In Amount')
                            ->numeric()
                            ->required()
                            ->prefix('₾')
                            ->helperText('Initial buy-in amount')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('current_stack')
                            ->label('Current Stack')
                            ->numeric()
                            ->default(0)
                            ->prefix('₾')
                            ->helperText('Current chip stack')
                            ->columnSpan(1),
                    ])->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'seated' => 'Seated',
                                'playing' => 'Playing',
                                'away' => 'Away',
                                'sitting_out' => 'Sitting Out',
                                'waiting' => 'Waiting List',
                                'left' => 'Left',
                                'removed' => 'Removed',
                            ])
                            ->default('seated')
                            ->live()
                            ->helperText('Select the player status')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('waiting_position')
                            ->label('Waiting Position')
                            ->numeric()
                            ->minValue(1)
                            ->visible(fn (Forms\Get $get): bool => $get('status') === 'waiting')
                            ->helperText('Position in the waiting list')
                            ->columnSpan(2),

                        Forms\Components\DateTimePicker::make('sat_down_at')
                            ->label('Sat Down At')
                            ->default(now())
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('left_at')
                            ->label('Left At')
                            ->visible(fn (Forms\Get $get): bool => in_array($get('status'), ['left', 'removed']))
                            ->columnSpan(1),
                    ])->columns(2),

                Forms\Components\Section::make('Rebuy Information')
                    ->schema([
                        Forms\Components\TextInput::make('rebuy_count')
                            ->label('Rebuy Count')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('total_rebuy_amount')
                            ->label('Total Rebuy Amount')
                            ->numeric()
                            ->default(0)
                            ->prefix('₾')
                            ->disabled()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('total_profit_loss')
                            ->label('Total Profit/Loss')
                            ->numeric()
                            ->default(0)
                            ->prefix('₾')
                            ->disabled()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('minutes_played')
                            ->label('Minutes Played')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->columnSpan(1),
                    ])->columns(2)
                    ->collapsed()
                    ->description('Rebuy and profit/loss tracking'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('player.first_name')
                    ->label('Player')
                    ->formatStateUsing(function (CashGameSeat $record): string {
                        if ($record->player) {
                            return $record->player->full_name;
                        }
                        return 'No player';
                    })
                    ->searchable(['player.first_name', 'player.last_name', 'player.email'])
                    ->sortable(false)
                    ->weight('bold')
                    ->description(fn (?CashGameSeat $record): ?string => 
                        $record && $record->player ? "Account: {$record->player->email}" : 'No account linked'
                    ),

                Tables\Columns\TextColumn::make('cashGame.name')
                    ->label('Cash Game')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('seat_number')
                    ->label('Seat')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => $state ? "Seat {$state}" : 'Waiting')
                    ->icon(fn (CashGameSeat $record): ?string => 
                        $record->seat_number ? 'heroicon-o-squares-2x2' : 'heroicon-o-clock'
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'seated' => 'info',
                        'playing' => 'success',
                        'away' => 'warning',
                        'sitting_out' => 'gray',
                        'waiting' => 'warning',
                        'left' => 'danger',
                        'removed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),

                Tables\Columns\TextColumn::make('buy_in_amount')
                    ->label('Buy-In')
                    ->money('GEL', divideBy: 1)
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_stack')
                    ->label('Stack')
                    ->money('GEL', divideBy: 1)
                    ->sortable()
                    ->color(fn ($state): string => $state > 0 ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('total_profit_loss')
                    ->label('P/L')
                    ->money('GEL', divideBy: 1)
                    ->sortable()
                    ->color(fn ($state): string => $state >= 0 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('waiting_position')
                    ->label('Wait Pos')
                    ->sortable()
                    ->visible(fn (?CashGameSeat $record): bool => $record && $record->status === 'waiting')
                    ->formatStateUsing(fn ($state): string => $state ? "#{$state}" : '-'),

                Tables\Columns\IconColumn::make('sat_down_at')
                    ->label('Joined')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn (?CashGameSeat $record): ?string => 
                        $record && $record->sat_down_at ? $record->sat_down_at->format('M d, Y H:i') : null
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cash_game')
                    ->relationship('cashGame', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('player_id')
                    ->label('Player Account')
                    ->relationship('player', 'email')
                    ->searchable(['first_name', 'last_name', 'email', 'phone'])
                    ->preload()
                    ->placeholder('All players'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'seated' => 'Seated',
                        'playing' => 'Playing',
                        'away' => 'Away',
                        'sitting_out' => 'Sitting Out',
                        'waiting' => 'Waiting List',
                        'left' => 'Left',
                        'removed' => 'Removed',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('has_seat')
                    ->label('Has Seat Assignment')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('seat_number')),

                Tables\Filters\Filter::make('waiting_list')
                    ->label('Waiting List')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'waiting')),

                Tables\Filters\Filter::make('active_players')
                    ->label('Active Players')
                    ->query(fn (Builder $query): Builder => $query->whereIn('status', ['seated', 'playing', 'away'])),
            ])
            ->actions([
                Tables\Actions\Action::make('set_playing')
                    ->label('Set Playing')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (?CashGameSeat $record): bool => $record && in_array($record->status, ['seated', 'away', 'sitting_out']))
                    ->action(function (CashGameSeat $record) {
                        $record->update(['status' => 'playing']);
                    })
                    ->successNotification(
                        fn () => \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Player set to playing')
                            ->body('The player status has been updated.')
                    ),

                Tables\Actions\Action::make('set_away')
                    ->label('Set Away')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (?CashGameSeat $record): bool => $record && in_array($record->status, ['seated', 'playing']))
                    ->action(function (CashGameSeat $record) {
                        $record->update(['status' => 'away']);
                    })
                    ->successNotification(
                        fn () => \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Player set to away')
                            ->body('The player status has been updated.')
                    ),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\DeleteAction::make()
                    ->label('Remove')
                    ->modalHeading('Remove Player')
                    ->successNotificationTitle('Player removed'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('set_playing_bulk')
                        ->label('Set Playing')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if (in_array($record->status, ['seated', 'away', 'sitting_out'])) {
                                    $record->update(['status' => 'playing']);
                                }
                            }
                        })
                        ->successNotification(
                            fn () => \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Players set to playing')
                                ->body('Selected players have been updated.')
                        ),

                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Remove Selected')
                        ->modalHeading('Remove Players')
                        ->successNotificationTitle('Players removed'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashGameSeats::route('/'),
            'create' => Pages\CreateCashGameSeat::route('/create'),
            'view' => Pages\ViewCashGameSeat::route('/{record}'),
            'edit' => Pages\EditCashGameSeat::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('status', ['seated', 'playing'])->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['cashGame', 'player'])
            ->latest();
    }
}


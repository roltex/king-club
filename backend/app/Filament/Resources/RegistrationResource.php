<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistrationResource\Pages;
use App\Models\Registration;
use App\Models\Tournament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;

class RegistrationResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Registrations';

    protected static ?string $navigationGroup = 'Tournament Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Player Selection')
                    ->schema([
                        Forms\Components\Select::make('tournament_id')
                            ->label('Tournament')
                            ->relationship('tournament', 'name')
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
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $player = \App\Models\Player::find($state);
                                    if ($player) {
                                        $set('first_name', $player->first_name);
                                        $set('last_name', $player->last_name);
                                        $set('phone', $player->phone);
                                        $set('email', $player->email);
                                    }
                                }
                            })
                            ->columnSpan(1)
                            ->helperText('Search by name, email, or phone number'),
                    ])->columns(2)
                    ->description('Select a player account. Player information will be automatically filled.'),

                Forms\Components\Section::make('Player Information (Auto-filled)')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('First Name')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('last_name')
                            ->label('Last Name')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('phone')
                            ->label('Phone')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),
                    ])->columns(2)
                    ->collapsed()
                    ->description('This information is automatically populated from the selected player account'),

                Forms\Components\Section::make('Registration Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'registered' => 'Registered',
                                'waiting' => 'Waiting List',
                                'checked_in' => 'Checked In',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('registered')
                            ->live()
                            ->helperText('Select the registration status')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('waiting_position')
                            ->label('Waiting Position')
                            ->numeric()
                            ->minValue(1)
                            ->visible(fn (Forms\Get $get): bool => $get('status') === 'waiting')
                            ->helperText('Position in the waiting list')
                            ->columnSpan(2),

                        Forms\Components\DateTimePicker::make('checkin_time')
                            ->label('Check-in Time')
                            ->visible(fn (Forms\Get $get): bool => $get('status') === 'checked_in')
                            ->default(now())
                            ->columnSpan(2),
                    ])->columns(2),

                Forms\Components\Section::make('Seat Assignment')
                    ->schema([
                        Forms\Components\Placeholder::make('seat_info')
                            ->label('')
                            ->content('💡 Leave empty for automatic seat assignment, or manually set table and seat numbers.')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('table_number')
                            ->label('Table Number')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Auto-assigned if empty')
                            ->helperText('Optional: Set manually or leave empty for auto-assignment')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('seat_number')
                            ->label('Seat Number')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Auto-assigned if empty')
                            ->helperText('Optional: Set manually or leave empty for auto-assignment')
                            ->columnSpan(1),
                    ])->columns(2)
                    ->visible(fn (Forms\Get $get): bool => in_array($get('status'), ['registered', 'checked_in']))
                    ->description('Manually set table/seat or leave empty for automatic assignment'),

                Forms\Components\Section::make('QR Code')
                    ->schema([
                        Forms\Components\Textarea::make('qr_code')
                            ->label('QR Code URL')
                            ->disabled()
                            ->rows(2),

                        Forms\Components\TextInput::make('qr_checksum')
                            ->label('QR Checksum')
                            ->disabled(),
                    ])
                    ->collapsed()
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Player')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Registration $record): ?string => 
                        $record->player ? "Account: {$record->player->email}" : 'No account linked'
                    ),

                Tables\Columns\TextColumn::make('tournament.name')
                    ->label('Tournament')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->copyable()
                    ->copyMessage('Phone number copied'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'registered' => 'success',
                        'waiting' => 'warning',
                        'checked_in' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('table_seat')
                    ->label('Table/Seat')
                    ->getStateUsing(function (Registration $record): string {
                        if ($record->table_number && $record->seat_number) {
                            return "T{$record->table_number}/S{$record->seat_number}";
                        }
                        if ($record->waiting_position) {
                            return "WL #{$record->waiting_position}";
                        }
                        return '-';
                    })
                    ->icon(fn (Registration $record): ?string => 
                        $record->waiting_position ? 'heroicon-o-clock' : 'heroicon-o-squares-2x2'
                    ),

                Tables\Columns\IconColumn::make('checkin_time')
                    ->label('Checked In')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn (Registration $record): ?string => 
                        $record->checkin_time ? $record->checkin_time->format('M d, Y H:i') : null
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tournament')
                    ->relationship('tournament', 'name')
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
                        'registered' => 'Registered',
                        'waiting' => 'Waiting List',
                        'checked_in' => 'Checked In',
                        'cancelled' => 'Cancelled',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('has_table')
                    ->label('Has Table Assignment')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('table_number')),

                Tables\Filters\Filter::make('checked_in')
                    ->label('Checked In')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'checked_in')),

                Tables\Filters\Filter::make('has_player_account')
                    ->label('Has Player Account')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('player_id')),
            ])
            ->actions([
                Tables\Actions\Action::make('check_in')
                    ->label('Check In')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Registration $record): bool => $record->status === 'registered')
                    ->action(function (Registration $record) {
                        $record->update([
                            'status' => 'checked_in',
                            'checkin_time' => now(),
                        ]);
                    })
                    ->successNotification(
                        fn () => \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Player checked in')
                            ->body('The player has been successfully checked in.')
                    ),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\DeleteAction::make()
                    ->label('Cancel')
                    ->modalHeading('Cancel Registration')
                    ->successNotificationTitle('Registration cancelled'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('check_in_bulk')
                        ->label('Check In Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->status === 'registered') {
                                    $record->update([
                                        'status' => 'checked_in',
                                        'checkin_time' => now(),
                                    ]);
                                }
                            }
                        })
                        ->successNotification(
                            fn () => \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Players checked in')
                                ->body('Selected players have been checked in.')
                        ),

                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Cancel Selected')
                        ->modalHeading('Cancel Registrations')
                        ->successNotificationTitle('Registrations cancelled'),
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
            'index' => Pages\ListRegistrations::route('/'),
            'create' => Pages\CreateRegistration::route('/create'),
            'view' => Pages\ViewRegistration::route('/{record}'),
            'edit' => Pages\EditRegistration::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'registered')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['tournament'])
            ->latest();
    }
}

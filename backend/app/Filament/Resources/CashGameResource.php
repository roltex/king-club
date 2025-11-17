<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashGameResource\Pages;
use App\Models\CashGame;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Tabs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class CashGameResource extends Resource
{
    protected static ?string $model = CashGame::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    
    protected static ?string $navigationLabel = 'Cash Games';
    
    protected static ?string $modelLabel = 'Cash Game';
    
    protected static ?string $pluralModelLabel = 'Cash Games';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $navigationGroup = 'Cash Game Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Cash Game Details')
                    ->tabs([
                        // Basic Information Tab
                        Tabs\Tab::make('Basic Info')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Section::make('Cash Game Details')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state)))
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\TextInput::make('slug')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->helperText('Auto-generated from name')
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\RichEditor::make('description')
                                            ->columnSpanFull()
                                            ->maxLength(65535),
                                        
                                        Forms\Components\FileUpload::make('image_url')
                                            ->image()
                                            ->disk('public')
                                            ->directory('cash-games')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->columnSpan(1),
                                        
                                        Forms\Components\TextInput::make('table_number')
                                            ->required()
                                            ->numeric()
                                            ->unique(ignoreRecord: true)
                                            ->helperText('Unique table number')
                                            ->columnSpan(1),
                                    ])->columns(2),
                                
                                Forms\Components\Section::make('Status & Visibility')
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->required()
                                            ->options([
                                                'draft' => 'Draft',
                                                'active' => 'Active',
                                                'full' => 'Full',
                                                'closed' => 'Closed',
                                                'maintenance' => 'Maintenance',
                                                'cancelled' => 'Cancelled',
                                            ])
                                            ->default('draft')
                                            ->native(false),
                                        
                                        Forms\Components\Toggle::make('is_published')
                                            ->label('Published')
                                            ->default(true)
                                            ->inline(false),
                                        
                                        Forms\Components\Toggle::make('is_featured')
                                            ->label('Featured')
                                            ->default(false)
                                            ->inline(false),
                                    ])->columns(3),
                            ]),
                        
                        // Stakes & Buy-In Tab
                        Tabs\Tab::make('Stakes & Buy-In')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Forms\Components\Section::make('Stakes')
                                    ->schema([
                                        Forms\Components\TextInput::make('small_blind')
                                            ->required()
                                            ->numeric()
                                            ->prefix('₾')
                                            ->step(0.01)
                                            ->default(1)
                                            ->helperText('Small blind amount'),
                                        
                                        Forms\Components\TextInput::make('big_blind')
                                            ->required()
                                            ->numeric()
                                            ->prefix('₾')
                                            ->step(0.01)
                                            ->default(2)
                                            ->helperText('Big blind amount (usually 2x small blind)'),
                                    ])->columns(2),
                                
                                Forms\Components\Section::make('Buy-In Limits')
                                    ->schema([
                                        Forms\Components\TextInput::make('min_buy_in')
                                            ->required()
                                            ->numeric()
                                            ->prefix('₾')
                                            ->step(0.01)
                                            ->default(20)
                                            ->helperText('Minimum buy-in amount'),
                                        
                                        Forms\Components\TextInput::make('max_buy_in')
                                            ->required()
                                            ->numeric()
                                            ->prefix('₾')
                                            ->step(0.01)
                                            ->default(200)
                                            ->helperText('Maximum buy-in amount'),
                                        
                                        Forms\Components\TextInput::make('default_buy_in')
                                            ->required()
                                            ->numeric()
                                            ->prefix('₾')
                                            ->step(0.01)
                                            ->default(100)
                                            ->helperText('Suggested/default buy-in amount'),
                                    ])->columns(3),
                            ]),
                        
                        // Rake Structure Tab
                        Tabs\Tab::make('Rake Structure')
                            ->icon('heroicon-o-calculator')
                            ->schema([
                                Forms\Components\Section::make('Rake Configuration')
                                    ->schema([
                                        Forms\Components\Select::make('rake_type')
                                            ->required()
                                            ->options([
                                                'no_rake' => 'No Rake',
                                                'time_charge' => 'Time Charge (Per Hour)',
                                                'percentage' => 'Percentage of Pot',
                                                'cap' => 'Capped Percentage',
                                                'no_flop_no_drop' => 'No Flop No Drop',
                                            ])
                                            ->default('percentage')
                                            ->native(false)
                                            ->live()
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\TextInput::make('rake_percentage')
                                            ->numeric()
                                            ->prefix('%')
                                            ->step(0.01)
                                            ->maxValue(10)
                                            ->visible(fn (Forms\Get $get) => in_array($get('rake_type'), ['percentage', 'cap']))
                                            ->helperText('Percentage of pot taken as rake'),
                                        
                                        Forms\Components\TextInput::make('rake_cap')
                                            ->numeric()
                                            ->prefix('₾')
                                            ->step(0.01)
                                            ->visible(fn (Forms\Get $get) => $get('rake_type') === 'cap')
                                            ->helperText('Maximum rake per hand'),
                                        
                                        Forms\Components\TextInput::make('time_charge_amount')
                                            ->numeric()
                                            ->prefix('₾')
                                            ->step(0.01)
                                            ->visible(fn (Forms\Get $get) => $get('rake_type') === 'time_charge')
                                            ->helperText('Amount charged per time interval'),
                                        
                                        Forms\Components\TextInput::make('time_charge_interval')
                                            ->numeric()
                                            ->suffix('minutes')
                                            ->visible(fn (Forms\Get $get) => $get('rake_type') === 'time_charge')
                                            ->default(60)
                                            ->helperText('Time interval for charges'),
                                    ])->columns(2),
                            ]),
                        
                        // Game Settings Tab
                        Tabs\Tab::make('Game Settings')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Forms\Components\Section::make('Game Type')
                                    ->schema([
                                        Forms\Components\Select::make('game_type')
                                            ->required()
                                            ->options([
                                                'texas_holdem' => 'Texas Hold\'em',
                                                'omaha' => 'Omaha',
                                                'omaha_hilo' => 'Omaha Hi-Lo',
                                                'plo' => 'Pot-Limit Omaha',
                                                'plo5' => 'PLO 5-Card',
                                                'seven_card_stud' => 'Seven Card Stud',
                                                'razz' => 'Razz',
                                                'horse' => 'H.O.R.S.E.',
                                                'mixed_games' => 'Mixed Games',
                                                'short_deck' => 'Short Deck',
                                                'chinese_poker' => 'Chinese Poker',
                                            ])
                                            ->default('texas_holdem')
                                            ->native(false),
                                        
                                        Forms\Components\Select::make('structure')
                                            ->required()
                                            ->options([
                                                'nlhe' => 'No-Limit',
                                                'limit' => 'Limit',
                                                'pot_limit' => 'Pot-Limit',
                                                'mixed' => 'Mixed',
                                            ])
                                            ->default('nlhe')
                                            ->native(false),
                                    ])->columns(2),
                                
                                Forms\Components\Section::make('Table Configuration')
                                    ->schema([
                                        Forms\Components\TextInput::make('seats_per_table')
                                            ->required()
                                            ->numeric()
                                            ->default(9)
                                            ->minValue(2)
                                            ->maxValue(10)
                                            ->helperText('Number of seats at the table'),
                                        
                                        Forms\Components\TextInput::make('max_players')
                                            ->required()
                                            ->numeric()
                                            ->default(9)
                                            ->minValue(2)
                                            ->maxValue(10),
                                        
                                        Forms\Components\TextInput::make('min_players')
                                            ->numeric()
                                            ->default(2)
                                            ->minValue(2)
                                            ->helperText('Minimum players to start'),
                                    ])->columns(3),
                                
                                Forms\Components\Section::make('Game Options')
                                    ->schema([
                                        Forms\Components\Toggle::make('allow_rebuy')
                                            ->label('Allow Rebuy')
                                            ->default(true)
                                            ->helperText('Players can add more chips during play'),
                                        
                                        Forms\Components\Toggle::make('allow_side_pots')
                                            ->label('Allow Side Pots')
                                            ->default(true)
                                            ->helperText('Enable side pots for all-in situations'),
                                        
                                        Forms\Components\Toggle::make('show_hand_history')
                                            ->label('Show Hand History')
                                            ->default(true)
                                            ->helperText('Display hand history to players'),
                                        
                                        Forms\Components\Toggle::make('auto_seat_assignment')
                                            ->label('Auto Seat Assignment')
                                            ->default(true)
                                            ->helperText('Automatically assign seats to players'),
                                        
                                        Forms\Components\Toggle::make('enable_waiting_list')
                                            ->label('Enable Waiting List')
                                            ->default(true)
                                            ->helperText('Allow players to join waiting list when full'),
                                        
                                        Forms\Components\TextInput::make('max_waiting_list')
                                            ->numeric()
                                            ->visible(fn (Forms\Get $get) => $get('enable_waiting_list'))
                                            ->helperText('Maximum players in waiting list'),
                                    ])->columns(2),
                            ]),
                        
                        // Location Tab
                        Tabs\Tab::make('Location')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Forms\Components\Section::make('Venue Information')
                                    ->schema([
                                        Forms\Components\TextInput::make('venue_name')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\Textarea::make('address')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\TextInput::make('city')
                                            ->maxLength(255),
                                        
                                        Forms\Components\TextInput::make('state')
                                            ->maxLength(255),
                                        
                                        Forms\Components\TextInput::make('country')
                                            ->maxLength(255),
                                        
                                        Forms\Components\TextInput::make('postal_code')
                                            ->maxLength(255),
                                        
                                        Forms\Components\TextInput::make('latitude')
                                            ->numeric()
                                            ->step(0.00000001)
                                            ->helperText('For Google Maps integration'),
                                        
                                        Forms\Components\TextInput::make('longitude')
                                            ->numeric()
                                            ->step(0.00000001)
                                            ->helperText('For Google Maps integration'),
                                    ])->columns(3),
                            ]),
                        
                        // Operating Hours Tab
                        Tabs\Tab::make('Operating Hours')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Forms\Components\Section::make('Schedule')
                                    ->schema([
                                        Forms\Components\TimePicker::make('opens_at')
                                            ->label('Opens At')
                                            ->seconds(false)
                                            ->helperText('Opening time (leave empty for 24/7)'),
                                        
                                        Forms\Components\TimePicker::make('closes_at')
                                            ->label('Closes At')
                                            ->seconds(false)
                                            ->helperText('Closing time (leave empty for 24/7)'),
                                        
                                        Forms\Components\CheckboxList::make('operating_days')
                                            ->label('Operating Days')
                                            ->options([
                                                'monday' => 'Monday',
                                                'tuesday' => 'Tuesday',
                                                'wednesday' => 'Wednesday',
                                                'thursday' => 'Thursday',
                                                'friday' => 'Friday',
                                                'saturday' => 'Saturday',
                                                'sunday' => 'Sunday',
                                            ])
                                            ->columns(7)
                                            ->helperText('Leave empty for all days'),
                                    ])->columns(2),
                                
                                Forms\Components\Section::make('Reservations')
                                    ->schema([
                                        Forms\Components\Toggle::make('allow_reservations')
                                            ->label('Allow Reservations')
                                            ->default(true)
                                            ->helperText('Players can reserve seats in advance'),
                                        
                                        Forms\Components\Toggle::make('require_approval')
                                            ->label('Require Approval')
                                            ->default(false)
                                            ->helperText('Admin approval required for reservations'),
                                    ])->columns(2),
                            ]),
                        
                        // Contact & Notes Tab
                        Tabs\Tab::make('Contact & Notes')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                Forms\Components\Section::make('Contact Information')
                                    ->schema([
                                        Forms\Components\TextInput::make('contact_name')
                                            ->maxLength(255),
                                        
                                        Forms\Components\TextInput::make('contact_email')
                                            ->email()
                                            ->maxLength(255),
                                        
                                        Forms\Components\TextInput::make('contact_phone')
                                            ->tel()
                                            ->maxLength(255),
                                    ])->columns(3),
                                
                                Forms\Components\Section::make('Additional Notes')
                                    ->schema([
                                        Forms\Components\Textarea::make('notes')
                                            ->rows(5)
                                            ->columnSpanFull()
                                            ->helperText('Internal notes (not visible to players)'),
                                    ]),
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('table_number')
                    ->label('Table')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('stakes_display')
                    ->label('Stakes')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($record) => $record->stakes_display),
                
                Tables\Columns\TextColumn::make('game_type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),
                
                Tables\Columns\TextColumn::make('current_players')
                    ->label('Players')
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => "{$state}/{$record->seats_per_table}"),
                
                Tables\Columns\TextColumn::make('fill_percentage')
                    ->label('Fill')
                    ->formatStateUsing(fn ($state) => number_format($state, 1) . '%')
                    ->color(fn ($state) => $state >= 90 ? 'danger' : ($state >= 70 ? 'warning' : 'success')),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'active' => 'success',
                        'full' => 'warning',
                        'closed' => 'danger',
                        'maintenance' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),
                
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),
                
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->label('Published'),
                
                Tables\Columns\TextColumn::make('venue_name')
                    ->searchable()
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'full' => 'Full',
                        'closed' => 'Closed',
                        'maintenance' => 'Maintenance',
                        'cancelled' => 'Cancelled',
                    ]),
                
                Tables\Filters\SelectFilter::make('game_type')
                    ->options([
                        'texas_holdem' => 'Texas Hold\'em',
                        'omaha' => 'Omaha',
                        'plo' => 'Pot-Limit Omaha',
                        'mixed_games' => 'Mixed Games',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
                
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),
                
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('table_number');
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
            'index' => Pages\ListCashGames::route('/'),
            'create' => Pages\CreateCashGame::route('/create'),
            'view' => Pages\ViewCashGame::route('/{record}'),
            'edit' => Pages\EditCashGame::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}


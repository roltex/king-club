<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TournamentResource\Pages;
use App\Filament\Resources\TournamentResource\RelationManagers;
use App\Models\Tournament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Tabs;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class TournamentResource extends Resource
{
    protected static ?string $model = Tournament::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    
    protected static ?string $navigationLabel = 'Tournaments';
    
    protected static ?string $modelLabel = 'Tournament';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tournament Details')
                    ->tabs([
                        // Basic Information Tab
                        Tabs\Tab::make('Basic Info')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Section::make('Tournament Details')
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
                                            ->helperText('Auto-generated from tournament name')
                    ->columnSpanFull(),
                                        
                                        Forms\Components\RichEditor::make('description')
                                            ->columnSpanFull()
                                            ->maxLength(65535),
                                        
                                        Forms\Components\FileUpload::make('image_url')
                                            ->image()
                                            ->disk('public')
                                            ->directory('tournaments')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->columnSpan(1),
                                        
                                        Forms\Components\FileUpload::make('banner_url')
                                            ->image()
                                            ->disk('public')
                                            ->directory('tournaments/banners')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->columnSpan(1),
                                    ])->columns(2),
                                
                                Forms\Components\Section::make('Status & Visibility')
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->required()
                                            ->options([
                                                'draft' => 'Draft',
                                                'published' => 'Published',
                                                'registration_open' => 'Registration Open',
                                                'registration_closed' => 'Registration Closed',
                                                'in_progress' => 'In Progress',
                                                'completed' => 'Completed',
                                                'cancelled' => 'Cancelled',
                                            ])
                                            ->default('published')
                                            ->native(false),
                                        
                                        Forms\Components\Toggle::make('is_published')
                                            ->label('Published')
                                            ->default(true)
                                            ->inline(false),
                                        
                                        Forms\Components\Toggle::make('is_featured')
                                            ->label('Featured on Homepage')
                                            ->default(false)
                                            ->inline(false),
                                    ])->columns(3),
                            ]),
                        
                        // Schedule Tab
                        Tabs\Tab::make('Schedule')
                            ->icon('heroicon-o-calendar')
                            ->schema([
                                Forms\Components\Section::make('Tournament Schedule')
                                    ->schema([
                Forms\Components\DateTimePicker::make('start_date')
                                            ->required()
                                            ->native(false)
                                            ->displayFormat('Y-m-d H:i')
                                            ->seconds(false),
                                        
                                        Forms\Components\DateTimePicker::make('end_date')
                                            ->native(false)
                                            ->displayFormat('Y-m-d H:i')
                                            ->seconds(false)
                                            ->after('start_date'),
                                    ])->columns(2),
                                
                                Forms\Components\Section::make('Registration Period')
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('registration_start')
                                            ->native(false)
                                            ->displayFormat('Y-m-d H:i')
                                            ->seconds(false),
                                        
                                        Forms\Components\DateTimePicker::make('registration_end')
                                            ->native(false)
                                            ->displayFormat('Y-m-d H:i')
                                            ->seconds(false)
                                            ->after('registration_start'),
                                        
                Forms\Components\TextInput::make('late_registration_minutes')
                                            ->label('Late Registration (minutes)')
                    ->numeric()
                                            ->default(0)
                                            ->helperText('How long after start can players register'),
                                    ])->columns(3),
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
                    ->required()
                                            ->maxLength(65535)
                                            ->rows(2)
                    ->columnSpanFull(),
                                        
                Forms\Components\TextInput::make('city')
                                            ->required()
                                            ->maxLength(255),
                                        
                                        Forms\Components\TextInput::make('state')
                                            ->maxLength(255),
                                        
                Forms\Components\TextInput::make('country')
                                            ->required()
                                            ->maxLength(255)
                                            ->default('Georgia'),
                                        
                                        Forms\Components\TextInput::make('postal_code')
                                            ->maxLength(255),
                                    ])->columns(2),
                                
                                Forms\Components\Section::make('GPS Coordinates')
                                    ->description('For Google Maps integration')
                                    ->schema([
                Forms\Components\TextInput::make('latitude')
                                            ->numeric()
                                            ->step(0.00000001)
                                            ->placeholder('41.7151')
                                            ->helperText('Tbilisi: 41.7151'),
                                        
                Forms\Components\TextInput::make('longitude')
                                            ->numeric()
                                            ->step(0.00000001)
                                            ->placeholder('44.8271')
                                            ->helperText('Tbilisi: 44.8271'),
                                    ])->columns(2),
                            ]),
                        
                        // Tournament Type Tab
                        Tabs\Tab::make('Type & Structure')
                            ->icon('heroicon-o-puzzle-piece')
                            ->schema([
                                Forms\Components\Section::make('Tournament Configuration')
                                    ->schema([
                                        Forms\Components\Select::make('tournament_type')
                                            ->required()
                                            ->options([
                                                'freezeout' => 'Freezeout (No Rebuys)',
                                                'rebuy' => 'Rebuy',
                                                'addon' => 'Add-on',
                                                'bounty' => 'Bounty',
                                                'progressive_bounty' => 'Progressive Bounty',
                                                'turbo' => 'Turbo',
                                                'hyper_turbo' => 'Hyper Turbo',
                                                'deep_stack' => 'Deep Stack',
                                                'shootout' => 'Shootout',
                                                'satellite' => 'Satellite',
                                                'freeroll' => 'Freeroll',
                                                'guaranteed' => 'Guaranteed',
                                                'mystery_bounty' => 'Mystery Bounty',
                                            ])
                                            ->native(false)
                                            ->searchable(),
                                        
                                        Forms\Components\Select::make('game_type')
                                            ->required()
                                            ->options([
                                                'texas_holdem' => "Texas Hold'em",
                                                'omaha' => 'Omaha',
                                                'omaha_hilo' => 'Omaha Hi-Lo',
                                                'seven_card_stud' => '7-Card Stud',
                                                'razz' => 'Razz',
                                                'horse' => 'HORSE',
                                                'mixed_games' => 'Mixed Games',
                                                'plo' => 'Pot-Limit Omaha',
                                                'plo5' => '5-Card PLO',
                                                'short_deck' => 'Short Deck (6+)',
                                            ])
                                            ->default('texas_holdem')
                                            ->native(false)
                                            ->searchable(),
                                        
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
                                    ])->columns(3),
                            ]),
                        
                        // Tables & Buy-In Tab
                        Tabs\Tab::make('Tables & Buy-In')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Forms\Components\Section::make('Table Configuration')
                                    ->schema([
                Forms\Components\TextInput::make('total_tables')
                    ->required()
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(100)
                                            ->default(6),
                                        
                Forms\Components\TextInput::make('seats_per_table')
                    ->required()
                    ->numeric()
                                            ->minValue(2)
                                            ->maxValue(10)
                                            ->default(9)
                                            ->helperText('Usually 6, 8, 9, or 10'),
                                        
                                        Forms\Components\Placeholder::make('total_seats_display')
                                            ->label('Total Seats')
                                            ->content(fn ($get) => ($get('total_tables') ?? 0) * ($get('seats_per_table') ?? 0)),
                                    ])->columns(3),
                                
                                Forms\Components\Section::make('Buy-In Structure')
                                    ->schema([
                Forms\Components\TextInput::make('buy_in')
                    ->required()
                                            ->numeric()
                                            ->prefix('₾')
                                            ->step(0.01)
                                            ->minValue(0)
                                            ->default(100),
                                        
                Forms\Components\TextInput::make('entry_fee')
                                            ->label('Entry Fee (Rake)')
                    ->numeric()
                                            ->prefix('₾')
                                            ->step(0.01)
                                            ->default(10)
                                            ->helperText('House fee'),
                                        
                                        Forms\Components\Placeholder::make('total_buy_in_display')
                                            ->label('Total Buy-In')
                                            ->content(fn ($get) => '₾' . number_format(($get('buy_in') ?? 0) + ($get('entry_fee') ?? 0), 2)),
                                    ])->columns(3),
                                
                                Forms\Components\Section::make('Prize Pool')
                                    ->schema([
                Forms\Components\TextInput::make('guaranteed_prize')
                                            ->label('Guaranteed Prize Pool')
                                            ->numeric()
                                            ->prefix('₾')
                                            ->step(0.01)
                                            ->helperText('Optional guaranteed amount'),
                                        
                Forms\Components\TextInput::make('actual_prize_pool')
                                            ->label('Current Prize Pool')
                    ->numeric()
                                            ->prefix('₾')
                                            ->default(0)
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->helperText('Auto-calculated from registrations'),
                                    ])->columns(2),
                            ]),
                        
                        // Blinds & Structure Tab
                        Tabs\Tab::make('Blinds')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Forms\Components\Section::make('Starting Structure')
                                    ->schema([
                Forms\Components\TextInput::make('starting_stack')
                    ->required()
                    ->numeric()
                                            ->default(10000)
                                            ->helperText('Starting chip count'),
                                        
                Forms\Components\TextInput::make('level_duration')
                                            ->label('Level Duration (minutes)')
                    ->required()
                    ->numeric()
                    ->default(20),
                                        
                Forms\Components\TextInput::make('starting_blinds_small')
                                            ->label('Small Blind')
                    ->required()
                    ->numeric()
                    ->default(25),
                                        
                Forms\Components\TextInput::make('starting_blinds_big')
                                            ->label('Big Blind')
                    ->required()
                    ->numeric()
                    ->default(50),
                                    ])->columns(4),
                                
                                Forms\Components\Section::make('Blind Structure')
                                    ->description('Define the blind levels for this tournament. Levels will increase as the tournament progresses.')
                                    ->schema([
                                        Forms\Components\Repeater::make('blind_structure')
                                            ->label('Blind Levels')
                                            ->schema([
                                                Forms\Components\TextInput::make('level')
                                                    ->label('Level')
                                                    ->numeric()
                                                    ->default(fn ($get) => count($get('../../blind_structure') ?? []) + 1)
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->columnSpan(1),
                                                
                                                Forms\Components\TextInput::make('small')
                                                    ->label('Small Blind')
                                                    ->numeric()
                                                    ->required()
                                                    ->minValue(0)
                                                    ->placeholder('25')
                                                    ->columnSpan(1),
                                                
                                                Forms\Components\TextInput::make('big')
                                                    ->label('Big Blind')
                                                    ->numeric()
                                                    ->required()
                                                    ->minValue(0)
                                                    ->placeholder('50')
                                                    ->columnSpan(1),
                                                
                                                Forms\Components\TextInput::make('ante')
                                                    ->label('Ante')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->minValue(0)
                                                    ->placeholder('0')
                                                    ->helperText('Optional chip ante')
                                                    ->columnSpan(1),
                                                
                                                Forms\Components\TextInput::make('duration')
                                                    ->label('Duration (minutes)')
                                                    ->numeric()
                                                    ->default(fn ($get) => $get('../../../level_duration') ?? 20)
                                                    ->minValue(1)
                                                    ->suffix('min')
                                                    ->helperText('Override tournament default if needed')
                                                    ->columnSpan(2),
                                            ])
                                            ->columns(6)
                                            ->defaultItems(3)
                                            ->itemLabel(fn (array $state): ?string => isset($state['small']) && isset($state['big'])
                                                ? "Level " . ($state['level'] ?? '?') . ": {$state['small']}/{$state['big']}" . (($state['ante'] ?? 0) > 0 ? " (Ante: {$state['ante']})" : '')
                                                : null)
                                            ->collapsed()
                                            ->cloneable()
                                            ->collapsible()
                                            ->reorderable(false)
                                            ->addActionLabel('Add Blind Level')
                                            ->columnSpanFull()
                                            ->mutateDehydratedStateUsing(function ($state) {
                                                if (is_array($state) && !empty($state)) {
                                                    // Re-index levels when saving, ensure all values are properly typed
                                                    $reindexed = [];
                                                    $level = 1;
                                                    foreach ($state as $item) {
                                                        if (is_array($item)) {
                                                            $reindexed[] = [
                                                                'level' => $level++,
                                                                'small' => (int)($item['small'] ?? 0),
                                                                'big' => (int)($item['big'] ?? 0),
                                                                'ante' => (int)($item['ante'] ?? 0),
                                                                'duration' => (int)($item['duration'] ?? 20),
                                                            ];
                                                        }
                                                    }
                                                    return $reindexed;
                                                }
                                                return $state ?? [];
                                            })
                                            ->helperText('💡 Tip: Start with small blinds (25/50) and gradually increase. Common structures double every few levels.'),
                                        
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('generate_blind_structure')
                                                ->label('Generate Blind Structure (30 Levels)')
                                                ->icon('heroicon-o-sparkles')
                                                ->color('primary')
                                                ->action(function (Forms\Set $set, Forms\Get $get) {
                                                    $levelDuration = $get('level_duration') ?? 20;
                                                    $structure = [
                                                        ['level' => 1, 'small' => 25, 'big' => 50, 'ante' => 0, 'duration' => $levelDuration],
                                                        ['level' => 2, 'small' => 50, 'big' => 100, 'ante' => 0, 'duration' => $levelDuration],
                                                        ['level' => 3, 'small' => 75, 'big' => 150, 'ante' => 0, 'duration' => $levelDuration],
                                                        ['level' => 4, 'small' => 100, 'big' => 200, 'ante' => 25, 'duration' => $levelDuration],
                                                        ['level' => 5, 'small' => 150, 'big' => 300, 'ante' => 50, 'duration' => $levelDuration],
                                                        ['level' => 6, 'small' => 200, 'big' => 400, 'ante' => 50, 'duration' => $levelDuration],
                                                        ['level' => 7, 'small' => 300, 'big' => 600, 'ante' => 75, 'duration' => $levelDuration],
                                                        ['level' => 8, 'small' => 400, 'big' => 800, 'ante' => 100, 'duration' => $levelDuration],
                                                        ['level' => 9, 'small' => 500, 'big' => 1000, 'ante' => 100, 'duration' => $levelDuration],
                                                        ['level' => 10, 'small' => 600, 'big' => 1200, 'ante' => 200, 'duration' => $levelDuration],
                                                        ['level' => 11, 'small' => 800, 'big' => 1600, 'ante' => 200, 'duration' => $levelDuration],
                                                        ['level' => 12, 'small' => 1000, 'big' => 2000, 'ante' => 300, 'duration' => $levelDuration],
                                                        ['level' => 13, 'small' => 1200, 'big' => 2400, 'ante' => 300, 'duration' => $levelDuration],
                                                        ['level' => 14, 'small' => 1500, 'big' => 3000, 'ante' => 500, 'duration' => $levelDuration],
                                                        ['level' => 15, 'small' => 2000, 'big' => 4000, 'ante' => 500, 'duration' => $levelDuration],
                                                        ['level' => 16, 'small' => 2500, 'big' => 5000, 'ante' => 500, 'duration' => $levelDuration],
                                                        ['level' => 17, 'small' => 3000, 'big' => 6000, 'ante' => 1000, 'duration' => $levelDuration],
                                                        ['level' => 18, 'small' => 4000, 'big' => 8000, 'ante' => 1000, 'duration' => $levelDuration],
                                                        ['level' => 19, 'small' => 5000, 'big' => 10000, 'ante' => 1000, 'duration' => $levelDuration],
                                                        ['level' => 20, 'small' => 6000, 'big' => 12000, 'ante' => 2000, 'duration' => $levelDuration],
                                                        ['level' => 21, 'small' => 8000, 'big' => 16000, 'ante' => 2000, 'duration' => $levelDuration],
                                                        ['level' => 22, 'small' => 10000, 'big' => 20000, 'ante' => 3000, 'duration' => $levelDuration],
                                                        ['level' => 23, 'small' => 12000, 'big' => 24000, 'ante' => 3000, 'duration' => $levelDuration],
                                                        ['level' => 24, 'small' => 15000, 'big' => 30000, 'ante' => 5000, 'duration' => $levelDuration],
                                                        ['level' => 25, 'small' => 20000, 'big' => 40000, 'ante' => 5000, 'duration' => $levelDuration],
                                                        ['level' => 26, 'small' => 25000, 'big' => 50000, 'ante' => 5000, 'duration' => $levelDuration],
                                                        ['level' => 27, 'small' => 30000, 'big' => 60000, 'ante' => 10000, 'duration' => $levelDuration],
                                                        ['level' => 28, 'small' => 40000, 'big' => 80000, 'ante' => 10000, 'duration' => $levelDuration],
                                                        ['level' => 29, 'small' => 50000, 'big' => 100000, 'ante' => 10000, 'duration' => $levelDuration],
                                                        ['level' => 30, 'small' => 60000, 'big' => 120000, 'ante' => 20000, 'duration' => $levelDuration],
                                                    ];
                                                    $set('blind_structure', $structure);
                                                }),
                                        ])->columnSpanFull(),
                                    ]),
                            ]),
                        
                        // Rebuys & Add-ons Tab
                        Tabs\Tab::make('Rebuys & Add-ons')
                            ->icon('heroicon-o-arrow-path')
                            ->schema([
                                Forms\Components\Section::make('Rebuy Options')
                                    ->schema([
                Forms\Components\Toggle::make('rebuys_allowed')
                                            ->label('Allow Rebuys')
                                            ->live()
                                            ->inline(false),
                                        
                Forms\Components\TextInput::make('rebuy_levels')
                                            ->label('Rebuy Until Level')
                                            ->numeric()
                                            ->visible(fn ($get) => $get('rebuys_allowed')),
                                        
                Forms\Components\TextInput::make('rebuy_cost')
                                            ->label('Rebuy Cost')
                                            ->numeric()
                                            ->prefix('₾')
                                            ->visible(fn ($get) => $get('rebuys_allowed')),
                                    ])->columns(3),
                                
                                Forms\Components\Section::make('Add-on Options')
                                    ->schema([
                Forms\Components\Toggle::make('addon_allowed')
                                            ->label('Allow Add-ons')
                                            ->live()
                                            ->inline(false),
                                        
                Forms\Components\TextInput::make('addon_cost')
                                            ->label('Add-on Cost')
                                            ->numeric()
                                            ->prefix('₾')
                                            ->visible(fn ($get) => $get('addon_allowed')),
                                        
                Forms\Components\TextInput::make('addon_chips')
                                            ->label('Add-on Chips')
                                            ->numeric()
                                            ->visible(fn ($get) => $get('addon_allowed')),
                                    ])->columns(3),
                                
                                Forms\Components\Section::make('Bounty Options')
                                    ->schema([
                Forms\Components\TextInput::make('bounty_amount')
                                            ->label('Bounty Amount')
                                            ->numeric()
                                            ->prefix('₾')
                                            ->helperText('Bounty per player knockout'),
                                        
                Forms\Components\Toggle::make('progressive_bounty')
                                            ->label('Progressive Bounty')
                                            ->helperText('Bounty grows with each knockout')
                                            ->inline(false),
                                    ])->columns(2),
                            ]),
                        
                        // Settings Tab
                        Tabs\Tab::make('Settings')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Forms\Components\Section::make('Registration Settings')
                                    ->schema([
                Forms\Components\Toggle::make('allow_early_registration')
                                            ->label('Allow Early Registration')
                                            ->default(true)
                                            ->inline(false),
                                        
                Forms\Components\Toggle::make('require_approval')
                                            ->label('Require Manual Approval')
                                            ->default(false)
                                            ->inline(false),
                                        
                Forms\Components\Toggle::make('auto_seat_assignment')
                                            ->label('Auto Seat Assignment')
                                            ->default(true)
                                            ->inline(false),
                                    ])->columns(3),
                                
                                Forms\Components\Section::make('Waiting List')
                                    ->schema([
                                        Forms\Components\Toggle::make('waiting_list_enabled')
                                            ->label('Enable Waiting List')
                                            ->default(true)
                                            ->live()
                                            ->inline(false),
                                        
                                        Forms\Components\TextInput::make('max_waiting_list')
                                            ->label('Max Waiting List Size')
                                            ->numeric()
                                            ->visible(fn ($get) => $get('waiting_list_enabled'))
                                            ->helperText('Leave empty for unlimited'),
                                    ])->columns(2),
                                
                                Forms\Components\Section::make('Check-In Options')
                                    ->schema([
                Forms\Components\Toggle::make('enable_qr_checkin')
                                            ->label('Enable QR Code Check-In')
                                            ->default(true)
                                            ->inline(false),
                                    ]),
                            ]),
                        
                        // Contact Tab
                        Tabs\Tab::make('Contact & Rules')
                            ->icon('heroicon-o-envelope')
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
                                
                                Forms\Components\Section::make('Rules & Notes')
                                    ->schema([
                                        Forms\Components\TextInput::make('rules_url')
                                            ->label('Rules URL')
                                            ->url()
                                            ->maxLength(255)
                                            ->placeholder('https://example.com/tournament-rules')
                    ->columnSpanFull(),
                                        
                Forms\Components\Textarea::make('notes')
                                            ->label('Internal Notes')
                                            ->rows(5)
                                            ->maxLength(65535)
                                            ->helperText('Private notes (not visible to players)')
                    ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('/images/tournament-default.png')),
                
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->color(fn ($record) => $record->days_until_start < 0 ? 'gray' : ($record->days_until_start < 7 ? 'warning' : 'success')),
                
                Tables\Columns\TextColumn::make('tournament_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('game_type')
                    ->label('Game')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('buy_in')
                    ->label('Buy-In')
                    ->money('GEL')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_seats')
                    ->label('Seats')
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('registrations_count')
                    ->label('Registered')
                    ->counts('registrations')
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state, $record) => $state >= $record->total_seats ? 'danger' : 'success'),
                
                Tables\Columns\TextColumn::make('status')
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
                
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('venue_name')
                    ->label('Venue')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'registration_open' => 'Registration Open',
                        'registration_closed' => 'Registration Closed',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('tournament_type')
                    ->label('Type')
                    ->options([
                        'freezeout' => 'Freezeout',
                        'rebuy' => 'Rebuy',
                        'bounty' => 'Bounty',
                        'turbo' => 'Turbo',
                        'satellite' => 'Satellite',
                    ])
                    ->multiple(),
                
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured')
                    ->placeholder('All tournaments')
                    ->trueLabel('Featured only')
                    ->falseLabel('Not featured'),
                
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published')
                    ->placeholder('All tournaments')
                    ->trueLabel('Published only')
                    ->falseLabel('Drafts only'),
                
                Tables\Filters\Filter::make('upcoming')
                    ->label('Upcoming Only')
                    ->query(fn (Builder $query): Builder => $query->where('start_date', '>', now())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('view_registrations')
                    ->label('Registrations')
                    ->icon('heroicon-o-user-group')
                    ->url(fn ($record) => route('filament.admin.resources.registrations.index', ['tournament' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_published' => true]))
                        ->color('success'),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish Selected')
                        ->icon('heroicon-o-x-mark')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_published' => false]))
                        ->color('warning'),
                ]),
            ])
            ->defaultSort('start_date', 'desc');
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
            'index' => Pages\ListTournaments::route('/'),
            'create' => Pages\CreateTournament::route('/create'),
            'edit' => Pages\EditTournament::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'registration_open')->count();
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}

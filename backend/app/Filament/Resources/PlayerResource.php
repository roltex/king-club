<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlayerResource\Pages;
use App\Filament\Resources\PlayerResource\RelationManagers;
use App\Models\Player;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class PlayerResource extends Resource
{
    protected static ?string $model = Player::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'Players';
    
    protected static ?string $navigationGroup = 'User Management';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->prefix('+')
                            ->placeholder('995555123456')
                            ->helperText('Enter phone number with country code')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('date_of_birth')
                            ->maxDate(now()->subYears(18))
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->helperText('Must be at least 18 years old')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('city')
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\Select::make('country')
                            ->options([
                                'Georgia' => 'Georgia',
                                'Armenia' => 'Armenia',
                                'Azerbaijan' => 'Azerbaijan',
                                'Turkey' => 'Turkey',
                                'Russia' => 'Russia',
                                'Ukraine' => 'Ukraine',
                                'Other' => 'Other',
                            ])
                            ->default('Georgia')
                            ->searchable()
                            ->columnSpan(1),

                        Forms\Components\FileUpload::make('profile_image')
                            ->image()
                            ->disk('public')
                            ->directory('players/profiles')
                            ->visibility('public')
                            ->imageEditor()
                            ->columnSpan(1),
                    ])->columns(2),

                Forms\Components\Section::make('Account Settings')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(6)
                            ->maxLength(255)
                            ->revealable()
                            ->helperText('Leave empty to keep current password (when editing)')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->dehydrated(false)
                            ->required(fn (string $context): bool => $context === 'create')
                            ->same('password')
                            ->minLength(6)
                            ->maxLength(255)
                            ->revealable()
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Account')
                            ->default(true)
                            ->helperText('Inactive accounts cannot login')
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('email_verified')
                            ->label('Email Verified')
                            ->default(false)
                            ->helperText('Mark as verified if you confirmed the email')
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->visible(fn (Forms\Get $get): bool => $get('email_verified'))
                            ->default(now())
                            ->columnSpan(2),
                    ])->columns(2),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('Internal notes (not visible to player)')
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_image')
                    ->label('Photo')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.png')),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name', 'last_name'])
                    ->weight('bold')
                    ->description(fn (Player $record): string => $record->email),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->copyable()
                    ->copyMessage('Phone copied'),

                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->toggleable()
                    ->icon('heroicon-o-map-pin'),

                Tables\Columns\TextColumn::make('registrations_count')
                    ->label('Tournaments')
                    ->counts('registrations')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('email_verified')
                    ->label('Verified')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All players')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\TernaryFilter::make('email_verified')
                    ->label('Email Verification')
                    ->placeholder('All players')
                    ->trueLabel('Verified only')
                    ->falseLabel('Unverified only'),

                Tables\Filters\SelectFilter::make('country')
                    ->options([
                        'Georgia' => 'Georgia',
                        'Armenia' => 'Armenia',
                        'Azerbaijan' => 'Azerbaijan',
                        'Turkey' => 'Turkey',
                        'Russia' => 'Russia',
                        'Ukraine' => 'Ukraine',
                        'Other' => 'Other',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('has_registrations')
                    ->label('Has Registrations')
                    ->query(fn (Builder $query): Builder => $query->has('registrations')),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('view_registrations')
                        ->label('View Registrations')
                        ->icon('heroicon-o-ticket')
                        ->color('info')
                        ->url(fn (Player $record): string => route('filament.admin.resources.registrations.index', ['tableFilters[player_id][value]' => $record->id]))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('toggle_active')
                        ->label(fn (Player $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                        ->icon(fn (Player $record): string => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn (Player $record): string => $record->is_active ? 'danger' : 'success')
                        ->requiresConfirmation()
                        ->action(fn (Player $record) => $record->update(['is_active' => !$record->is_active]))
                        ->successNotificationTitle(fn (Player $record): string => $record->is_active ? 'Player activated' : 'Player deactivated'),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\RegistrationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlayers::route('/'),
            'create' => Pages\CreatePlayer::route('/create'),
            'view' => Pages\ViewPlayer::route('/{record}'),
            'edit' => Pages\EditPlayer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}

@php
    use Illuminate\Support\Facades\Storage;
    $data = $this->getViewData();
    $tournaments = $data['tournaments'] ?? [];
    $cashGames = $data['cashGames'] ?? [];
@endphp

<x-filament-widgets::widget>
    <div style="padding: 1rem;">
        <!-- Tournaments Section -->
        <div style="margin-bottom: 2rem;">
            <div style="margin-bottom: 1.25rem;">
                <h3 style="font-size: 1.5rem; font-weight: 800; color: #1f2937; margin: 0 0 0.25rem 0;">Active Tournaments</h3>
                <p style="font-size: 0.875rem; color: #6b7280; margin: 0;">Click to view dashboard</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem;">
                @forelse($tournaments as $tournament)
                @php
                    $fillPercentage = $tournament->total_seats > 0 ? min(100, ($tournament->registered_count / $tournament->total_seats) * 100) : 0;
                    $statusConfig = [
                        'in_progress' => ['color' => '#10b981', 'bg' => '#10b981', 'text' => 'Live'],
                        'registration_open' => ['color' => '#3b82f6', 'bg' => '#3b82f6', 'text' => 'Open'],
                        'registration_closed' => ['color' => '#f59e0b', 'bg' => '#f59e0b', 'text' => 'Closed'],
                    ];
                    $status = $statusConfig[$tournament->status] ?? $statusConfig['registration_open'];
                    $prizePool = $tournament->guaranteed_prize ?? $tournament->prize_pool ?? 0;
                    if ($prizePool == 0) {
                        $prizePool = $tournament->registered_count * $tournament->buy_in;
                    }
                    $availableSeats = $tournament->total_seats - $tournament->registered_count;
                @endphp
                <a href="{{ url('/admin/tournament-dashboard?tournament=' . $tournament->id) }}" 
                   style="display: block; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; transition: all 0.2s; text-decoration: none; color: inherit;"
                   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'; this.style.borderColor='#667eea'"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)'; this.style.borderColor='#e5e7eb'">
                    
                    <!-- Compact Header -->
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1rem; position: relative;">
                        @if($tournament->image_url)
                            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.2;">
                                <img src="{{ Storage::url($tournament->image_url) }}" 
                                     alt="{{ $tournament->name }}"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        @endif
                        
                        <div style="position: relative; z-index: 1; display: flex; align-items: start; justify-content: space-between; gap: 0.75rem;">
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                    <span style="background: {{ $status['bg'] }}; color: white; padding: 0.25rem 0.625rem; border-radius: 4px; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                        {{ $status['text'] }}
                                    </span>
                                    @if($tournament->is_featured)
                                        <span style="background: #fbbf24; color: white; padding: 0.25rem 0.625rem; border-radius: 4px; font-size: 0.6875rem; font-weight: 700;">⭐</span>
                                    @endif
                                </div>
                                <h4 style="font-size: 1.125rem; font-weight: 800; color: white; margin: 0; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ $tournament->name }}
                                </h4>
                            </div>
                            <div style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); padding: 0.5rem 0.75rem; border-radius: 8px; text-align: right; flex-shrink: 0;">
                                <div style="font-size: 0.6875rem; color: rgba(255,255,255,0.9); font-weight: 600; margin-bottom: 0.125rem;">Prize</div>
                                <div style="font-size: 1.25rem; font-weight: 900; color: white; line-height: 1;">₾{{ number_format($prizePool) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Compact Body -->
                    <div style="padding: 1rem;">
                        <!-- Stats Row -->
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; margin-bottom: 1rem;">
                            <div style="background: #3b82f6; padding: 0.625rem 0.5rem; border-radius: 8px; text-align: center; color: white;">
                                <div style="font-size: 0.625rem; opacity: 0.9; margin-bottom: 0.25rem; font-weight: 600;">Buy-in</div>
                                <div style="font-size: 0.9375rem; font-weight: 800;">₾{{ number_format($tournament->buy_in) }}</div>
                            </div>
                            <div style="background: #06b6d4; padding: 0.625rem 0.5rem; border-radius: 8px; text-align: center; color: white;">
                                <div style="font-size: 0.625rem; opacity: 0.9; margin-bottom: 0.25rem; font-weight: 600;">Players</div>
                                <div style="font-size: 0.9375rem; font-weight: 800;">{{ $tournament->registered_count }}</div>
                                <div style="font-size: 0.625rem; opacity: 0.8; margin-top: 0.125rem;">/{{ $tournament->total_seats }}</div>
                            </div>
                            <div style="background: #10b981; padding: 0.625rem 0.5rem; border-radius: 8px; text-align: center; color: white;">
                                <div style="font-size: 0.625rem; opacity: 0.9; margin-bottom: 0.25rem; font-weight: 600;">Checked</div>
                                <div style="font-size: 0.9375rem; font-weight: 800;">{{ $tournament->checked_in_count ?? 0 }}</div>
                            </div>
                            <div style="background: #f59e0b; padding: 0.625rem 0.5rem; border-radius: 8px; text-align: center; color: white;">
                                <div style="font-size: 0.625rem; opacity: 0.9; margin-bottom: 0.25rem; font-weight: 600;">Free</div>
                                <div style="font-size: 0.9375rem; font-weight: 800;">{{ $availableSeats }}</div>
                            </div>
                        </div>

                        <!-- Compact Info Row -->
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid #e5e7eb;">
                            <div style="display: flex; align-items: center; gap: 0.375rem; color: #6b7280; font-size: 0.8125rem;">
                                <span>📅</span>
                                <span>{{ $tournament->start_date->format('M d, H:i') }}</span>
                            </div>
                            @if($tournament->venue_name)
                            <div style="display: flex; align-items: center; gap: 0.375rem; color: #6b7280; font-size: 0.8125rem; max-width: 50%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <span>📍</span>
                                <span>{{ $tournament->venue_name }}</span>
                            </div>
                            @endif
                        </div>

                        <!-- Progress Bar -->
                        <div style="margin-bottom: 0.75rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="font-size: 0.75rem; color: #6b7280; font-weight: 600;">Fill Rate</span>
                                <span style="font-size: 1rem; font-weight: 800; color: #1f2937;">{{ round($fillPercentage) }}%</span>
                            </div>
                            <div style="width: 100%; background: #e5e7eb; border-radius: 6px; height: 6px; overflow: hidden;">
                                <div style="height: 100%; border-radius: 6px; width: {{ $fillPercentage }}%; transition: width 0.3s;
                                    background: {{ $fillPercentage >= 90 ? '#ef4444' : ($fillPercentage >= 70 ? '#f59e0b' : '#10b981') }};"></div>
                            </div>
                        </div>

                        <!-- Compact Button -->
                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 0.625rem; border-radius: 8px; text-align: center; color: white; font-weight: 700; font-size: 0.8125rem; cursor: pointer; transition: all 0.2s;"
                             onmouseover="this.style.opacity='0.9'; this.style.transform='scale(1.01)'"
                             onmouseout="this.style.opacity='1'; this.style.transform='scale(1)'">
                            View Dashboard →
                        </div>
                    </div>
                </a>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 2rem 1rem;">
                        <div style="width: 48px; height: 48px; margin: 0 auto 0.75rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 1.5rem;">🎯</span>
                        </div>
                        <p style="font-size: 0.875rem; color: #6b7280; margin: 0;">No active tournaments</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Cash Games Section -->
        <div>
            <div style="margin-bottom: 1.25rem;">
                <h3 style="font-size: 1.5rem; font-weight: 800; color: #1f2937; margin: 0 0 0.25rem 0;">Active Cash Games</h3>
                <p style="font-size: 0.875rem; color: #6b7280; margin: 0;">Click to view dashboard</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem;">
                @forelse($cashGames as $cashGame)
                    @php
                        $fillPercentage = $cashGame->seats_per_table > 0 ? min(100, (($cashGame->active_seats_count ?? 0) / $cashGame->seats_per_table) * 100) : 0;
                        $statusConfig = [
                            'running' => ['color' => '#10b981', 'bg' => '#10b981', 'text' => 'Running'],
                            'active' => ['color' => '#3b82f6', 'bg' => '#3b82f6', 'text' => 'Active'],
                            'open' => ['color' => '#3b82f6', 'bg' => '#3b82f6', 'text' => 'Open'],
                        ];
                        $status = $statusConfig[$cashGame->status] ?? $statusConfig['open'];
                        $availableSeats = $cashGame->seats_per_table - ($cashGame->active_seats_count ?? 0);
                    @endphp
                    <a href="{{ url('/admin/cash-game-dashboard?cash_game=' . $cashGame->id) }}" 
                       style="display: block; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; transition: all 0.2s; text-decoration: none; color: inherit;"
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'; this.style.borderColor='#10b981'"
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)'; this.style.borderColor='#e5e7eb'">
                        
                        <!-- Compact Header -->
                        <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 1rem; position: relative;">
                            @if($cashGame->image_url)
                                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.2;">
                                    <img src="{{ Storage::url($cashGame->image_url) }}" 
                                         alt="{{ $cashGame->name }}"
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            @endif
                            
                            <div style="position: relative; z-index: 1; display: flex; align-items: start; justify-content: space-between; gap: 0.75rem;">
                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                        <span style="background: {{ $status['bg'] }}; color: white; padding: 0.25rem 0.625rem; border-radius: 4px; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                            {{ $status['text'] }}
                                        </span>
                                        @if($cashGame->is_featured)
                                            <span style="background: #fbbf24; color: white; padding: 0.25rem 0.625rem; border-radius: 4px; font-size: 0.6875rem; font-weight: 700;">⭐</span>
                                        @endif
                                    </div>
                                    <h4 style="font-size: 1.125rem; font-weight: 800; color: white; margin: 0; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $cashGame->name }}
                                    </h4>
                                </div>
                                <div style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); padding: 0.5rem 0.75rem; border-radius: 8px; text-align: right; flex-shrink: 0;">
                                    <div style="font-size: 0.6875rem; color: rgba(255,255,255,0.9); font-weight: 600; margin-bottom: 0.125rem;">Stakes</div>
                                    <div style="font-size: 1.25rem; font-weight: 900; color: white; line-height: 1;">{{ $cashGame->stakes_display }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Compact Body -->
                        <div style="padding: 1rem;">
                            <!-- Stats Row -->
                            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; margin-bottom: 1rem;">
                                <div style="background: #3b82f6; padding: 0.625rem 0.5rem; border-radius: 8px; text-align: center; color: white;">
                                    <div style="font-size: 0.625rem; opacity: 0.9; margin-bottom: 0.25rem; font-weight: 600;">Buy-in</div>
                                    <div style="font-size: 0.9375rem; font-weight: 800;">₾{{ number_format($cashGame->default_buy_in ?? $cashGame->min_buy_in) }}</div>
                                </div>
                                <div style="background: #06b6d4; padding: 0.625rem 0.5rem; border-radius: 8px; text-align: center; color: white;">
                                    <div style="font-size: 0.625rem; opacity: 0.9; margin-bottom: 0.25rem; font-weight: 600;">Players</div>
                                    <div style="font-size: 0.9375rem; font-weight: 800;">{{ $cashGame->active_seats_count ?? 0 }}</div>
                                    <div style="font-size: 0.625rem; opacity: 0.8; margin-top: 0.125rem;">/{{ $cashGame->seats_per_table }}</div>
                                </div>
                                <div style="background: #10b981; padding: 0.625rem 0.5rem; border-radius: 8px; text-align: center; color: white;">
                                    <div style="font-size: 0.625rem; opacity: 0.9; margin-bottom: 0.25rem; font-weight: 600;">Playing</div>
                                    <div style="font-size: 0.9375rem; font-weight: 800;">{{ $cashGame->seats()->where('status', 'playing')->count() }}</div>
                                </div>
                                <div style="background: #f59e0b; padding: 0.625rem 0.5rem; border-radius: 8px; text-align: center; color: white;">
                                    <div style="font-size: 0.625rem; opacity: 0.9; margin-bottom: 0.25rem; font-weight: 600;">Free</div>
                                    <div style="font-size: 0.9375rem; font-weight: 800;">{{ max(0, $availableSeats) }}</div>
                                </div>
                            </div>

                            <!-- Compact Info Row -->
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid #e5e7eb;">
                                <div style="display: flex; align-items: center; gap: 0.375rem; color: #6b7280; font-size: 0.8125rem;">
                                    <span>🪑</span>
                                    <span>Table {{ $cashGame->table_number }}</span>
                                </div>
                                @if($cashGame->venue_name)
                                <div style="display: flex; align-items: center; gap: 0.375rem; color: #6b7280; font-size: 0.8125rem; max-width: 50%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <span>📍</span>
                                    <span>{{ $cashGame->venue_name }}</span>
                                </div>
                                @endif
                            </div>

                            <!-- Progress Bar -->
                            <div style="margin-bottom: 0.75rem;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span style="font-size: 0.75rem; color: #6b7280; font-weight: 600;">Fill Rate</span>
                                    <span style="font-size: 1rem; font-weight: 800; color: #1f2937;">{{ round($fillPercentage) }}%</span>
                                </div>
                                <div style="width: 100%; background: #e5e7eb; border-radius: 6px; height: 6px; overflow: hidden;">
                                    <div style="height: 100%; border-radius: 6px; width: {{ $fillPercentage }}%; transition: width 0.3s;
                                        background: {{ $fillPercentage >= 90 ? '#ef4444' : ($fillPercentage >= 70 ? '#f59e0b' : '#10b981') }};"></div>
                                </div>
                            </div>

                            <!-- Compact Button -->
                            <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 0.625rem; border-radius: 8px; text-align: center; color: white; font-weight: 700; font-size: 0.8125rem; cursor: pointer; transition: all 0.2s;"
                                 onmouseover="this.style.opacity='0.9'; this.style.transform='scale(1.01)'"
                                 onmouseout="this.style.opacity='1'; this.style.transform='scale(1)'">
                                View Dashboard →
                            </div>
                        </div>
                    </a>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 2rem 1rem;">
                        <div style="width: 48px; height: 48px; margin: 0 auto 0.75rem; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 1.5rem;">💰</span>
                        </div>
                        <p style="font-size: 0.875rem; color: #6b7280; margin: 0;">No active cash games</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-widgets::widget>

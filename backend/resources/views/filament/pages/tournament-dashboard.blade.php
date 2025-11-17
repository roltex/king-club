@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;
@endphp

<x-filament-panels::page>
    @if($this->tournament)
        <div id="tournament-dashboard" style="background: #f8f9fb; padding: 0; margin: -1.5rem; height: 100vh; overflow: hidden; display: flex; flex-direction: column;">
            
            <!-- Header with Stats in One Line -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 0.75rem 1rem; color: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); flex-shrink: 0;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                    <!-- Left: Tournament Info -->
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            🏆
                        </div>
                        <div>
                            <h1 style="margin: 0; font-size: 1rem; font-weight: 700; line-height: 1.2;">{{ $this->tournament->name }}</h1>
                            <div style="margin-top: 0.125rem; font-size: 0.7rem; opacity: 0.9; display: flex; gap: 0.75rem;">
                                <span>📅 {{ $this->tournament->start_date->format('M d, Y') }}</span>
                                <span>🕐 {{ $this->tournament->start_date->format('h:i A') }}</span>
                                <span>💰 ₾{{ number_format($this->tournament->buy_in) }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Center: Stats -->
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <div style="background: rgba(255,255,255,0.2); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 75px; text-align: center; backdrop-filter: blur(10px);">
                            <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">{{ $this->tournament->total_seats }}</div>
                            <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Total</div>
                        </div>
                        <div style="background: rgba(6, 182, 212, 0.9); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 75px; text-align: center;">
                            <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">{{ $this->stats['available_seats'] }}</div>
                            <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Available</div>
                        </div>
                        <div style="background: rgba(59, 130, 246, 0.9); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 75px; text-align: center;">
                            <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">{{ $this->stats['registered'] }}</div>
                            <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Registered</div>
                        </div>
                        <div style="background: rgba(16, 185, 129, 0.9); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 75px; text-align: center;">
                            <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">{{ $this->stats['checked_in'] }}</div>
                            <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Checked In</div>
                        </div>
                        <div style="background: rgba(245, 158, 11, 0.9); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 75px; text-align: center;">
                            <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">{{ $this->stats['waiting_list'] }}</div>
                            <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Waiting</div>
                        </div>
                        <div style="background: rgba(236, 72, 153, 0.9); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 75px; text-align: center;">
                            <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">{{ $this->stats['fill_percentage'] }}%</div>
                            <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Fill Rate</div>
                        </div>
                        <div style="background: rgba(251, 191, 36, 0.9); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 95px; text-align: center;">
                            <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">₾{{ number_format($this->tournament->guaranteed_prize ?? 0) }}</div>
                            <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Guaranteed</div>
                        </div>
                    </div>
                    
                    <!-- Right: Prize Pool, Countdown & Fullscreen -->
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        @php
                            // Use actual prize pool or calculate dynamically
                            $prizePool = $this->tournament->actual_prize_pool;
                            if ($prizePool == 0 || $prizePool == null) {
                                // Calculate based on registered players * buy-in
                                $registeredCount = $this->stats['registered'] ?? 0;
                                $buyIn = $this->tournament->buy_in ?? 0;
                                $prizePool = $registeredCount * $buyIn;
                            }
                        @endphp
                        <div style="background: rgba(255,255,255,0.2); padding: 0.375rem 0.75rem; border-radius: 6px; text-align: center; min-width: 80px; backdrop-filter: blur(10px);">
                            <div style="font-size: 0.625rem; opacity: 0.9; margin-bottom: 0.0625rem;">Prize Pool</div>
                            <div style="font-size: 1rem; font-weight: 700; line-height: 1;">₾{{ number_format($prizePool) }}</div>
                        </div>
                        <div id="countdown-card" style="background: rgba(255,255,255,0.2); padding: 0.375rem 0.75rem; border-radius: 6px; text-align: center; min-width: 100px; backdrop-filter: blur(10px);">
                            <div style="font-size: 0.625rem; opacity: 0.9; margin-bottom: 0.0625rem;">Starts In</div>
                            <div id="countdown" style="font-size: 0.875rem; font-weight: 700; line-height: 1;">--:--:--</div>
                        </div>
                        <button onclick="toggleFullscreen()" style="background: rgba(255, 255, 255, 0.2); color: white; border: none; padding: 0.375rem 0.75rem; border-radius: 6px; cursor: pointer; font-size: 1.25rem; backdrop-filter: blur(10px); transition: all 0.2s; height: 100%;" onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'; this.style.transform='scale(1.05)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'; this.style.transform='scale(1)'">
                            <span id="fullscreen-icon">⛶</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tables & Waiting List Container -->
            <div style="flex: 1; display: flex; gap: 1rem; padding: 1rem; min-height: 0;">
                <!-- Tables Section -->
                <div style="flex: 1; display: flex; flex-direction: column; min-height: 0;">
                    @php
                        $tableCount = count($this->tables);
                        $maxCols = min($tableCount, 3);
                    @endphp
                <style>
                    #tables-grid {
                        display: grid;
                        gap: 1rem;
                        height: 100%;
                        align-content: start;
                    }
                    
                    /* Default: responsive grid */
                    @media (max-width: 1199px) {
                        #tables-grid {
                            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
                        }
                    }
                    
                    /* Large screens and fullscreen: max 3 columns */
                    @media (min-width: 1200px) {
                        #tables-grid {
                            grid-template-columns: repeat({{ $maxCols }}, 1fr);
                        }
                    }
                    
                    .table-card {
                        display: flex;
                        flex-direction: column;
                        min-height: 0;
                    }
                    
                    .seats-grid {
                        display: grid;
                        grid-template-columns: repeat(3, 1fr);
                        gap: 0.75rem;
                        height: 100%;
                    }
                    
                    .seat-item {
                        min-height: 0;
                        display: flex;
                    }
                    
                    /* Drag and drop styles */
                    .draggable-seat {
                        transition: all 0.2s ease;
                    }
                    
                    .draggable-seat:hover {
                        transform: scale(1.02);
                        box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
                    }
                    
                    .draggable-seat:active {
                        cursor: grabbing !important;
                    }
                    
                    .empty-seat-drop-zone {
                        transition: all 0.2s ease;
                    }
                    
                    .seat-item.drag-over {
                        transform: scale(1.05);
                    }
                    
                    /* Player menu styles */
                    .player-menu-container {
                        z-index: 10;
                    }
                    
                    .player-menu {
                        animation: slideDown 0.15s ease-out;
                    }
                    
                    @keyframes slideDown {
                        from {
                            opacity: 0;
                            transform: translateY(-10px);
                        }
                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }
                    
                    /* Waiting list scrollbar */
                    .waiting-list-scroll::-webkit-scrollbar {
                        width: 6px;
                    }
                    
                    .waiting-list-scroll::-webkit-scrollbar-track {
                        background: #f3f4f6;
                        border-radius: 3px;
                    }
                    
                    .waiting-list-scroll::-webkit-scrollbar-thumb {
                        background: #d1d5db;
                        border-radius: 3px;
                    }
                    
                    .waiting-list-scroll::-webkit-scrollbar-thumb:hover {
                        background: #9ca3af;
                    }
                    
                    /* Waiting player draggable styles */
                    .waiting-player-draggable {
                        transition: all 0.2s ease;
                    }
                    
                    .waiting-player-draggable:hover {
                        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
                    }
                    
                    .waiting-player-draggable:active {
                        cursor: grabbing !important;
                    }
                </style>
                <div id="tables-grid">
                    @foreach($this->tables as $table)
                        <div class="table-card" style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb;">
                            
                            <!-- Table Header -->
                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 0.875rem 1rem; color: white;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700;">
                                            {{ $table['table_number'] }}
                                        </div>
                                        <div>
                                            <div style="font-size: 1rem; font-weight: 700; line-height: 1.2;">Table {{ $table['table_number'] }}</div>
                                            <div style="font-size: 0.75rem; opacity: 0.9; margin-top: 0.125rem;">{{ collect($table['seats'])->where('checked_in', true)->count() }}/{{ $table['total_seats'] }} Active</div>
                                        </div>
                                    </div>
                                    <div style="background: @if($table['occupied_count'] >= $table['total_seats']) #ef4444 @elseif($table['occupied_count'] >= $table['total_seats'] * 0.8) #f59e0b @else #10b981 @endif; padding: 0.5rem 0.875rem; border-radius: 6px; font-weight: 700; font-size: 1rem; line-height: 1;">
                                        {{ $table['occupied_count'] }}/{{ $table['total_seats'] }}
                                    </div>
                                </div>
                            </div>

                            <!-- Poker Table -->
                            <div style="padding: 1rem; background: #fafafa; flex: 1; display: flex; flex-direction: column; min-height: 0;">
                                <div style="background: linear-gradient(135deg, #059669 0%, #065f46 100%); border-radius: 16px; padding: 1rem; box-shadow: inset 0 2px 8px rgba(0,0,0,0.2); flex: 1; display: flex; flex-direction: column;">
                                    
                                    <!-- Seats Grid -->
                                    <div class="seats-grid">
                                        @foreach($table['seats'] as $seat)
                                            <div class="seat-item 
                                                @if($seat['occupied']) seat-drop-zone @else empty-seat-drop-zone @endif"
                                                data-table="{{ $table['table_number'] }}"
                                                data-seat="{{ $seat['seat_number'] }}"
                                                ondragover="handleDragOver(event)"
                                                ondragleave="handleDragLeave(event)"
                                                ondrop="handleDrop(event)">
                                                @if($seat['occupied'])
                                                    <!-- Occupied Seat -->
                                                    @php
                                                        // Default: Registered (blue - matching stats)
                                                        $seatGradient = 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)';
                                                        $badgeBg = '#1d4ed8';
                                                        $statusText = 'Registered';
                                                        
                                                        if ($seat['checked_in']) {
                                                            // Checked In: Green (matching stats)
                                                            $seatGradient = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                                                            $badgeBg = '#047857';
                                                            $statusText = '✓ Checked In';
                                                        } elseif (isset($seat['status']) && $seat['status'] === 'cancelled') {
                                                            // Cancelled: Red
                                                            $seatGradient = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
                                                            $badgeBg = '#b91c1c';
                                                            $statusText = '✕ Cancelled';
                                                        }
                                                    @endphp
                                                    <div class="draggable-seat" 
                                                        draggable="true"
                                                        data-registration-id="{{ $seat['registration_id'] ?? '' }}"
                                                        data-player-name="{{ $seat['player_name'] ?? 'Unknown' }}"
                                                        data-table="{{ $table['table_number'] }}"
                                                        data-seat="{{ $seat['seat_number'] }}"
                                                        ondragstart="handleDragStart(event)"
                                                        ondragend="handleDragEnd(event)"
                                                        style="background: {{ $seatGradient }}; padding: 0.5rem; border-radius: 12px; text-align: center; width: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.2); position: relative; cursor: move;">
                                                        
                                                        <!-- 3-Dot Menu -->
                                                        <div class="player-menu-container" style="position: absolute; top: 0.375rem; right: 0.375rem;">
                                                            <button onclick="togglePlayerMenu(event, '{{ $seat['registration_id'] }}')"
                                                                onmousedown="event.stopPropagation()"
                                                                draggable="false"
                                                                style="background: rgba(0,0,0,0.3); color: white; border: none; width: 24px; height: 24px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1rem; font-weight: 700; padding: 0; transition: all 0.2s;"
                                                                onmouseover="this.style.background='rgba(0,0,0,0.5)'; this.style.transform='scale(1.1)'" 
                                                                onmouseout="this.style.background='rgba(0,0,0,0.3)'; this.style.transform='scale(1)'">
                                                                ⋮
                                                            </button>
                                                            <div id="menu-{{ $seat['registration_id'] }}" class="player-menu" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 0.25rem; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); min-width: 150px; z-index: 1000; overflow: hidden;">
                                                                <button onclick="updatePlayerStatus('{{ $seat['registration_id'] }}', 'registered')" 
                                                                    style="width: 100%; padding: 0.5rem 0.75rem; border: none; background: white; color: #1f2937; text-align: left; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; display: flex; align-items: center; gap: 0.5rem;"
                                                                    onmouseover="this.style.background='#f3f4f6'" 
                                                                    onmouseout="this.style.background='white'">
                                                                    <span style="font-size: 1rem; color: #3b82f6;">●</span>
                                                                    <span>Set Registered</span>
                                                                </button>
                                                                <button onclick="updatePlayerStatus('{{ $seat['registration_id'] }}', 'checked_in')" 
                                                                    style="width: 100%; padding: 0.5rem 0.75rem; border: none; background: white; color: #1f2937; text-align: left; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; display: flex; align-items: center; gap: 0.5rem;"
                                                                    onmouseover="this.style.background='#f3f4f6'" 
                                                                    onmouseout="this.style.background='white'">
                                                                    <span style="font-size: 1rem; color: #10b981;">✓</span>
                                                                    <span>Check In</span>
                                                                </button>
                                                                <button onclick="updatePlayerStatus('{{ $seat['registration_id'] }}', 'waiting', '{{ $seat['player_name'] }}', '{{ $table['table_number'] }}', '{{ $seat['seat_number'] }}')" 
                                                                    style="width: 100%; padding: 0.5rem 0.75rem; border: none; background: white; color: #1f2937; text-align: left; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; display: flex; align-items: center; gap: 0.5rem; border-top: 1px solid #e5e7eb;"
                                                                    onmouseover="this.style.background='#fffbeb'" 
                                                                    onmouseout="this.style.background='white'">
                                                                    <span style="font-size: 1rem; color: #f59e0b;">⏱</span>
                                                                    <span>Move to Waiting</span>
                                                                </button>
                                                                <button onclick="updatePlayerStatus('{{ $seat['registration_id'] }}', 'cancelled')" 
                                                                    style="width: 100%; padding: 0.5rem 0.75rem; border: none; background: white; color: #ef4444; text-align: left; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; display: flex; align-items: center; gap: 0.5rem; border-top: 1px solid #e5e7eb;"
                                                                    onmouseover="this.style.background='#fef2f2'" 
                                                                    onmouseout="this.style.background='white'">
                                                                    <span style="font-size: 1rem;">✕</span>
                                                                    <span>Cancel</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        
                                                        <div style="width: 48px; height: 48px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #1f2937; font-size: 1.5rem; margin-bottom: 0.5rem;">
                                                            {{ $seat['seat_number'] }}
                                                        </div>
                                                        <div style="color: white; font-size: 0.875rem; font-weight: 600; line-height: 1.2; margin-bottom: 0.375rem; width: 100%; word-wrap: break-word; overflow-wrap: break-word; hyphens: auto;">
                                                            {{ $seat['player_name'] ?? 'Unknown' }}
                                                        </div>
                                                        <div style="background: {{ $badgeBg }}; color: white; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 700;">
                                                            {{ $statusText }}
                                                        </div>
                                                    </div>
                                                @else
                                                    <!-- Empty Seat -->
                                                    <div style="background: rgba(0,0,0,0.08); border: 2px dashed rgba(255,255,255,0.3); border-radius: 12px; width: 100%; padding: 1rem; display: flex; flex-direction: column; align-items: center; justify-content: center; color: rgba(255,255,255,0.5); transition: all 0.2s;">
                                                        <div style="font-size: 2rem; line-height: 1;">+</div>
                                                        <div style="font-size: 0.75rem; margin-top: 0.25rem; font-weight: 600;">Seat {{ $seat['seat_number'] }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Table Footer -->
                            <div style="background: white; padding: 0.75rem 1rem; border-top: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; font-size: 0.875rem;">
                                <div style="display: flex; gap: 1rem;">
                                    <div style="display: flex; align-items: center; gap: 0.375rem;">
                                        <div style="width: 10px; height: 10px; background: #06b6d4; border-radius: 50%;"></div>
                                        <span style="color: #6b7280;"><strong style="color: #1f2937;">{{ collect($table['seats'])->where('checked_in', true)->count() }}</strong> Active</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.375rem;">
                                        <div style="width: 10px; height: 10px; background: #6366f1; border-radius: 50%;"></div>
                                        <span style="color: #6b7280;"><strong style="color: #1f2937;">{{ collect($table['seats'])->where('occupied', true)->where('checked_in', false)->count() }}</strong> Waiting</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.375rem;">
                                        <div style="width: 10px; height: 10px; background: #d1d5db; border-radius: 50%;"></div>
                                        <span style="color: #6b7280;"><strong style="color: #1f2937;">{{ collect($table['seats'])->where('occupied', false)->count() }}</strong> Empty</span>
                                    </div>
                                </div>
                                <div style="color: #1f2937; font-weight: 700; font-size: 1rem;">
                                    {{ round(($table['occupied_count'] / $table['total_seats']) * 100) }}%
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                </div>
                <!-- End Tables Section -->
                
                <!-- Waiting List Column -->
                <div style="width: 280px; display: flex; flex-direction: column; min-height: 0;">
                    <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; display: flex; flex-direction: column; height: 100%;">
                        <!-- Header -->
                        <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 1rem; color: white;">
                            <h3 style="font-size: 1rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                                <span style="font-size: 1.25rem;">⏱</span>
                                <span>Waiting List</span>
                                <span style="background: rgba(255,255,255,0.3); padding: 0.125rem 0.5rem; border-radius: 12px; font-size: 0.75rem; margin-left: auto;">{{ count($this->waitingList) }}</span>
                            </h3>
                        </div>
                        
                        <!-- Waiting List Content -->
                        <div class="waiting-list-scroll" style="flex: 1; overflow-y: auto; padding: 0.75rem;">
                            @if(count($this->waitingList) > 0)
                                @foreach($this->waitingList as $index => $player)
                                    <div class="waiting-player-draggable"
                                        draggable="true"
                                        data-registration-id="{{ $player['id'] }}"
                                        data-player-name="{{ $player['name'] }}"
                                        data-from-waiting="true"
                                        ondragstart="handleWaitingDragStart(event)"
                                        ondragend="handleDragEnd(event)"
                                        style="background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; padding: 0.75rem; margin-bottom: 0.5rem; transition: all 0.2s; cursor: move;" 
                                        onmouseover="this.style.background='#fde68a'; this.style.transform='translateX(2px)'" 
                                        onmouseout="this.style.background='#fef3c7'; this.style.transform='translateX(0)'">
                                        <div style="display: flex; align-items: start; gap: 0.75rem;">
                                            <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.875rem; flex-shrink: 0;">
                                                {{ $player['position'] }}
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <div style="font-weight: 600; font-size: 0.875rem; color: #1f2937; margin-bottom: 0.25rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    {{ $player['name'] }}
                                                </div>
                                                <div style="font-size: 0.75rem; color: #6b7280; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    {{ $player['email'] }}
                                                </div>
                                                <div style="font-size: 0.625rem; color: #9ca3af; margin-top: 0.25rem;">
                                                    {{ $player['joined_at'] }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div style="text-align: center; padding: 2rem; color: #9ca3af;">
                                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">📋</div>
                                    <p style="font-size: 0.875rem; margin: 0;">No players waiting</p>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Footer -->
                        <div style="background: #fafafa; padding: 0.75rem; border-top: 1px solid #e5e7eb; text-align: center;">
                            <div style="font-size: 0.75rem; color: #6b7280;">
                                <strong style="color: #1f2937;">{{ count($this->waitingList) }}</strong> in waiting list
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Waiting List Column -->
            </div>
        </div>

        <script>
        // Countdown Timer
        const tournamentStartTime = new Date('{{ $this->tournament->start_date->format('Y-m-d H:i:s') }}').getTime();
        
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = tournamentStartTime - now;
            
            const countdownElement = document.getElementById('countdown');
            const countdownCard = document.getElementById('countdown-card');
            
            if (distance < 0) {
                countdownElement.textContent = 'Started';
                countdownCard.querySelector('div:first-child').textContent = 'Status';
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            if (days > 0) {
                countdownElement.textContent = days + 'd ' + hours + 'h ' + minutes + 'm';
            } else if (hours > 0) {
                countdownElement.textContent = hours + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            } else {
                countdownElement.textContent = minutes + ':' + String(seconds).padStart(2, '0');
            }
        }
        
        // Update countdown every second
        updateCountdown();
        setInterval(updateCountdown, 1000);
        
        // Fullscreen functionality
        function toggleFullscreen() {
            const elem = document.getElementById('tournament-dashboard');
            const icon = document.getElementById('fullscreen-icon');
            
            if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.mozFullScreenElement && !document.msFullscreenElement) {
                // Enter fullscreen
                if (elem.requestFullscreen) {
                    elem.requestFullscreen();
                } else if (elem.webkitRequestFullscreen) {
                    elem.webkitRequestFullscreen();
                } else if (elem.mozRequestFullScreen) {
                    elem.mozRequestFullScreen();
                } else if (elem.msRequestFullscreen) {
                    elem.msRequestFullscreen();
                }
                icon.textContent = '⛶';
            } else {
                // Exit fullscreen
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
                icon.textContent = '⛶';
            }
        }
        
        // Listen for fullscreen change events
        document.addEventListener('fullscreenchange', updateFullscreenIcon);
        document.addEventListener('webkitfullscreenchange', updateFullscreenIcon);
        document.addEventListener('mozfullscreenchange', updateFullscreenIcon);
        document.addEventListener('MSFullscreenChange', updateFullscreenIcon);
        
        function updateFullscreenIcon() {
            const icon = document.getElementById('fullscreen-icon');
            if (document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement) {
                icon.textContent = '⛶';
            } else {
                icon.textContent = '⛶';
            }
        }
        
        // Drag and Drop functionality
        let draggedElement = null;
        let draggedData = null;
        
        function handleDragStart(event) {
            draggedElement = event.target;
            draggedData = {
                registrationId: event.target.dataset.registrationId,
                playerName: event.target.dataset.playerName,
                fromTable: event.target.dataset.table,
                fromSeat: event.target.dataset.seat,
                fromWaiting: false
            };
            
            event.target.style.opacity = '0.5';
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/html', event.target.innerHTML);
        }
        
        function handleWaitingDragStart(event) {
            draggedElement = event.target;
            draggedData = {
                registrationId: event.target.dataset.registrationId,
                playerName: event.target.dataset.playerName,
                fromWaiting: true
            };
            
            event.target.style.opacity = '0.5';
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/html', event.target.innerHTML);
        }
        
        function handleDragEnd(event) {
            event.target.style.opacity = '1';
            
            // Remove all drop zone highlights
            document.querySelectorAll('.seat-item').forEach(item => {
                item.style.background = '';
                item.style.transform = '';
            });
        }
        
        function handleDragOver(event) {
            if (event.preventDefault) {
                event.preventDefault();
            }
            
            event.dataTransfer.dropEffect = 'move';
            
            // Highlight the drop zone
            const dropZone = event.currentTarget;
            if (dropZone.classList.contains('empty-seat-drop-zone')) {
                dropZone.querySelector('div').style.background = 'rgba(59, 130, 246, 0.3)';
                dropZone.querySelector('div').style.borderColor = '#3b82f6';
                dropZone.querySelector('div').style.transform = 'scale(1.05)';
            }
            
            return false;
        }
        
        function handleDragLeave(event) {
            const dropZone = event.currentTarget;
            if (dropZone.classList.contains('empty-seat-drop-zone')) {
                dropZone.querySelector('div').style.background = 'rgba(0,0,0,0.08)';
                dropZone.querySelector('div').style.borderColor = 'rgba(255,255,255,0.3)';
                dropZone.querySelector('div').style.transform = 'scale(1)';
            }
        }
        
        async function handleDrop(event) {
            if (event.stopPropagation) {
                event.stopPropagation();
            }
            
            const dropZone = event.currentTarget;
            const toTable = dropZone.dataset.table;
            const toSeat = dropZone.dataset.seat;
            
            // Only allow dropping on empty seats
            if (!dropZone.classList.contains('empty-seat-drop-zone')) {
                showErrorModal('Cannot drop on an occupied seat');
                return false;
            }
            
            if (!draggedData || !draggedData.registrationId) {
                showErrorModal('Invalid drag data');
                return false;
            }
            
            // Check if trying to drop on the same seat (only for seat-to-seat moves)
            if (!draggedData.fromWaiting && draggedData.fromTable === toTable && draggedData.fromSeat === toSeat) {
                return false;
            }
            
            // Show loading indicator
            dropZone.style.opacity = '0.5';
            
            try {
                // Determine which endpoint to use
                const endpoint = draggedData.fromWaiting 
                    ? `{{ url('') }}/api/registration/${draggedData.registrationId}/move-from-waiting`
                    : `{{ url('') }}/api/registration/${draggedData.registrationId}/seat`;
                
                const actionText = draggedData.fromWaiting 
                    ? 'Moving player from waiting list...'
                    : 'Updating seat assignment...';
                
                // Show loading modal
                showLoadingModal(actionText);
                
                // Make API call
                const response = await fetch(endpoint, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        table_number: parseInt(toTable),
                        seat_number: parseInt(toSeat)
                    })
                });
                
                const data = await response.json();
                
                hideModal();
                dropZone.style.opacity = '1';
                
                if (response.ok && data.success) {
                    // Update UI without page reload
                    if (draggedData.fromWaiting) {
                        // Remove from waiting list
                        draggedElement.remove();
                        updateWaitingListCount(-1);
                        
                        // Add to seat
                        updateSeatToOccupied(toTable, toSeat, draggedData.registrationId, draggedData.playerName, 'registered');
                        
                        // Update stats
                        updateStats('registered', 1);
                        updateStats('waiting', -1);
                        updateStats('available', -1);
                    } else {
                        // Move from old seat to new seat
                        updateSeatToEmpty(draggedData.fromTable, draggedData.fromSeat);
                        updateSeatToOccupied(toTable, toSeat, draggedData.registrationId, draggedData.playerName, 'registered');
                    }
                    
                    const successMsg = draggedData.fromWaiting 
                        ? `${draggedData.playerName} moved to Table ${toTable}, Seat ${toSeat}!`
                        : 'Seat assignment updated successfully!';
                    showSuccessToast(successMsg);
                } else {
                    showErrorModal(data.message || 'Failed to complete action');
                }
            } catch (error) {
                console.error('Error:', error);
                hideModal();
                dropZone.style.opacity = '1';
                showErrorModal('An error occurred while completing the action');
            }
            
            return false;
        }
        
        // Player Menu functionality
        function togglePlayerMenu(event, registrationId) {
            event.preventDefault();
            event.stopPropagation();
            
            // Prevent dragging when clicking menu
            const draggableSeat = event.target.closest('.draggable-seat');
            if (draggableSeat) {
                draggableSeat.setAttribute('draggable', 'false');
                setTimeout(() => {
                    draggableSeat.setAttribute('draggable', 'true');
                }, 100);
            }
            
            const menu = document.getElementById(`menu-${registrationId}`);
            const allMenus = document.querySelectorAll('.player-menu');
            
            // Close all other menus
            allMenus.forEach(m => {
                if (m !== menu) {
                    m.style.display = 'none';
                }
            });
            
            // Toggle current menu
            if (menu.style.display === 'none' || menu.style.display === '') {
                menu.style.display = 'block';
            } else {
                menu.style.display = 'none';
            }
        }
        
        // Close menus when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.player-menu-container')) {
                document.querySelectorAll('.player-menu').forEach(menu => {
                    menu.style.display = 'none';
                });
            }
        });
        
        // Update player status
        async function updatePlayerStatus(registrationId, newStatus, playerName = null, tableNumber = null, seatNumber = null) {
            // Close the menu
            document.getElementById(`menu-${registrationId}`).style.display = 'none';
            
            const statusInfo = {
                'registered': {
                    name: 'Registered',
                    icon: '●',
                    color: '#3b82f6',
                    bgColor: '#eff6ff'
                },
                'checked_in': {
                    name: 'Checked In',
                    icon: '✓',
                    color: '#10b981',
                    bgColor: '#ecfdf5'
                },
                'waiting': {
                    name: 'Waiting',
                    icon: '⏱',
                    color: '#f59e0b',
                    bgColor: '#fffbeb'
                },
                'cancelled': {
                    name: 'Cancelled',
                    icon: '✕',
                    color: '#ef4444',
                    bgColor: '#fef2f2'
                }
            };
            
            const status = statusInfo[newStatus];
            
            // Show custom confirmation modal
            showConfirmModal(
                'Change Player Status',
                `Are you sure you want to change this player's status to <strong style="color: ${status.color}">${status.icon} ${status.name}</strong>?`,
                status.color,
                async () => {
                    // Show loading modal
                    showLoadingModal('Updating status...');
                    
                    try {
                        const response = await fetch(`{{ url('') }}/api/registration/${registrationId}/status`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                            },
                            body: JSON.stringify({
                                status: newStatus
                            })
                        });
                        
                        const data = await response.json();
                        
                        hideModal();
                        
                        if (response.ok && data.success) {
                            // Handle waiting status specially - move to waiting list
                            if (newStatus === 'waiting') {
                                // Remove from seat
                                if (tableNumber && seatNumber) {
                                    updateSeatToEmpty(tableNumber, seatNumber);
                                }
                                
                                // Add to waiting list
                                if (playerName) {
                                    addToWaitingList(registrationId, playerName, data.registration.waiting_position);
                                }
                                
                                // Update stats
                                updateStats(data.registration.old_status, -1);
                                updateStats('waiting', 1);
                                updateStats('available', 1);
                                
                                showSuccessToast('Player moved to waiting list!');
                            } else {
                                // Update UI without page reload for other statuses
                                updatePlayerStatusUI(registrationId, newStatus, data.registration.old_status);
                                showSuccessToast('Status updated successfully!');
                            }
                        } else {
                            showErrorModal(data.message || 'Failed to update status');
                        }
                    } catch (error) {
                        console.error('Error updating status:', error);
                        hideModal();
                        showErrorModal('An error occurred while updating the status');
                    }
                }
            );
        }
        
        // Helper function to get the correct container (fullscreen element or body)
        function getModalContainer() {
            const fullscreenElement = document.fullscreenElement || 
                                     document.webkitFullscreenElement || 
                                     document.mozFullScreenElement || 
                                     document.msFullscreenElement;
            return fullscreenElement || document.body;
        }
        
        // Modal functions
        function showConfirmModal(title, message, accentColor, onConfirm) {
            const modal = document.createElement('div');
            modal.id = 'custom-modal';
            modal.innerHTML = `
                <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 2147483647; animation: fadeIn 0.2s;">
                    <div style="background: white; border-radius: 16px; padding: 2rem; max-width: 400px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); animation: slideUp 0.3s;">
                        <div style="text-align: center; margin-bottom: 1.5rem;">
                            <div style="width: 64px; height: 64px; margin: 0 auto 1rem; background: ${accentColor}; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; font-weight: 700;">?</div>
                            <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin: 0 0 0.5rem 0;">${title}</h3>
                            <p style="color: #6b7280; font-size: 0.875rem; margin: 0;">${message}</p>
                        </div>
                        <div style="display: flex; gap: 0.75rem;">
                            <button onclick="hideModal()" style="flex: 1; padding: 0.75rem 1.5rem; border: 1px solid #e5e7eb; background: white; color: #374151; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">
                                Cancel
                            </button>
                            <button id="confirm-btn" style="flex: 1; padding: 0.75rem 1.5rem; border: none; background: ${accentColor}; color: white; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" onmouseover="this.style.opacity='0.9'; this.style.transform='translateY(-1px)'" onmouseout="this.style.opacity='1'; this.style.transform='translateY(0)'">
                                Confirm
                            </button>
                        </div>
                    </div>
                </div>
            `;
            getModalContainer().appendChild(modal);
            document.getElementById('confirm-btn').onclick = () => {
                hideModal();
                onConfirm();
            };
        }
        
        function showLoadingModal(message) {
            const modal = document.createElement('div');
            modal.id = 'custom-modal';
            modal.innerHTML = `
                <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 2147483647; animation: fadeIn 0.2s;">
                    <div style="background: white; border-radius: 16px; padding: 2rem; max-width: 300px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); text-align: center; animation: slideUp 0.3s;">
                        <div style="width: 48px; height: 48px; margin: 0 auto 1rem; border: 4px solid #e5e7eb; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                        <p style="color: #6b7280; font-size: 0.875rem; margin: 0;">${message}</p>
                    </div>
                </div>
            `;
            getModalContainer().appendChild(modal);
        }
        
        function showSuccessModal(message, onClose) {
            const modal = document.createElement('div');
            modal.id = 'custom-modal';
            modal.innerHTML = `
                <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 2147483647; animation: fadeIn 0.2s;">
                    <div style="background: white; border-radius: 16px; padding: 2rem; max-width: 300px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); text-align: center; animation: slideUp 0.3s;">
                        <div style="width: 64px; height: 64px; margin: 0 auto 1rem; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">✓</div>
                        <p style="color: #1f2937; font-weight: 600; font-size: 1rem; margin: 0;">${message}</p>
                    </div>
                </div>
            `;
            getModalContainer().appendChild(modal);
            setTimeout(() => {
                hideModal();
                if (onClose) onClose();
            }, 1500);
        }
        
        function showErrorModal(message) {
            const modal = document.createElement('div');
            modal.id = 'custom-modal';
            modal.innerHTML = `
                <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 2147483647; animation: fadeIn 0.2s;">
                    <div style="background: white; border-radius: 16px; padding: 2rem; max-width: 400px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); animation: slideUp 0.3s;">
                        <div style="text-align: center; margin-bottom: 1.5rem;">
                            <div style="width: 64px; height: 64px; margin: 0 auto 1rem; background: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">✕</div>
                            <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin: 0 0 0.5rem 0;">Error</h3>
                            <p style="color: #6b7280; font-size: 0.875rem; margin: 0;">${message}</p>
                        </div>
                        <button onclick="hideModal()" style="width: 100%; padding: 0.75rem 1.5rem; border: none; background: #ef4444; color: white; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                            Close
                        </button>
                    </div>
                </div>
            `;
            getModalContainer().appendChild(modal);
        }
        
        function hideModal() {
            const modal = document.getElementById('custom-modal');
            if (modal) {
                modal.remove();
            }
        }
        
        // Toast notification (non-blocking success message)
        function showSuccessToast(message) {
            const toast = document.createElement('div');
            toast.style.cssText = 'position: fixed; top: 20px; right: 20px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 2147483646; animation: slideInRight 0.3s; display: flex; align-items: center; gap: 0.75rem; font-weight: 600;';
            toast.innerHTML = `
                <span style="font-size: 1.25rem;">✓</span>
                <span>${message}</span>
            `;
            getModalContainer().appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOutRight 0.3s';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        // Update seat to empty
        function updateSeatToEmpty(tableNumber, seatNumber) {
            const seatItem = findSeatElement(tableNumber, seatNumber);
            if (!seatItem) return;
            
            // Remove seat-drop-zone class and add empty-seat-drop-zone
            seatItem.classList.remove('seat-drop-zone');
            seatItem.classList.add('empty-seat-drop-zone');
            
            // Replace content with empty seat
            seatItem.innerHTML = `
                <div style="background: rgba(0,0,0,0.08); border: 2px dashed rgba(255,255,255,0.3); border-radius: 12px; width: 100%; padding: 1rem; display: flex; flex-direction: column; align-items: center; justify-content: center; color: rgba(255,255,255,0.5); transition: all 0.2s;">
                    <div style="font-size: 2rem; line-height: 1;">+</div>
                    <div style="font-size: 0.75rem; margin-top: 0.25rem; font-weight: 600;">Seat ${seatNumber}</div>
                </div>
            `;
            
            // Update table footer counts
            updateTableFooter(tableNumber);
        }
        
        // Update seat to occupied
        function updateSeatToOccupied(tableNumber, seatNumber, registrationId, playerName, status) {
            const seatItem = findSeatElement(tableNumber, seatNumber);
            if (!seatItem) return;
            
            // Remove empty-seat-drop-zone class and add seat-drop-zone
            seatItem.classList.remove('empty-seat-drop-zone');
            seatItem.classList.add('seat-drop-zone');
            
            // Determine colors based on status
            let seatGradient, badgeBg, statusText, statusIcon;
            if (status === 'checked_in') {
                seatGradient = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                badgeBg = '#047857';
                statusText = 'Checked In';
                statusIcon = '✓';
            } else if (status === 'cancelled') {
                seatGradient = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
                badgeBg = '#b91c1c';
                statusText = 'Cancelled';
                statusIcon = '✕';
            } else {
                seatGradient = 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)';
                badgeBg = '#1d4ed8';
                statusText = 'Registered';
                statusIcon = '';
            }
            
            // Create occupied seat HTML
            seatItem.innerHTML = `
                <div class="draggable-seat" 
                    draggable="true"
                    data-registration-id="${registrationId}"
                    data-player-name="${playerName}"
                    data-table="${tableNumber}"
                    data-seat="${seatNumber}"
                    ondragstart="handleDragStart(event)"
                    ondragend="handleDragEnd(event)"
                    style="background: ${seatGradient}; padding: 0.5rem; border-radius: 12px; text-align: center; width: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.2); position: relative; cursor: move;">
                    
                    <div class="player-menu-container" style="position: absolute; top: 0.375rem; right: 0.375rem;">
                        <button onclick="togglePlayerMenu(event, '${registrationId}')"
                            onmousedown="event.stopPropagation()"
                            draggable="false"
                            style="background: rgba(0,0,0,0.3); color: white; border: none; width: 24px; height: 24px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1rem; font-weight: 700; padding: 0; transition: all 0.2s;"
                            onmouseover="this.style.background='rgba(0,0,0,0.5)'; this.style.transform='scale(1.1)'" 
                            onmouseout="this.style.background='rgba(0,0,0,0.3)'; this.style.transform='scale(1)'">
                            ⋮
                        </button>
                        <div id="menu-${registrationId}" class="player-menu" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 0.25rem; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); min-width: 150px; z-index: 1000; overflow: hidden;">
                            <button onclick="updatePlayerStatus('${registrationId}', 'registered')" 
                                style="width: 100%; padding: 0.5rem 0.75rem; border: none; background: white; color: #1f2937; text-align: left; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; display: flex; align-items: center; gap: 0.5rem;"
                                onmouseover="this.style.background='#f3f4f6'" 
                                onmouseout="this.style.background='white'">
                                <span style="font-size: 1rem; color: #3b82f6;">●</span>
                                <span>Set Registered</span>
                            </button>
                            <button onclick="updatePlayerStatus('${registrationId}', 'checked_in')" 
                                style="width: 100%; padding: 0.5rem 0.75rem; border: none; background: white; color: #1f2937; text-align: left; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; display: flex; align-items: center; gap: 0.5rem;"
                                onmouseover="this.style.background='#f3f4f6'" 
                                onmouseout="this.style.background='white'">
                                <span style="font-size: 1rem; color: #10b981;">✓</span>
                                <span>Check In</span>
                            </button>
                            <button onclick="updatePlayerStatus('${registrationId}', 'waiting', '${playerName}', '${tableNumber}', '${seatNumber}')" 
                                style="width: 100%; padding: 0.5rem 0.75rem; border: none; background: white; color: #1f2937; text-align: left; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; display: flex; align-items: center; gap: 0.5rem; border-top: 1px solid #e5e7eb;"
                                onmouseover="this.style.background='#fffbeb'" 
                                onmouseout="this.style.background='white'">
                                <span style="font-size: 1rem; color: #f59e0b;">⏱</span>
                                <span>Move to Waiting</span>
                            </button>
                            <button onclick="updatePlayerStatus('${registrationId}', 'cancelled')" 
                                style="width: 100%; padding: 0.5rem 0.75rem; border: none; background: white; color: #ef4444; text-align: left; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; display: flex; align-items: center; gap: 0.5rem; border-top: 1px solid #e5e7eb;"
                                onmouseover="this.style.background='#fef2f2'" 
                                onmouseout="this.style.background='white'">
                                <span style="font-size: 1rem;">✕</span>
                                <span>Cancel</span>
                            </button>
                        </div>
                    </div>
                    
                    <div style="width: 48px; height: 48px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #1f2937; font-size: 1.5rem; margin-bottom: 0.5rem;">
                        ${seatNumber}
                    </div>
                    <div style="color: white; font-size: 0.875rem; font-weight: 600; line-height: 1.2; margin-bottom: 0.375rem; width: 100%; word-wrap: break-word; overflow-wrap: break-word; hyphens: auto;">
                        ${playerName}
                    </div>
                    <div style="background: ${badgeBg}; color: white; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 700;">
                        ${statusIcon ? statusIcon + ' ' : ''}${statusText}
                    </div>
                </div>
            `;
            
            // Update table footer counts
            updateTableFooter(tableNumber);
        }
        
        // Update player status (change colors and badge) - overloaded function
        function updatePlayerStatusUI(registrationId, newStatus, oldStatus) {
            // Find the seat element by registration ID
            const draggableSeat = document.querySelector(`[data-registration-id="${registrationId}"]`);
            if (!draggableSeat) return;
            
            const seatItem = draggableSeat.closest('.seat-item');
            const tableNumber = draggableSeat.dataset.table;
            const seatNumber = draggableSeat.dataset.seat;
            const playerName = draggableSeat.dataset.playerName;
            
            // Update the seat with new status
            updateSeatToOccupied(tableNumber, seatNumber, registrationId, playerName, newStatus);
            
            // Update stats if status changed
            if (oldStatus && oldStatus !== newStatus) {
                updateStats(oldStatus, -1);
                updateStats(newStatus, 1);
            }
        }
        
        // Find seat element
        function findSeatElement(tableNumber, seatNumber) {
            return document.querySelector(`.seat-item[data-table="${tableNumber}"][data-seat="${seatNumber}"]`);
        }
        
        // Update table footer counts
        function updateTableFooter(tableNumber) {
            // This would update the Active/Waiting/Empty counts in the table footer
            // For now, we'll skip this as it requires counting all seats in the table
        }
        
        // Update stats in header
        function updateStats(type, change) {
            const statsMap = {
                'registered': 2, // Index in stats bar
                'checked_in': 3,
                'waiting': 4,
                'available': 1
            };
            
            if (!statsMap[type]) return;
            
            const statsCards = document.querySelectorAll('div[style*="background: rgba"] > div[style*="font-size: 1.25rem"]');
            if (statsCards[statsMap[type]]) {
                const currentValue = parseInt(statsCards[statsMap[type]].textContent);
                statsCards[statsMap[type]].textContent = currentValue + change;
            }
            
            // Update fill rate
            if (type === 'available' || type === 'registered' || type === 'checked_in') {
                // Calculate new fill rate
                const totalSeats = parseInt(statsCards[0].textContent);
                const available = parseInt(statsCards[1].textContent);
                const occupied = totalSeats - available;
                const fillPercentage = ((occupied / totalSeats) * 100).toFixed(1);
                
                // Update fill rate display
                if (statsCards[5]) {
                    statsCards[5].textContent = fillPercentage + '%';
                }
            }
        }
        
        // Update waiting list count
        function updateWaitingListCount(change) {
            const waitingBadge = document.querySelector('.waiting-list-scroll').previousElementSibling.querySelector('span[style*="background: rgba(255,255,255,0.3)"]');
            const waitingFooter = document.querySelector('.waiting-list-scroll').nextElementSibling.querySelector('strong');
            
            if (waitingBadge) {
                const currentCount = parseInt(waitingBadge.textContent);
                const newCount = Math.max(0, currentCount + change);
                waitingBadge.textContent = newCount;
                if (waitingFooter) {
                    waitingFooter.textContent = newCount;
                }
            }
        }
        
        // Add player to waiting list UI
        function addToWaitingList(registrationId, playerName, position) {
            const waitingListContainer = document.querySelector('.waiting-list-scroll');
            if (!waitingListContainer) return;
            
            // Check if empty state exists and remove it
            const emptyState = waitingListContainer.querySelector('div[style*="text-align: center"]');
            if (emptyState) {
                emptyState.remove();
            }
            
            // Create new waiting list item
            const now = new Date();
            const joinedAt = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ' ' + 
                           now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            
            const waitingItem = document.createElement('div');
            waitingItem.className = 'waiting-player-draggable';
            waitingItem.setAttribute('draggable', 'true');
            waitingItem.setAttribute('data-registration-id', registrationId);
            waitingItem.setAttribute('data-player-name', playerName);
            waitingItem.setAttribute('data-from-waiting', 'true');
            waitingItem.setAttribute('ondragstart', 'handleWaitingDragStart(event)');
            waitingItem.setAttribute('ondragend', 'handleDragEnd(event)');
            waitingItem.style.cssText = 'background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; padding: 0.75rem; margin-bottom: 0.5rem; transition: all 0.2s; cursor: move;';
            waitingItem.setAttribute('onmouseover', "this.style.background='#fde68a'; this.style.transform='translateX(2px)'");
            waitingItem.setAttribute('onmouseout', "this.style.background='#fef3c7'; this.style.transform='translateX(0)'");
            
            waitingItem.innerHTML = `
                <div style="display: flex; align-items: start; gap: 0.75rem;">
                    <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.875rem; flex-shrink: 0;">
                        ${position}
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 600; font-size: 0.875rem; color: #1f2937; margin-bottom: 0.25rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            ${playerName}
                        </div>
                        <div style="font-size: 0.625rem; color: #9ca3af; margin-top: 0.25rem;">
                            ${joinedAt}
                        </div>
                    </div>
                </div>
            `;
            
            // Insert at the beginning or end based on position
            waitingListContainer.insertBefore(waitingItem, waitingListContainer.firstChild);
            
            // Update waiting list count
            updateWaitingListCount(1);
        }
        
        // Add animation styles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
            
            @keyframes fadeIn {
                from {
                    opacity: 0;
                }
                to {
                    opacity: 1;
                }
            }
            
            @keyframes slideUp {
                from {
                    transform: translateY(20px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            
            @keyframes spin {
                from {
                    transform: rotate(0deg);
                }
                to {
                    transform: rotate(360deg);
                }
            }
        `;
        document.head.appendChild(style);
        </script>
    @else
        <div style="text-align: center; padding: 3rem;">
            <p style="color: #6b7280;">No tournament selected</p>
        </div>
    @endif
</x-filament-panels::page>

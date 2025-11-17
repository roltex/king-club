@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;
@endphp

<x-filament-panels::page>
    @if($this->cashGame)
        <div id="cash-game-dashboard" style="background: #f8f9fb; padding: 0; margin: -1.5rem; height: 100vh; overflow: hidden; display: flex; flex-direction: column;">
            
            <!-- Header with Stats in One Line -->
            <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 0.75rem 1rem; color: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); flex-shrink: 0;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                    <!-- Left: Cash Game Info -->
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            💰
                        </div>
                        <div>
                            <h1 style="margin: 0; font-size: 1rem; font-weight: 700; line-height: 1.2;">{{ $this->cashGame->name }}</h1>
                            <div style="margin-top: 0.125rem; font-size: 0.7rem; opacity: 0.9; display: flex; gap: 0.75rem;">
                                <span>🪑 Table {{ $this->cashGame->table_number }}</span>
                                <span>🎲 {{ $this->cashGame->stakes_display }}</span>
                                <span>💰 ₾{{ number_format($this->cashGame->default_buy_in ?? $this->cashGame->min_buy_in) }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Center: Stats -->
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <div style="background: rgba(255,255,255,0.2); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 75px; text-align: center; backdrop-filter: blur(10px);">
                            <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">{{ $this->stats['total_seats'] }}</div>
                            <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Total</div>
                        </div>
                        <div style="background: rgba(6, 182, 212, 0.9); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 75px; text-align: center;">
                            <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">{{ $this->stats['available_seats'] }}</div>
                            <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Available</div>
                        </div>
                        <div style="background: rgba(59, 130, 246, 0.9); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 75px; text-align: center;">
                            <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">{{ $this->stats['seated'] }}</div>
                            <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Seated</div>
                        </div>
                        <div style="background: rgba(16, 185, 129, 0.9); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 75px; text-align: center;">
                            <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">{{ $this->stats['playing'] }}</div>
                            <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Playing</div>
                        </div>
                        <div style="background: rgba(245, 158, 11, 0.9); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 75px; text-align: center;">
                            <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">{{ $this->stats['away'] }}</div>
                            <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Away</div>
                        </div>
                        <div style="background: rgba(245, 158, 11, 0.9); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 75px; text-align: center;">
                            <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">{{ $this->stats['waiting'] ?? 0 }}</div>
                            <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Waiting</div>
                        </div>
                        <div style="background: rgba(236, 72, 153, 0.9); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 75px; text-align: center;">
                            <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">{{ $this->stats['fill_percentage'] }}%</div>
                            <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Fill Rate</div>
                        </div>
                    </div>
                    
                    <!-- Right: Total Pot & Fullscreen -->
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <div style="background: rgba(255,255,255,0.2); padding: 0.375rem 0.75rem; border-radius: 6px; text-align: center; min-width: 100px; backdrop-filter: blur(10px);">
                            <div style="font-size: 0.625rem; opacity: 0.9; margin-bottom: 0.0625rem;">Total Pot</div>
                            <div style="font-size: 1rem; font-weight: 700; line-height: 1;">₾{{ number_format($this->stats['total_pot'] ?? 0) }}</div>
                        </div>
                        <button onclick="toggleFullscreen()" style="background: rgba(255, 255, 255, 0.2); color: white; border: none; padding: 0.375rem 0.75rem; border-radius: 6px; cursor: pointer; font-size: 1.25rem; backdrop-filter: blur(10px); transition: all 0.2s; height: 100%;" onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'; this.style.transform='scale(1.05)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'; this.style.transform='scale(1)'">
                            <span id="fullscreen-icon">⛶</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table & Waiting List Container -->
            <div style="flex: 1; display: flex; gap: 1rem; padding: 1rem; min-height: 0;">
                <!-- Table Section -->
                <div style="flex: 1; display: flex; flex-direction: column; min-height: 0;">
                <style>
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
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; height: 100%; display: flex; flex-direction: column;">
                    
                    <!-- Table Header -->
                    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 0.875rem 1rem; color: white;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700;">
                                    {{ $this->table['table_number'] }}
                                </div>
                                <div>
                                    <div style="font-size: 1rem; font-weight: 700; line-height: 1.2;">Table {{ $this->table['table_number'] }}</div>
                                    <div style="font-size: 0.75rem; opacity: 0.9; margin-top: 0.125rem;">{{ $this->stats['playing'] }}/{{ $this->table['total_seats'] }} Playing</div>
                                </div>
                            </div>
                            <div style="background: @if($this->table['occupied_count'] >= $this->table['total_seats']) #ef4444 @elseif($this->table['occupied_count'] >= $this->table['total_seats'] * 0.8) #f59e0b @else #10b981 @endif; padding: 0.5rem 0.875rem; border-radius: 6px; font-weight: 700; font-size: 1rem; line-height: 1;">
                                {{ $this->table['occupied_count'] }}/{{ $this->table['total_seats'] }}
                            </div>
                        </div>
                    </div>

                    <!-- Poker Table -->
                    <div style="padding: 1rem; background: #fafafa; flex: 1; display: flex; flex-direction: column; min-height: 0;">
                        <div style="background: linear-gradient(135deg, #059669 0%, #065f46 100%); border-radius: 16px; padding: 1rem; box-shadow: inset 0 2px 8px rgba(0,0,0,0.2); flex: 1; display: flex; flex-direction: column;">
                            
                            <!-- Seats Grid -->
                            <div class="seats-grid">
                                @foreach($this->table['seats'] as $seat)
                                    <div class="seat-item 
                                        @if($seat['occupied']) seat-drop-zone @else empty-seat-drop-zone @endif"
                                        data-seat="{{ $seat['seat_number'] }}"
                                        ondragover="handleDragOver(event)"
                                        ondragleave="handleDragLeave(event)"
                                        ondrop="handleDrop(event)">
                                        @if($seat['occupied'])
                                            <!-- Occupied Seat -->
                                            @php
                                                // Default: Seated (blue)
                                                $seatGradient = 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)';
                                                $badgeBg = '#1d4ed8';
                                                $statusText = 'Seated';
                                                
                                                if ($seat['status'] === 'playing') {
                                                    // Playing: Green
                                                    $seatGradient = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                                                    $badgeBg = '#047857';
                                                    $statusText = '✓ Playing';
                                                } elseif ($seat['status'] === 'away') {
                                                    // Away: Yellow
                                                    $seatGradient = 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)';
                                                    $badgeBg = '#b45309';
                                                    $statusText = '⏱ Away';
                                                } elseif ($seat['status'] === 'sitting_out') {
                                                    // Sitting Out: Gray
                                                    $seatGradient = 'linear-gradient(135deg, #6b7280 0%, #4b5563 100%)';
                                                    $badgeBg = '#374151';
                                                    $statusText = '⏸ Sitting Out';
                                                } elseif ($seat['status'] === 'left' || $seat['status'] === 'removed') {
                                                    // Left/Removed: Red
                                                    $seatGradient = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
                                                    $badgeBg = '#b91c1c';
                                                    $statusText = '✕ Left';
                                                }
                                            @endphp
                                            <div class="draggable-seat" 
                                                draggable="true"
                                                data-seat-id="{{ $seat['seat_id'] ?? '' }}"
                                                data-player-name="{{ $seat['player_name'] ?? 'Unknown' }}"
                                                data-seat="{{ $seat['seat_number'] }}"
                                                ondragstart="handleDragStart(event)"
                                                ondragend="handleDragEnd(event)"
                                                style="background: {{ $seatGradient }}; padding: 0.5rem; border-radius: 12px; text-align: center; width: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.2); position: relative; cursor: move;">
                                                
                                                <!-- 3-Dot Menu -->
                                                <div class="player-menu-container" style="position: absolute; top: 0.375rem; right: 0.375rem;">
                                                    <button onclick="togglePlayerMenu(event, '{{ $seat['seat_id'] }}')"
                                                        onmousedown="event.stopPropagation()"
                                                        draggable="false"
                                                        style="background: rgba(0,0,0,0.3); color: white; border: none; width: 24px; height: 24px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1rem; font-weight: 700; padding: 0; transition: all 0.2s;"
                                                        onmouseover="this.style.background='rgba(0,0,0,0.5)'; this.style.transform='scale(1.1)'" 
                                                        onmouseout="this.style.background='rgba(0,0,0,0.3)'; this.style.transform='scale(1)'">
                                                        ⋮
                                                    </button>
                                                    <div id="menu-{{ $seat['seat_id'] }}" class="player-menu" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 0.25rem; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); min-width: 150px; z-index: 1000; overflow: hidden;">
                                                        <button onclick="updatePlayerStatus('{{ $seat['seat_id'] }}', 'seated')" 
                                                            style="width: 100%; padding: 0.5rem 0.75rem; border: none; background: white; color: #1f2937; text-align: left; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; display: flex; align-items: center; gap: 0.5rem;"
                                                            onmouseover="this.style.background='#f3f4f6'" 
                                                            onmouseout="this.style.background='white'">
                                                            <span style="font-size: 1rem; color: #3b82f6;">●</span>
                                                            <span>Set Seated</span>
                                                        </button>
                                                        <button onclick="updatePlayerStatus('{{ $seat['seat_id'] }}', 'playing')" 
                                                            style="width: 100%; padding: 0.5rem 0.75rem; border: none; background: white; color: #1f2937; text-align: left; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; display: flex; align-items: center; gap: 0.5rem;"
                                                            onmouseover="this.style.background='#f3f4f6'" 
                                                            onmouseout="this.style.background='white'">
                                                            <span style="font-size: 1rem; color: #10b981;">✓</span>
                                                            <span>Set Playing</span>
                                                        </button>
                                                        <button onclick="updatePlayerStatus('{{ $seat['seat_id'] }}', 'away')" 
                                                            style="width: 100%; padding: 0.5rem 0.75rem; border: none; background: white; color: #1f2937; text-align: left; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; display: flex; align-items: center; gap: 0.5rem;"
                                                            onmouseover="this.style.background='#f3f4f6'" 
                                                            onmouseout="this.style.background='white'">
                                                            <span style="font-size: 1rem; color: #f59e0b;">⏱</span>
                                                            <span>Set Away</span>
                                                        </button>
                                                        <button onclick="updatePlayerStatus('{{ $seat['seat_id'] }}', 'sitting_out')" 
                                                            style="width: 100%; padding: 0.5rem 0.75rem; border: none; background: white; color: #1f2937; text-align: left; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; display: flex; align-items: center; gap: 0.5rem; border-top: 1px solid #e5e7eb;"
                                                            onmouseover="this.style.background='#f3f4f6'" 
                                                            onmouseout="this.style.background='white'">
                                                            <span style="font-size: 1rem; color: #6b7280;">⏸</span>
                                                            <span>Sit Out</span>
                                                        </button>
                                                        <button onclick="updatePlayerStatus('{{ $seat['seat_id'] }}', 'waiting', '{{ $seat['player_name'] }}', '{{ $seat['seat_number'] }}')" 
                                                            style="width: 100%; padding: 0.5rem 0.75rem; border: none; background: white; color: #1f2937; text-align: left; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; display: flex; align-items: center; gap: 0.5rem; border-top: 1px solid #e5e7eb;"
                                                            onmouseover="this.style.background='#fffbeb'" 
                                                            onmouseout="this.style.background='white'">
                                                            <span style="font-size: 1rem; color: #f59e0b;">⏱</span>
                                                            <span>Move to Waiting</span>
                                                        </button>
                                                        <button onclick="updatePlayerStatus('{{ $seat['seat_id'] }}', 'left')" 
                                                            style="width: 100%; padding: 0.5rem 0.75rem; border: none; background: white; color: #ef4444; text-align: left; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; display: flex; align-items: center; gap: 0.5rem; border-top: 1px solid #e5e7eb;"
                                                            onmouseover="this.style.background='#fef2f2'" 
                                                            onmouseout="this.style.background='white'">
                                                            <span style="font-size: 1rem;">✕</span>
                                                            <span>Remove</span>
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <div style="width: 48px; height: 48px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #1f2937; font-size: 1.5rem; margin-bottom: 0.5rem;">
                                                    {{ $seat['seat_number'] }}
                                                </div>
                                                <div style="color: white; font-size: 0.875rem; font-weight: 600; line-height: 1.2; margin-bottom: 0.375rem; width: 100%; word-wrap: break-word; overflow-wrap: break-word; hyphens: auto;">
                                                    {{ $seat['player_name'] ?? 'Unknown' }}
                                                </div>
                                                <div style="background: {{ $badgeBg }}; color: white; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 700; margin-bottom: 0.25rem;">
                                                    {{ $statusText }}
                                                </div>
                                                @if($seat['current_stack'] > 0)
                                                <div style="color: white; font-size: 0.75rem; opacity: 0.9; font-weight: 600;">
                                                    ₾{{ number_format($seat['current_stack']) }}
                                                </div>
                                                @endif
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
                                <div style="width: 10px; height: 10px; background: #10b981; border-radius: 50%;"></div>
                                <span style="color: #6b7280;"><strong style="color: #1f2937;">{{ collect($this->table['seats'])->where('status', 'playing')->count() }}</strong> Playing</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.375rem;">
                                <div style="width: 10px; height: 10px; background: #3b82f6; border-radius: 50%;"></div>
                                <span style="color: #6b7280;"><strong style="color: #1f2937;">{{ collect($this->table['seats'])->where('status', 'seated')->count() }}</strong> Seated</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.375rem;">
                                <div style="width: 10px; height: 10px; background: #d1d5db; border-radius: 50%;"></div>
                                <span style="color: #6b7280;"><strong style="color: #1f2937;">{{ collect($this->table['seats'])->where('occupied', false)->count() }}</strong> Empty</span>
                            </div>
                        </div>
                        <div style="color: #1f2937; font-weight: 700; font-size: 1rem;">
                            {{ round(($this->table['occupied_count'] / $this->table['total_seats']) * 100) }}%
                        </div>
                    </div>
                </div>
                </div>
                <!-- End Table Section -->
                
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
                                        data-seat-id="{{ $player['id'] }}"
                                        data-player-name="{{ $player['name'] }}"
                                        data-from-waiting="true"
                                        ondragstart="handleWaitingDragStart(event)"
                                        ondragend="handleDragEnd(event)"
                                        style="background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; padding: 0.75rem; margin-bottom: 0.5rem; transition: all 0.2s; cursor: move;" 
                                        onmouseover="this.style.background='#fde68a'; this.style.transform='translateX(2px)'" 
                                        onmouseout="this.style.background='#fef3c7'; this.style.transform='translateX(0)'">
                                        <div style="display: flex; align-items: start; gap: 0.75rem;">
                                            <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.875rem; flex-shrink: 0;">
                                                {{ $player['position'] ?? ($index + 1) }}
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
        // Fullscreen functionality
        function toggleFullscreen() {
            const elem = document.getElementById('cash-game-dashboard');
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
                seatId: event.target.dataset.seatId,
                playerName: event.target.dataset.playerName,
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
                seatId: event.target.dataset.seatId,
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
            const toSeat = dropZone.dataset.seat;
            
            // Only allow dropping on empty seats
            if (!dropZone.classList.contains('empty-seat-drop-zone')) {
                showErrorModal('Cannot drop on an occupied seat');
                return false;
            }
            
            if (!draggedData || !draggedData.seatId) {
                showErrorModal('Invalid drag data');
                return false;
            }
            
            // Check if trying to drop on the same seat (only for seat-to-seat moves)
            if (!draggedData.fromWaiting && draggedData.fromSeat === toSeat) {
                return false;
            }
            
            // Show loading indicator
            dropZone.style.opacity = '0.5';
            
            try {
                // Determine which endpoint to use
                const endpoint = draggedData.fromWaiting 
                    ? `{{ url('/api/cash-game-seat') }}/${draggedData.seatId}/move-from-waiting`
                    : `{{ url('/api/cash-game-seat') }}/${draggedData.seatId}/seat`;
                
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
                        seat_number: parseInt(toSeat),
                        buy_in_amount: draggedData.fromWaiting ? {{ $this->cashGame->default_buy_in ?? $this->cashGame->min_buy_in ?? 0 }} : undefined
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
                        updateSeatToOccupied(toSeat, draggedData.seatId, draggedData.playerName, 'seated', 0);
                        
                        // Update stats
                        updateStats('seated', 1);
                        updateStats('waiting', -1);
                        updateStats('available', -1);
                    } else {
                        // Move from old seat to new seat
                        updateSeatToEmpty(draggedData.fromSeat);
                        updateSeatToOccupied(toSeat, draggedData.seatId, draggedData.playerName, 'seated', 0);
                    }
                    
                    const successMsg = draggedData.fromWaiting 
                        ? `${draggedData.playerName} moved to Seat ${toSeat}!`
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
        function togglePlayerMenu(event, seatId) {
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
            
            const menu = document.getElementById(`menu-${seatId}`);
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
        async function updatePlayerStatus(seatId, newStatus, playerName = null, seatNumber = null) {
            // Close the menu
            document.getElementById(`menu-${seatId}`).style.display = 'none';
            
            const statusInfo = {
                'seated': {
                    name: 'Seated',
                    icon: '●',
                    color: '#3b82f6',
                    bgColor: '#eff6ff'
                },
                'playing': {
                    name: 'Playing',
                    icon: '✓',
                    color: '#10b981',
                    bgColor: '#ecfdf5'
                },
                'away': {
                    name: 'Away',
                    icon: '⏱',
                    color: '#f59e0b',
                    bgColor: '#fffbeb'
                },
                'sitting_out': {
                    name: 'Sitting Out',
                    icon: '⏸',
                    color: '#6b7280',
                    bgColor: '#f3f4f6'
                },
                'waiting': {
                    name: 'Waiting',
                    icon: '⏱',
                    color: '#f59e0b',
                    bgColor: '#fffbeb'
                },
                'left': {
                    name: 'Left',
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
                        const response = await fetch(`{{ url('/api/cash-game-seat') }}/${seatId}/status`, {
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
                                if (seatNumber) {
                                    updateSeatToEmpty(seatNumber);
                                }
                                
                                // Add to waiting list
                                if (playerName) {
                                    addToWaitingList(seatId, playerName, data.seat.waiting_position || 1);
                                }
                                
                                // Update stats
                                updateStats('waiting', 1);
                                updateStats('available', 1);
                                
                                // Decrease old status count
                                const oldStatus = data.seat.old_status || 'seated';
                                if (oldStatus !== 'waiting') {
                                    updateStats(oldStatus, -1);
                                }
                                
                                showSuccessToast('Player moved to waiting list!');
                            } else {
                                // Update UI without page reload for other statuses
                                updatePlayerStatusUI(seatId, newStatus, data.seat.old_status || 'seated');
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
        
        // Find seat element
        function findSeatElement(seatNumber) {
            return document.querySelector(`.seat-item[data-seat="${seatNumber}"]`);
        }
        
        // Update seat to empty
        function updateSeatToEmpty(seatNumber) {
            const seatItem = findSeatElement(seatNumber);
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
        }
        
        // Update seat to occupied
        function updateSeatToOccupied(seatNumber, seatId, playerName, status, currentStack = 0) {
            const seatItem = findSeatElement(seatNumber);
            if (!seatItem) return;
            
            // Remove empty-seat-drop-zone class and add seat-drop-zone
            seatItem.classList.remove('empty-seat-drop-zone');
            seatItem.classList.add('seat-drop-zone');
            
            // Determine colors based on status
            let seatGradient, badgeBg, statusText;
            if (status === 'playing') {
                seatGradient = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                badgeBg = '#047857';
                statusText = '✓ Playing';
            } else if (status === 'away') {
                seatGradient = 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)';
                badgeBg = '#b45309';
                statusText = '⏱ Away';
            } else if (status === 'sitting_out') {
                seatGradient = 'linear-gradient(135deg, #6b7280 0%, #4b5563 100%)';
                badgeBg = '#374151';
                statusText = '⏸ Sitting Out';
            } else if (status === 'left' || status === 'removed') {
                seatGradient = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
                badgeBg = '#b91c1c';
                statusText = '✕ Left';
            } else {
                seatGradient = 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)';
                badgeBg = '#1d4ed8';
                statusText = 'Seated';
            }
            
            // Create occupied seat HTML
            seatItem.innerHTML = `
                <div class="draggable-seat" 
                    draggable="true"
                    data-seat-id="${seatId}"
                    data-player-name="${playerName}"
                    data-seat="${seatNumber}"
                    ondragstart="handleDragStart(event)"
                    ondragend="handleDragEnd(event)"
                    style="background: ${seatGradient}; padding: 0.5rem; border-radius: 12px; text-align: center; width: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.2); position: relative; cursor: move;">
                    
                    <div class="player-menu-container" style="position: absolute; top: 0.375rem; right: 0.375rem;">
                        <button onclick="togglePlayerMenu(event, '${seatId}')"
                            onmousedown="event.stopPropagation()"
                            draggable="false"
                            style="background: rgba(0,0,0,0.3); color: white; border: none; width: 24px; height: 24px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1rem; font-weight: 700; padding: 0; transition: all 0.2s;"
                            onmouseover="this.style.background='rgba(0,0,0,0.5)'; this.style.transform='scale(1.1)'" 
                            onmouseout="this.style.background='rgba(0,0,0,0.3)'; this.style.transform='scale(1)'">
                            ⋮
                        </button>
                        <div id="menu-${seatId}" class="player-menu" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 0.25rem; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); min-width: 150px; z-index: 1000; overflow: hidden;">
                            <button onclick="updatePlayerStatus('${seatId}', 'seated')" 
                                style="width: 100%; padding: 0.5rem 0.75rem; border: none; background: white; color: #1f2937; text-align: left; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; display: flex; align-items: center; gap: 0.5rem;"
                                onmouseover="this.style.background='#f3f4f6'" 
                                onmouseout="this.style.background='white'">
                                <span style="font-size: 1rem; color: #3b82f6;">●</span>
                                <span>Set Seated</span>
                            </button>
                            <button onclick="updatePlayerStatus('${seatId}', 'playing')" 
                                style="width: 100%; padding: 0.5rem 0.75rem; border: none; background: white; color: #1f2937; text-align: left; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; display: flex; align-items: center; gap: 0.5rem;"
                                onmouseover="this.style.background='#f3f4f6'" 
                                onmouseout="this.style.background='white'">
                                <span style="font-size: 1rem; color: #10b981;">✓</span>
                                <span>Set Playing</span>
                            </button>
                            <button onclick="updatePlayerStatus('${seatId}', 'away')" 
                                style="width: 100%; padding: 0.5rem 0.75rem; border: none; background: white; color: #1f2937; text-align: left; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; display: flex; align-items: center; gap: 0.5rem;"
                                onmouseover="this.style.background='#f3f4f6'" 
                                onmouseout="this.style.background='white'">
                                <span style="font-size: 1rem; color: #f59e0b;">⏱</span>
                                <span>Set Away</span>
                            </button>
                            <button onclick="updatePlayerStatus('${seatId}', 'sitting_out')" 
                                style="width: 100%; padding: 0.5rem 0.75rem; border: none; background: white; color: #1f2937; text-align: left; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; display: flex; align-items: center; gap: 0.5rem; border-top: 1px solid #e5e7eb;"
                                onmouseover="this.style.background='#f3f4f6'" 
                                onmouseout="this.style.background='white'">
                                <span style="font-size: 1rem; color: #6b7280;">⏸</span>
                                <span>Sit Out</span>
                            </button>
                            <button onclick="updatePlayerStatus('${seatId}', 'waiting', '${playerName}', '${seatNumber}')" 
                                style="width: 100%; padding: 0.5rem 0.75rem; border: none; background: white; color: #1f2937; text-align: left; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; display: flex; align-items: center; gap: 0.5rem; border-top: 1px solid #e5e7eb;"
                                onmouseover="this.style.background='#fffbeb'" 
                                onmouseout="this.style.background='white'">
                                <span style="font-size: 1rem; color: #f59e0b;">⏱</span>
                                <span>Move to Waiting</span>
                            </button>
                            <button onclick="updatePlayerStatus('${seatId}', 'left')" 
                                style="width: 100%; padding: 0.5rem 0.75rem; border: none; background: white; color: #ef4444; text-align: left; cursor: pointer; font-size: 0.875rem; transition: background 0.2s; display: flex; align-items: center; gap: 0.5rem; border-top: 1px solid #e5e7eb;"
                                onmouseover="this.style.background='#fef2f2'" 
                                onmouseout="this.style.background='white'">
                                <span style="font-size: 1rem;">✕</span>
                                <span>Remove</span>
                            </button>
                        </div>
                    </div>
                    
                    <div style="width: 48px; height: 48px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #1f2937; font-size: 1.5rem; margin-bottom: 0.5rem;">
                        ${seatNumber}
                    </div>
                    <div style="color: white; font-size: 0.875rem; font-weight: 600; line-height: 1.2; margin-bottom: 0.375rem; width: 100%; word-wrap: break-word; overflow-wrap: break-word; hyphens: auto;">
                        ${playerName}
                    </div>
                    <div style="background: ${badgeBg}; color: white; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 700; margin-bottom: 0.25rem;">
                        ${statusText}
                    </div>
                    ${currentStack > 0 ? `<div style="color: white; font-size: 0.75rem; opacity: 0.9; font-weight: 600;">₾${currentStack.toLocaleString()}</div>` : ''}
                </div>
            `;
        }
        
        // Update player status (change colors and badge)
        function updatePlayerStatusUI(seatId, newStatus, oldStatus) {
            // Find the seat element by seat ID
            const draggableSeat = document.querySelector(`[data-seat-id="${seatId}"]`);
            if (!draggableSeat) return;
            
            const seatItem = draggableSeat.closest('.seat-item');
            const seatNumber = draggableSeat.dataset.seat;
            const playerName = draggableSeat.dataset.playerName;
            const currentStack = 0; // You might want to preserve this from the original element
            
            // Update the seat with new status
            updateSeatToOccupied(seatNumber, seatId, playerName, newStatus, currentStack);
            
            // Update stats if status changed
            if (oldStatus && oldStatus !== newStatus) {
                updateStats(oldStatus, -1);
                updateStats(newStatus, 1);
            }
        }
        
        // Update stats in header
        function updateStats(type, change) {
            const statsMap = {
                'seated': 2, // Index in stats bar (0=Total, 1=Available, 2=Seated, 3=Playing, 4=Away, 5=Waiting, 6=Fill Rate)
                'playing': 3,
                'away': 4,
                'waiting': 5,
                'available': 1
            };
            
            if (!statsMap[type]) return;
            
            // Get all stat cards in the header
            const statsContainer = document.querySelector('div[style*="display: flex; gap: 0.5rem"]');
            if (!statsContainer) return;
            
            const statsCards = statsContainer.querySelectorAll('div[style*="min-width: 75px"]');
            if (statsCards[statsMap[type]]) {
                const statValue = statsCards[statsMap[type]].querySelector('div[style*="font-size: 1.25rem"]');
                if (statValue) {
                    const currentValue = parseInt(statValue.textContent) || 0;
                    statValue.textContent = Math.max(0, currentValue + change);
                }
            }
            
            // Update fill rate
            if (type === 'available' || type === 'seated' || type === 'playing' || type === 'away') {
                // Calculate new fill rate
                const totalSeats = parseInt(statsCards[0]?.querySelector('div[style*="font-size: 1.25rem"]')?.textContent) || 0;
                const available = parseInt(statsCards[1]?.querySelector('div[style*="font-size: 1.25rem"]')?.textContent) || 0;
                const occupied = totalSeats - available;
                const fillPercentage = totalSeats > 0 ? ((occupied / totalSeats) * 100).toFixed(1) : 0;
                
                // Update fill rate display
                const fillRateCard = statsCards[6];
                if (fillRateCard) {
                    const fillRateValue = fillRateCard.querySelector('div[style*="font-size: 1.25rem"]');
                    if (fillRateValue) {
                        fillRateValue.textContent = fillPercentage + '%';
                    }
                }
            }
        }
        
        // Update waiting list count
        function updateWaitingListCount(change) {
            const waitingBadge = document.querySelector('.waiting-list-scroll')?.previousElementSibling?.querySelector('span[style*="background: rgba(255,255,255,0.3)"]');
            const waitingFooter = document.querySelector('.waiting-list-scroll')?.nextElementSibling?.querySelector('strong');
            
            if (waitingBadge) {
                const currentCount = parseInt(waitingBadge.textContent) || 0;
                const newCount = Math.max(0, currentCount + change);
                waitingBadge.textContent = newCount;
                if (waitingFooter) {
                    waitingFooter.textContent = newCount;
                }
            }
            
            // Also update stats
            updateStats('waiting', change);
        }
        
        // Add player to waiting list UI
        function addToWaitingList(seatId, playerName, position) {
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
            waitingItem.setAttribute('data-seat-id', seatId);
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
        
        // Toast notification
        function showSuccessToast(message) {
            const toast = document.createElement('div');
            toast.id = 'success-toast';
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 10000;
                font-weight: 600;
                animation: slideInRight 0.3s ease-out;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            `;
            toast.innerHTML = `
                <span style="font-size: 1.25rem;">✓</span>
                <span>${message}</span>
            `;
            
            const container = getModalContainer();
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            @keyframes slideInRight {
                from {
                    opacity: 0;
                    transform: translateX(100%);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            
            @keyframes slideOutRight {
                from {
                    opacity: 1;
                    transform: translateX(0);
                }
                to {
                    opacity: 0;
                    transform: translateX(100%);
                }
            }
            
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
        </script>
    @else
        <div style="text-align: center; padding: 3rem;">
            <p style="color: #6b7280;">No cash game selected. Please select a cash game from the dashboard.</p>
        </div>
    @endif
</x-filament-panels::page>


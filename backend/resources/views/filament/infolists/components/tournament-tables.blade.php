@php
    $viewData = $getViewData();
    $tables = $viewData['tables'] ?? [];
    $tournament = $viewData['tournament'] ?? null;
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($tables as $table)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Table {{ $table['table_number'] }}
                    </h3>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full 
                        {{ $table['occupied_count'] >= $table['total_seats'] ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                           ($table['occupied_count'] >= $table['total_seats'] * 0.8 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                           'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200') }}">
                        {{ $table['occupied_count'] }}/{{ $table['total_seats'] }}
                    </span>
                </div>
                
                <div class="grid grid-cols-3 gap-2">
                    @foreach($table['seats'] as $seat)
                        <div class="relative">
                            <div class="aspect-square rounded-lg border-2 flex items-center justify-center text-xs font-medium transition-all
                                {{ $seat['occupied'] 
                                    ? ($seat['checked_in'] 
                                        ? 'bg-green-100 border-green-500 text-green-900 dark:bg-green-900 dark:border-green-400 dark:text-green-100' 
                                        : 'bg-blue-100 border-blue-500 text-blue-900 dark:bg-blue-900 dark:border-blue-400 dark:text-blue-100')
                                    : 'bg-gray-50 border-gray-300 text-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400' }}">
                                <div class="text-center">
                                    <div class="font-bold">{{ $seat['seat_number'] }}</div>
                                    @if($seat['occupied'])
                                        <div class="text-[10px] mt-1 truncate px-1" title="{{ $seat['player_name'] }}">
                                            {{ Str::limit($seat['player_name'], 8) }}
                                        </div>
                                        @if($seat['checked_in'])
                                            <div class="text-[8px] text-green-700 dark:text-green-300 mt-0.5">✓</div>
                                        @endif
                                    @else
                                        <div class="text-[10px] mt-1 text-gray-400">Empty</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
    
    <div class="mt-4 flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded border-2 bg-green-100 border-green-500"></div>
            <span>Checked In</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded border-2 bg-blue-100 border-blue-500"></div>
            <span>Registered</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded border-2 bg-gray-50 border-gray-300"></div>
            <span>Empty</span>
        </div>
    </div>
</div>


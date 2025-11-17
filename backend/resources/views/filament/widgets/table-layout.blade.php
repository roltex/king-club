<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Table Layout
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($this->getTableLayout() as $table)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-800">
                    <h3 class="text-lg font-bold text-center mb-4 text-gray-900 dark:text-white">
                        Table {{ $table['table_number'] }}
                    </h3>
                    
                    <div class="grid grid-cols-3 gap-2">
                        @foreach($table['seats'] as $seat)
                            <div class="relative">
                                <div class="aspect-square rounded-lg border-2 flex items-center justify-center text-sm font-semibold
                                    {{ $seat['occupied'] 
                                        ? ($seat['status'] === 'checked_in' 
                                            ? 'bg-green-100 border-green-500 text-green-800 dark:bg-green-900 dark:text-green-200' 
                                            : 'bg-blue-100 border-blue-500 text-blue-800 dark:bg-blue-900 dark:text-blue-200') 
                                        : 'bg-gray-50 border-gray-300 text-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400' 
                                    }}">
                                    
                                    <div class="text-center">
                                        <div class="text-xs opacity-60">Seat</div>
                                        <div class="text-lg">{{ $seat['seat_number'] }}</div>
                                        
                                        @if($seat['occupied'])
                                            <div class="text-xs mt-1 truncate px-1" title="{{ $seat['player']['name'] ?? '' }}">
                                                {{ Str::limit($seat['player']['name'] ?? '', 10) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-4 text-xs text-gray-500 dark:text-gray-400 text-center">
                        {{ collect($table['seats'])->where('occupied', true)->count() }}/9 occupied
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex flex-wrap gap-4 justify-center text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-gray-50 border-2 border-gray-300 dark:bg-gray-700 dark:border-gray-600"></div>
                <span class="text-gray-700 dark:text-gray-300">Available</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-blue-100 border-2 border-blue-500 dark:bg-blue-900"></div>
                <span class="text-gray-700 dark:text-gray-300">Reserved</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-green-100 border-2 border-green-500 dark:bg-green-900"></div>
                <span class="text-gray-700 dark:text-gray-300">Checked In</span>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>


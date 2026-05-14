<div>
    <div x-data="{ open: false }" class="relative">
        <!-- Notification Bell Button -->
        <button
            @click="open = !open"
            class="flex items-center justify-center p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none"
        >
            <span class="relative inline-block">
                <x-heroicon-o-bell class="w-6 h-6" />
                @if($this->unreadAlertsCount > 0)
                    <span class="absolute top-0 right-0 flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-danger-500 rounded-full">
                        {{ $this->unreadAlertsCount }}
                    </span>
                @endif
            </span>
        </button>

        <!-- Dropdown -->
        <div
            x-show="open"
            @click.away="open = false"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute right-0 z-50 mt-2 w-80 origin-top-right rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
            style="display: none;"
        >
            <div class="p-2">
                <div class="flex items-center justify-between mb-2 px-3 pt-2">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Notifications</h3>
                    @if($this->unreadAlertsCount > 0)
                        <button 
                            wire:click="markAllAsRead"
                            class="text-sm text-primary-600 hover:text-primary-500 dark:text-primary-500 dark:hover:text-primary-400"
                        >
                            Tout marquer comme lu
                        </button>
                    @endif
                </div>
                
                <div class="max-h-72 overflow-y-auto">
                    @forelse($this->recentAlerts as $alert)
                        <div class="flex items-start p-3 {{ $alert->is_read ? 'opacity-75' : 'bg-gray-50 dark:bg-gray-700/50' }} hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg mb-1">
                            <div class="flex-shrink-0 mr-3">
                                <x-dynamic-component 
                                    :component="'heroicon-o-' . str_replace('heroicon-o-', '', $alert->icon)" 
                                    class="w-5 h-5 {{ $alert->is_read ? 'text-gray-400 dark:text-gray-500' : 'text-' . $alert->color . '-500' }}" 
                                />
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 {{ $alert->is_read ? '' : 'font-bold' }}">
                                        {{ $alert->title }}
                                    </p>
                                    <button 
                                        wire:click="markAsRead({{ $alert->id }})"
                                        class="{{ $alert->is_read ? 'hidden' : 'text-xs text-primary-600 hover:text-primary-500 dark:text-primary-500 dark:hover:text-primary-400' }}"
                                    >
                                        <x-heroicon-o-check class="w-4 h-4" />
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $alert->content }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    {{ $alert->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="py-4 text-center text-gray-500 dark:text-gray-400">
                            <p>Aucune notification</p>
                        </div>
                    @endforelse
                </div>
                
                <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                    <a 
                        href="{{ route('filament.admin.resources.alerts.index') }}"
                        class="block w-full text-center py-2 text-sm text-primary-600 hover:text-primary-500 dark:text-primary-500 dark:hover:text-primary-400"
                    >
                        Voir toutes les notifications
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

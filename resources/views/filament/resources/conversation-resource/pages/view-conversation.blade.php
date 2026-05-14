<x-filament-panels::page>
    <div class="flex flex-col h-full gap-4">
        <!-- Conversation Info -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                {{ $this->record->display_title }}
            </h2>
            <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                <div>
                    <span class="font-medium">Salle:</span>
                    {{ $this->record->gym->name }}
                </div>
                @if($this->record->superAdmin)
                    <div>
                        <span class="font-medium">Super Admin:</span>
                        {{ $this->record->superAdmin->name }}
                    </div>
                @endif
                <div>
                    <span class="font-medium">Messages:</span>
                    {{ $this->record->messages_count ?? $this->record->messages()->count() }}
                </div>
            </div>
        </div>

        <!-- Messages Container -->
        <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden flex flex-col">
            <!-- Messages List -->
            <div 
                class="flex-1 overflow-y-auto p-6 space-y-4"
                id="messages-container"
                x-data="{
                    initMessages() {
                        this.scrollToBottom();
                        this.$wire.$on('messageSent', () => {
                            this.scrollToBottom();
                        });
                    },
                    scrollToBottom() {
                        this.$nextTick(() => {
                            this.$el.scrollTop = this.$el.scrollHeight;
                        });
                    }
                }"
                x-init="initMessages()"
            >
                @forelse($this->messages as $message)
                    <div class="flex {{ $message->isFromCurrentUser() ? 'justify-end' : 'justify-start' }}">
                        <div class="{{ $message->isFromCurrentUser() ? 'bg-primary-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100' }} rounded-lg px-4 py-2 max-w-[80%]">
                            <div class="flex justify-between items-center mb-1 gap-2">
                                <span class="font-medium text-sm">{{ $message->user->name }}</span>
                                <span class="text-xs {{ $message->isFromCurrentUser() ? 'text-primary-100' : 'text-gray-500 dark:text-gray-400' }}">
                                    {{ $message->created_at->format('H:i') }}
                                </span>
                            </div>
                            <p class="text-sm whitespace-pre-wrap">{{ $message->content }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                        <p>Aucun message. Commencez la conversation!</p>
                    </div>
                @endforelse
            </div>

            <!-- Message Input -->
            <div class="border-t border-gray-200 dark:border-gray-700 p-6">
                <form wire:submit="sendMessage" class="flex items-end gap-2">
                    <div class="flex-1">
                        <textarea 
                            wire:model="messageContent" 
                            placeholder="Tapez votre message..." 
                            class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 resize-none p-3"
                            rows="3"
                            x-data="{ 
                                handleKeydown(e) {
                                    if (e.key === 'Enter' && !e.shiftKey) {
                                        e.preventDefault();
                                        $wire.sendMessage();
                                    }
                                }
                            }"
                            x-on:keydown="handleKeydown($event)"
                        ></textarea>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Appuyez sur Entrée pour envoyer, Maj+Entrée pour un saut de ligne
                        </p>
                    </div>
                    <button 
                        type="submit" 
                        class="px-6 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition flex items-center gap-2 h-fit"
                    >
                        <span>Envoyer</span>
                        <x-heroicon-o-paper-airplane class="w-4 h-4" />
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-filament-panels::page>

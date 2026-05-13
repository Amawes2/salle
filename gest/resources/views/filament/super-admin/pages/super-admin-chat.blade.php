<x-filament-panels::page>
    <div class="flex flex-col h-[70vh] bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <div class="flex h-full">
            <!-- Conversations Sidebar -->
            <div class="w-1/4 border-r border-gray-200 dark:border-gray-700 flex flex-col">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Conversations</h2>
                    <button 
                        x-data=""
                        x-on:click="$dispatch('open-modal', { id: 'new-conversation-modal' })"
                        class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
                        title="Nouvelle conversation"
                    >
                        <x-heroicon-o-plus class="w-5 h-5 text-primary-500" />
                    </button>
                </div>
                <div class="overflow-y-auto flex-1">
                    @forelse($this->conversations as $conversation)
                        <button 
                            wire:click="selectConversation({{ $conversation->id }})"
                            class="w-full text-left p-4 hover:bg-gray-50 dark:hover:bg-gray-700 {{ $selectedConversationId === $conversation->id ? 'bg-gray-100 dark:bg-gray-700' : '' }} border-b border-gray-200 dark:border-gray-700 flex items-start"
                        >
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                        {{ $conversation->gym->name }}
                                    </p>
                                    @if($unreadCount = $this->getUnreadCount($conversation->id))
                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-primary-500 rounded-full">
                                            {{ $unreadCount }}
                                        </span>
                                    @endif
                                </div>
                                @if($conversation->latestMessage)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-1">
                                        {{ $conversation->latestMessage->user->name }}: {{ Str::limit($conversation->latestMessage->content, 30) }}
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                        {{ $conversation->latestMessage->created_at->diffForHumans() }}
                                    </p>
                                @else
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Aucun message
                                    </p>
                                @endif
                            </div>
                        </button>
                    @empty
                        <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                            <p>Aucune conversation</p>
                            <button 
                                x-data=""
                                x-on:click="$dispatch('open-modal', { id: 'new-conversation-modal' })"
                                class="mt-2 px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition"
                            >
                                Démarrer une conversation
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Chat Area -->
            <div class="flex-1 flex flex-col">
                @if($this->selectedConversation)
                    <!-- Chat Header -->
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ $this->selectedConversation->gym->name }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Propriétaire: {{ $this->selectedConversation->gym->owner->name }}
                        </p>
                    </div>

                    <!-- Messages -->
                    <div 
                        class="flex-1 p-4 overflow-y-auto" 
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
                        <div class="space-y-4">
                            @forelse($this->messages as $message)
                                <div class="flex {{ $message->isFromCurrentUser() ? 'justify-end' : 'justify-start' }}">
                                    <div class="{{ $message->isFromCurrentUser() ? 'bg-primary-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100' }} rounded-lg px-4 py-2 max-w-[80%]">
                                        <div class="flex justify-between items-center mb-1">
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
                    </div>

                    <!-- Message Input -->
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        <form wire:submit="sendMessage" class="flex items-end gap-2">
                            <div class="flex-1">
                                <textarea 
                                    wire:model="messageContent" 
                                    placeholder="Tapez votre message..." 
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 resize-none"
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
                                class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition flex items-center gap-2"
                            >
                                <span>Envoyer</span>
                                <x-heroicon-o-paper-airplane class="w-4 h-4" />
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex-1 flex items-center justify-center">
                        <div class="text-center text-gray-500 dark:text-gray-400">
                            <x-heroicon-o-chat-bubble-left-right class="w-16 h-16 mx-auto mb-4 opacity-50" />
                            <h3 class="text-lg font-medium mb-2">Aucune conversation sélectionnée</h3>
                            <p class="mb-4">Sélectionnez une conversation ou démarrez-en une nouvelle</p>
                            <button 
                                x-data=""
                                x-on:click="$dispatch('open-modal', { id: 'new-conversation-modal' })"
                                class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition"
                            >
                                Démarrer une conversation
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- New Conversation Modal -->
    <x-filament::modal id="new-conversation-modal" width="md">
        <x-slot name="heading">Nouvelle conversation</x-slot>

        <x-slot name="description">
            Sélectionnez une salle de sport pour démarrer une conversation avec son propriétaire.
        </x-slot>

        <div class="space-y-4">
            <div>
                <x-filament::input.wrapper>
                    <x-filament::input.select
                        wire:model="selectedGymId"
                        placeholder="Sélectionnez une salle de sport"
                    >
                        @foreach($this->gyms as $gym)
                            <option value="{{ $gym->id }}">{{ $gym->name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button
                x-on:click="$dispatch('close-modal', { id: 'new-conversation-modal' })"
                color="gray"
            >
                Annuler
            </x-filament::button>

            <x-filament::button
                wire:click="startNewConversation"
                x-on:click="$dispatch('close-modal', { id: 'new-conversation-modal' })"
                :disabled="!$selectedGymId"
            >
                Démarrer la conversation
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>

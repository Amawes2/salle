<div>
    <a 
        href="{{ route('filament.admin.pages.chat') }}"
        class="flex items-center justify-center p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none"
    >
        <span class="relative inline-block">
            <x-heroicon-o-chat-bubble-left-right class="w-6 h-6" />
            @if($this->unreadMessagesCount > 0)
                <span class="absolute top-0 right-0 flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-primary-500 rounded-full">
                    {{ $this->unreadMessagesCount }}
                </span>
            @endif
        </span>
    </a>
</div>

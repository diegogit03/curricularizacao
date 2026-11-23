<x-filament-panels::page>
    <div class="mx-auto flex h-[calc(100dvh-14rem)] min-h-[28rem] flex-col">
        <div class="mb-4 flex items-start justify-between gap-4">
            <div>
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                    Assistente Financeiro
                </h2>

                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                    Converse com o agente para consultar e registrar transações e categorias.
                </p>
            </div>

            <x-filament::button
                color="gray"
                icon="heroicon-m-plus"
                size="sm"
                wire:click="newConversation"
                :disabled="$streaming"
                outlined
            >
                Nova conversa
            </x-filament::button>
        </div>

        <div
            class="flex-1 space-y-4 overflow-y-auto rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
            x-data
            x-ref="messages"
            x-init="
                const scrollToBottom = () => {
                    $refs.messages.scrollTop = $refs.messages.scrollHeight;
                };

                scrollToBottom();

                new MutationObserver(scrollToBottom).observe($refs.messages, {
                    childList: true,
                    subtree: true,
                    characterData: true,
                });
            "
        >
            @if ($messages === [] && ! $streaming)
                <div class="flex h-full flex-col items-center justify-center gap-2 text-center">
                    <x-filament::icon
                        icon="heroicon-o-sparkles"
                        class="size-8 text-gray-400 dark:text-gray-500"
                    />

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Nenhuma mensagem ainda. Comece uma conversa abaixo.
                    </p>
                </div>
            @endif

            @foreach ($messages as $message)
                @if ($message['role'] === 'user')
                    <div class="flex justify-end">
                        <div class="max-w-[80%] rounded-2xl rounded-br-sm bg-primary-600 px-4 py-2 text-sm text-white">
                            <p class="whitespace-pre-wrap break-words">{{ $message['content'] }}</p>
                        </div>
                    </div>
                @else
                    <div class="flex justify-start">
                        <div class="max-w-[80%] rounded-2xl rounded-bl-sm bg-gray-100 px-4 py-2 text-sm text-gray-950 dark:bg-white/10 dark:text-white">
                            <p class="whitespace-pre-wrap break-words">{{ $message['content'] }}</p>
                        </div>
                    </div>
                @endif
            @endforeach

            @if ($streaming)
                <div class="flex justify-start">
                    <div
                        class="max-w-[80%] rounded-2xl rounded-bl-sm bg-gray-100 px-4 py-2 text-sm text-gray-950 dark:bg-white/10 dark:text-white"
                        wire:stream="assistant"
                    >
                        @if ($assistantText === '')
                            <div class="flex items-center gap-1.5 py-0.5">
                                <span class="size-1.5 animate-bounce rounded-full bg-gray-400 [animation-delay:0ms]"></span>
                                <span class="size-1.5 animate-bounce rounded-full bg-gray-400 [animation-delay:150ms]"></span>
                                <span class="size-1.5 animate-bounce rounded-full bg-gray-400 [animation-delay:300ms]"></span>
                            </div>
                        @else
                            <p class="whitespace-pre-wrap break-words">{{ $assistantText }}</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <form class="mt-4 flex gap-2" wire:submit="send">
            <x-filament::input.wrapper class="flex-1">
                <x-filament::input
                    autocomplete="off"
                    placeholder="Pergunte algo sobre suas finanças..."
                    type="text"
                    wire:model="input"
                    :disabled="$streaming"
                />
            </x-filament::input.wrapper>

            <x-filament::button
                icon="heroicon-m-paper-airplane"
                type="submit"
                :disabled="$streaming"
            >
                Enviar
            </x-filament::button>
        </form>
    </div>
</x-filament-panels::page>

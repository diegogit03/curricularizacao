<?php

namespace App\Filament\Pages;

use App\Ai\Agents\FinanceAgent;
use App\Models\User;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Laravel\Ai\Streaming\Events\TextDelta;
use Laravel\Ai\Streaming\Events\TextStart;
use Throwable;

class Agent extends Page
{
    protected string $view = 'filament.pages.agent';

    protected static ?string $title = 'Agente de IA';

    protected ?string $heading = '';

    protected static ?string $navigationLabel = 'Agente de IA';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Sparkles;

    /**
     * @var array<int, array{role: string, content: string}>
     */
    public array $messages = [];

    public string $input = '';

    public bool $streaming = false;

    public ?string $conversationId = null;

    public string $assistantText = '';

    public ?string $pendingQuestion = null;

    public function mount(): void
    {
        $this->loadConversation();
    }

    public function send(): void
    {
        $this->input = trim($this->input);

        if ($this->input === '' || $this->streaming) {
            return;
        }

        $this->pendingQuestion = $this->input;

        $this->messages[] = ['role' => 'user', 'content' => $this->pendingQuestion];

        $this->input = '';
        $this->streaming = true;
        $this->assistantText = '';

        $this->js('$wire.ask()');
    }

    public function ask(): void
    {
        if ($this->pendingQuestion === null) {
            return;
        }

        try {
            $user = $this->currentUser();

            $agent = $this->conversationId !== null
                ? (new FinanceAgent)->continue($this->conversationId, as: $user)
                : (new FinanceAgent)->forUser($user);

            $stream = $agent->stream($this->pendingQuestion, provider: 'openrouter', model: 'deepseek/deepseek-v4.1-flash-1017');

            foreach ($stream as $event) {
                if ($event instanceof TextStart && $this->assistantText !== '') {
                    $this->assistantText .= "\n\n";
                }

                if ($event instanceof TextDelta) {
                    $this->assistantText .= $event->delta;

                    $this->stream(to: 'assistant', content: $this->assistantText, replace: true);
                }
            }

            $this->conversationId = $agent->currentConversation();

            $this->refreshConversation();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Não foi possível obter uma resposta do agente.')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            array_pop($this->messages);
        } finally {
            $this->pendingQuestion = null;
            $this->streaming = false;
            $this->assistantText = '';
        }
    }

    public function newConversation(): void
    {
        $this->conversationId = null;
        $this->messages = [];
        $this->input = '';
        $this->streaming = false;
        $this->assistantText = '';
        $this->pendingQuestion = null;
    }

    protected function loadConversation(): void
    {
        $conversation = $this->currentUser()
            ?->conversations()
            ->with('messages')
            ->latest('updated_at')
            ->first();

        if ($conversation === null) {
            return;
        }

        $this->conversationId = $conversation->id;

        $this->messages = $conversation->messages
            ->sortBy('created_at')
            ->values()
            ->map(fn ($message) => ['role' => $message->role, 'content' => $message->content])
            ->all();
    }

    protected function refreshConversation(): void
    {
        if ($this->conversationId === null) {
            return;
        }

        $conversation = $this->currentUser()
            ?->conversations()
            ->find($this->conversationId);

        if ($conversation === null) {
            return;
        }

        $this->messages = $conversation->messages()
            ->orderBy('created_at')
            ->get(['role', 'content'])
            ->map(fn ($message) => ['role' => $message->role, 'content' => $message->content])
            ->all();
    }

    protected function currentUser(): ?User
    {
        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }
}

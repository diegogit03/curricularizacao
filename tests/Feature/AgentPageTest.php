<?php

namespace Tests\Feature;

use App\Ai\Agents\FinanceAgent;
use App\Filament\Pages\Agent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Models\Conversation;
use Laravel\Ai\Models\ConversationMessage;
use Livewire\Livewire;
use Tests\TestCase;

class AgentPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->actingAs($this->user);
    }

    public function test_agent_page_renders(): void
    {
        $this->get('/dashboard/agent')
            ->assertOk()
            ->assertSee('Assistente Financeiro')
            ->assertSee('Nenhuma mensagem ainda. Comece uma conversa abaixo.');
    }

    public function test_sending_a_message_persists_the_conversation_and_shows_the_response(): void
    {
        FinanceAgent::fake(['Olá! Como posso ajudar?']);

        Livewire::test(Agent::class)
            ->assertSet('messages', [])
            ->set('input', 'Quais são as minhas categorias?')
            ->call('send')
            ->call('ask')
            ->assertSet('streaming', false)
            ->assertNotSet('conversationId', null)
            ->assertSee('Quais são as minhas categorias?')
            ->assertSee('Olá! Como posso ajudar?');

        $this->assertDatabaseHas(Conversation::class, [
            'participant_type' => 'App\Models\User',
            'participant_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas(ConversationMessage::class, [
            'role' => 'user',
            'content' => 'Quais são as minhas categorias?',
        ]);

        $this->assertDatabaseHas(ConversationMessage::class, [
            'role' => 'assistant',
            'content' => 'Olá! Como posso ajudar?',
        ]);
    }

    public function test_continuing_a_conversation_keeps_the_context(): void
    {
        FinanceAgent::fake([
            'Primeira resposta.',
            'Segunda resposta com contexto.',
        ]);

        $page = Livewire::test(Agent::class)
            ->set('input', 'Primeira pergunta')
            ->call('send')
            ->call('ask')
            ->assertSet('streaming', false);

        $conversationId = $page->get('conversationId');

        $this->assertNotNull($conversationId);
        $this->assertDatabaseCount(Conversation::class, 1);

        Livewire::test(Agent::class)
            ->assertSet('conversationId', $conversationId)
            ->assertSee('Primeira pergunta')
            ->assertSee('Primeira resposta.')
            ->set('input', 'Segunda pergunta')
            ->call('send')
            ->call('ask')
            ->assertSee('Segunda pergunta')
            ->assertSee('Segunda resposta com contexto.');

        $this->assertDatabaseCount(Conversation::class, 1);
        $this->assertDatabaseCount(ConversationMessage::class, 4);
    }

    public function test_new_conversation_clears_the_current_state(): void
    {
        FinanceAgent::fake(['Olá! Como posso ajudar?']);

        Livewire::test(Agent::class)
            ->set('input', 'Alguma pergunta')
            ->call('send')
            ->call('ask')
            ->assertSet('streaming', false)
            ->assertNotSet('conversationId', null)
            ->call('newConversation')
            ->assertSet('conversationId', null)
            ->assertSet('messages', [])
            ->assertSet('input', '');
    }

    public function test_send_is_ignored_when_input_is_blank_or_streaming(): void
    {
        FinanceAgent::fake(['Olá!']);

        Livewire::test(Agent::class)
            ->set('input', '   ')
            ->call('send')
            ->assertSet('messages', [])
            ->assertSet('streaming', false);

        Livewire::test(Agent::class)
            ->set('input', 'Pergunta')
            ->call('send')
            ->call('send')
            ->assertSet('streaming', true)
            ->assertCount('messages', 1);
    }
}

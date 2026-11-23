<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CreateCategory;
use App\Ai\Tools\CreateTransaction;
use App\Ai\Tools\ListCategories;
use App\Ai\Tools\ListTransactions;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Stringable;

class FinanceAgent implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'Você é um agente financeiro dentro do sistema, utilize as ferramentas para atender as solicitações do usuário';
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new CreateCategory,
            new CreateTransaction,
            new ListCategories,
            new ListTransactions,
        ];
    }
}

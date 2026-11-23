---
paths:
  - 'app/Ai/**'
---

# Ai

## Conversation persistence for AI agents
Agents that persist chat history use the RemembersConversations trait (User has HasConversations). The manual messages() method must be REMOVED from the agent or it overrides the trait's DB-backed history. ai.conversations.generate_title=false in config/ai.php avoids an extra provider call per new conversation (title falls back to the first prompt). Tables agent_conversations / agent_conversation_messages were already covered by migration 2026_08_25_030416 - do not publish the SDK's migration again. The conversationId is captured after streaming via $agent->currentConversation() (set by the RememberConversation middleware).

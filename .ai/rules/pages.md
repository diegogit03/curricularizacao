---
paths:
  - app/Filament/Pages/Agent.php
---

# Pages

## Chat streaming on Filament pages
The Agent page streams FinanceAgent responses using Livewire's $this->stream(to:'assistant', ..., replace:true) + wire:stream in the Blade view. send() appends the user message and triggers ask() via $this->js('$wire.ask()') so the question renders before the stream. State that must survive the second request (pendingQuestion) has to be a PUBLIC Livewire property - protected properties are not serialized between requests. The typing indicator must live inside the wire:stream target because the first chunk replaces its innerHTML.

<?php

namespace App\Ai\Tools;

use App\Models\Transaction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Number;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListTransactions implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Lista transações financeiras com filtros opcionais por tipo (REVENUE ou EXPENSE), categoria, período (start_date e end_date, formato Y-m-d) e limite de resultados.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validator = Validator::make($request->all(), [
            'type' => ['nullable', 'string', 'in:REVENUE,EXPENSE'],
            'category' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return 'Não foi possível listar as transações. Erros de validação: '.$validator->errors()->toJson();
        }

        $transactions = Transaction::query()
            ->with('category')
            ->when($request['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when(
                $request['category'] ?? null,
                fn ($query, $category) => $query->whereHas(
                    'category',
                    fn ($query) => $query->whereRaw('LOWER(name) = ?', [mb_strtolower($category)])
                )
            )
            ->when($request['start_date'] ?? null, fn ($query, $date) => $query->where('date', '>=', $date))
            ->when($request['end_date'] ?? null, fn ($query, $date) => $query->where('date', '<=', $date))
            ->latest('date')
            ->limit($request['limit'] ?? 20)
            ->get();

        if ($transactions->isEmpty()) {
            return 'Nenhuma transação encontrada.';
        }

        return "Transações encontradas:\n".$transactions
            ->map(fn (Transaction $transaction) => sprintf(
                '- %s — %s (%s) em %s [Categoria: %s]',
                $transaction->description,
                Number::currency((float) $transaction->value, 'BRL', 'pt_BR'),
                $transaction->type->value,
                $transaction->date->format('d/m/Y'),
                $transaction->category->name,
            ))
            ->implode("\n");
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()->pattern('^(REVENUE|EXPENSE)$'),
            'category' => $schema->string(),
            'start_date' => $schema->string()->pattern('^\d{4}-\d{2}-\d{2}$'),
            'end_date' => $schema->string()->pattern('^\d{4}-\d{2}-\d{2}$'),
            'limit' => $schema->integer()->min(1)->max(100)->default(20),
        ];
    }
}

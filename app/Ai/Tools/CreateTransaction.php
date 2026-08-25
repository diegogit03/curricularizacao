<?php

namespace App\Ai\Tools;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CreateTransaction implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Registra uma nova transação financeira. Se a categoria informada não existir, ela será criada automaticamente com o mesmo tipo da transação.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validator = Validator::make($request->all(), [
            'description' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::enum(TransactionType::class)],
            'value' => ['required', 'numeric', 'min:0.01'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'category' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return 'Não foi possível registrar a transação. Erros de validação: '.$validator->errors()->toJson();
        }

        $category = Category::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($request['category'])])
            ->first();

        if ($category === null) {
            $category = Category::query()->create([
                'name' => $request['category'],
                'type' => $request['type'],
            ]);
        }

        $transaction = Transaction::query()->create([
            'description' => $request['description'],
            'type' => $request['type'],
            'value' => $request['value'],
            'date' => $request['date'] ?? now()->toDateString(),
            'category_id' => $category->id,
        ]);

        return "Transação registrada: {$transaction->description} de {$transaction->value} ({$transaction->type->value}) na categoria {$category->name}."
            .($category->wasRecentlyCreated ? ' A categoria foi criada automaticamente.' : '');
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'description' => $schema->string()->required(),
            'type' => $schema->string()->pattern('^(REVENUE|EXPENSE)$')->required(),
            'value' => $schema->number()->min(0.01)->required(),
            'date' => $schema->string()->pattern('^\d{4}-\d{2}-\d{2}$'),
            'category' => $schema->string()->required(),
        ];
    }
}

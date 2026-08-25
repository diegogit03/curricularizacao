<?php

namespace App\Ai\Tools;

use App\Enums\TransactionType;
use App\Models\Category;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CreateCategory implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Cria uma nova categoria financeira com nome e tipo (REVENUE para receitas ou EXPENSE para despesas).';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::enum(TransactionType::class)],
        ]);

        if ($validator->fails()) {
            return 'Não foi possível criar a categoria. Erros de validação: '.$validator->errors()->toJson();
        }

        $category = Category::query()->create([
            'name' => $request['name'],
            'type' => $request['type'],
        ]);

        return "Categoria criada: {$category->name} ({$category->type->value}).";
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->required(),
            'type' => $schema->string()->pattern('^(REVENUE|EXPENSE)$')->required(),
        ];
    }
}

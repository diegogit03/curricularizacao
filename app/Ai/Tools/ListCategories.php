<?php

namespace App\Ai\Tools;

use App\Models\Category;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListCategories implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Lista as categorias financeiras cadastradas, com filtro opcional por tipo (REVENUE ou EXPENSE).';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $validator = Validator::make($request->all(), [
            'type' => ['nullable', 'string', 'in:REVENUE,EXPENSE'],
        ]);

        if ($validator->fails()) {
            return 'Não foi possível listar as categorias. Erros de validação: '.$validator->errors()->toJson();
        }

        $categories = Category::query()
            ->when($request['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        if ($categories->isEmpty()) {
            return 'Nenhuma categoria encontrada.';
        }

        return "Categorias encontradas:\n".$categories
            ->map(fn (Category $category) => "- {$category->name} ({$category->type->value}) [ID: {$category->id}]")
            ->implode("\n");
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()->pattern('^(REVENUE|EXPENSE)$'),
        ];
    }
}

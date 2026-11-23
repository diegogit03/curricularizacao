<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Unifies payable_accounts and receivable_accounts into a single accounts table,
 * where the type (REVENUE/EXPENSE) tells whether the account is to be received or paid.
 */
return new class extends Migration
{
    /**
     * @var array<string, array{type: string, settled_status: string, settled_column: string}>
     */
    private array $legacyTables = [
        'payable_accounts' => ['type' => 'EXPENSE', 'settled_status' => 'PAID', 'settled_column' => 'paid_at'],
        'receivable_accounts' => ['type' => 'REVENUE', 'settled_status' => 'RECEIVED', 'settled_column' => 'received_at'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->enum('type', ['REVENUE', 'EXPENSE']);
            $table->enum('status', ['PENDING', 'SETTLED', 'OVERDUE'])->default('PENDING');
            $table->decimal('value', 12, 2);
            $table->date('due_at');
            $table->date('settled_at')->nullable();
            $table->foreignId('category_id')->constrained();
            $table->foreignId('bank_account_id')->nullable()->constrained()->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'due_at']);
        });

        foreach ($this->legacyTables as $legacyTable => $mapping) {
            DB::table($legacyTable)->orderBy('id')->chunk(500, function ($rows) use ($mapping) {
                DB::table('accounts')->insert($rows->map(fn (object $row): array => [
                    'description' => $row->description,
                    'type' => $mapping['type'],
                    'status' => $row->status === $mapping['settled_status'] ? 'SETTLED' : 'PENDING',
                    'value' => $row->value,
                    'due_at' => $row->due_at,
                    'settled_at' => $row->status === $mapping['settled_status'] ? $row->{$mapping['settled_column']} : null,
                    'category_id' => $row->category_id,
                    'bank_account_id' => $row->bank_account_id,
                    'deleted_at' => $row->deleted_at,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ])->all());
            });

            Schema::drop($legacyTable);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->legacyTables as $legacyTable => $mapping) {
            Schema::create($legacyTable, function (Blueprint $table) use ($mapping) {
                $table->id();
                $table->string('description');
                $table->enum('status', [$mapping['settled_status'], 'PENDING']);
                $table->decimal('value', 12, 2);
                $table->date('due_at');
                $table->date($mapping['settled_column']);
                $table->softDeletes();
                $table->timestamps();
                $table->foreignId('category_id')->constrained();
                $table->foreignId('bank_account_id')->nullable()->constrained()->nullOnDelete();
            });

            DB::table('accounts')->where('type', $mapping['type'])->orderBy('id')->chunk(500, function ($rows) use ($legacyTable, $mapping) {
                DB::table($legacyTable)->insert($rows->map(fn (object $row): array => [
                    'description' => $row->description,
                    'status' => $row->status === 'SETTLED' ? $mapping['settled_status'] : 'PENDING',
                    'value' => $row->value,
                    'due_at' => $row->due_at,
                    $mapping['settled_column'] => $row->settled_at ?? $row->due_at,
                    'category_id' => $row->category_id,
                    'bank_account_id' => $row->bank_account_id,
                    'deleted_at' => $row->deleted_at,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ])->all());
            });
        }

        Schema::dropIfExists('accounts');
    }
};

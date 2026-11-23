<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\TransactionType;
use Carbon\CarbonInterface;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['description', 'type', 'status', 'value', 'due_at', 'settled_at', 'category_id', 'bank_account_id'])]
class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'PENDING',
    ];

    /**
     * Only settled accounts keep a settlement date.
     */
    protected static function booted(): void
    {
        static::saving(function (Account $account): void {
            if (! $account->isSettled()) {
                $account->settled_at = null;
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'status' => AccountStatus::class,
            'value' => 'decimal:2',
            'due_at' => 'date:Y-m-d',
            'settled_at' => 'date:Y-m-d',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function isSettled(): bool
    {
        return $this->status === AccountStatus::Settled;
    }

    /**
     * Mark the account as paid (expense) or received (revenue).
     */
    public function markAsSettled(?CarbonInterface $settledAt = null): void
    {
        $this->update([
            'status' => AccountStatus::Settled,
            'settled_at' => $settledAt ?? now(),
        ]);
    }

    /**
     * Pending accounts whose due date is before the given day.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function pastDue(Builder $query, ?CarbonInterface $today = null): void
    {
        $query->where('status', AccountStatus::Pending)
            ->whereDate('due_at', '<', ($today ?? now())->toDateString());
    }
}

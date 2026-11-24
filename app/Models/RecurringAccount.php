<?php

namespace App\Models;

use App\Enums\RecurrenceFrequency;
use App\Enums\TransactionType;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\RecurringAccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['description', 'type', 'frequency', 'value', 'starts_at', 'ends_at', 'is_active', 'category_id', 'bank_account_id'])]
class RecurringAccount extends Model
{
    /** @use HasFactory<RecurringAccountFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'frequency' => RecurrenceFrequency::class,
            'value' => 'decimal:2',
            'starts_at' => 'date:Y-m-d',
            'ends_at' => 'date:Y-m-d',
            'is_active' => 'boolean',
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

    /**
     * Due dates of this recurring account that fall within the given period (inclusive).
     *
     * @return list<CarbonImmutable>
     */
    public function occurrencesBetween(CarbonInterface $from, CarbonInterface $to): array
    {
        if (! $this->is_active) {
            return [];
        }

        $firstDate = CarbonImmutable::parse($this->starts_at)->startOfDay();
        $from = CarbonImmutable::parse($from)->startOfDay();
        $to = CarbonImmutable::parse($to)->startOfDay();

        if ($this->ends_at !== null && $this->ends_at->lt($to)) {
            $to = CarbonImmutable::parse($this->ends_at)->startOfDay();
        }

        $occurrences = [];

        for ($index = 0; ($date = $this->frequency->occurrence($firstDate, $index))->lte($to); $index++) {
            if ($date->gte($from)) {
                $occurrences[] = $date;
            }
        }

        return $occurrences;
    }

    /**
     * First due date on or after the given day, or null when the recurrence has ended.
     */
    public function nextOccurrence(?CarbonInterface $from = null): ?CarbonImmutable
    {
        if (! $this->is_active) {
            return null;
        }

        $firstDate = CarbonImmutable::parse($this->starts_at)->startOfDay();
        $from = CarbonImmutable::parse($from ?? now())->startOfDay();

        for ($index = 0; ($date = $this->frequency->occurrence($firstDate, $index))->lt($from); $index++) {
            //
        }

        if ($this->ends_at !== null && $date->gt($this->ends_at)) {
            return null;
        }

        return $date;
    }

    /**
     * Recurring accounts that are currently generating occurrences.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}

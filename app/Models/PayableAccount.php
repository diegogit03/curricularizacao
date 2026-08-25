<?php

namespace App\Models;

use App\Enums\PayableAccountStatus;
use Database\Factories\PayableAccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['description', 'status', 'value', 'due_at', 'paid_at', 'category_id'])]
class PayableAccount extends Model
{
    /** @use HasFactory<PayableAccountFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PayableAccountStatus::class,
            'value' => 'decimal:2',
            'due_at' => 'date:Y-m-d',
            'paid_at' => 'date:Y-m-d',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

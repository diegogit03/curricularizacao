<?php

namespace App\Models;

use App\Enums\ReceivableAccountStatus;
use Database\Factories\ReceivableAccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['description', 'status', 'value', 'due_at', 'received_at', 'category_id'])]
class ReceivableAccount extends Model
{
    /** @use HasFactory<ReceivableAccountFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ReceivableAccountStatus::class,
            'value' => 'decimal:2',
            'due_at' => 'date',
            'received_at' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $user_id
 * @property string $balance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder<static>|Wallet newModelQuery()
 * @method static Builder<static>|Wallet newQuery()
 * @method static Builder<static>|Wallet query()
 * @method static Builder<static>|Wallet whereBalance($value)
 * @method static Builder<static>|Wallet whereCreatedAt($value)
 * @method static Builder<static>|Wallet whereId($value)
 * @method static Builder<static>|Wallet whereUpdatedAt($value)
 * @method static Builder<static>|Wallet whereUserId($value)
 * @mixin Builder
 */
class Wallet extends Model
{
    use UuidTrait;
    protected $guarded = [];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

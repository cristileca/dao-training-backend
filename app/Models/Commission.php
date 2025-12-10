<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $from_user_id
 * @property string $to_user_id
 * @property int $level
 * @property string $amount
 * @property int $claimed
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $fromUser
 * @property-read User|null $toUser
 * @method static Builder<static>|Commission newModelQuery()
 * @method static Builder<static>|Commission newQuery()
 * @method static Builder<static>|Commission query()
 * @method static Builder<static>|Commission whereAmount($value)
 * @method static Builder<static>|Commission whereClaimed($value)
 * @method static Builder<static>|Commission whereCreatedAt($value)
 * @method static Builder<static>|Commission whereFromUserId($value)
 * @method static Builder<static>|Commission whereId($value)
 * @method static Builder<static>|Commission whereLevel($value)
 * @method static Builder<static>|Commission whereToUserId($value)
 * @method static Builder<static>|Commission whereUpdatedAt($value)
 * @mixin Builder
 */
class Commission extends Model
{
    use UuidTrait;

    protected $casts = [
        'id' => 'string',
    ];

    protected $guarded =  [];

    public function toUser(){
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * @property mixed $0
 * @property mixed $1
 * @property mixed $2
 * @property mixed $3
 * @property mixed $4
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VolumeHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VolumeHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VolumeHistory query()
 * @mixin \Eloquent
 */
class VolumeHistory extends Model
{
    use UuidTrait;

    protected $guarded=[];

    protected $casts = ['user_id', 'from_user_id', 'volume_id', 'price', 'old','type', 'new'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

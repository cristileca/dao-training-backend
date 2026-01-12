<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\VolumesFactory factory($count = null, $state = [])
 * @method static Builder|Volumes newModelQuery()
 * @method static Builder|Volumes newQuery()
 * @method static Builder|Volumes query()
 * @mixin Builder
 * @property string $id
 * @property string $user_id
 * @property float $volume
 * @property float $sales
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|Volumes whereCreatedAt($value)
 * @method static Builder|Volumes whereId($value)
 * @method static Builder|Volumes whereSales($value)
 * @method static Builder|Volumes whereUpdatedAt($value)
 * @method static Builder|Volumes whereUserId($value)
 * @method static Builder|Volumes whereVolume($value)
 * @mixin Builder
 * @property string $start
 * @property string $end
 * @property mixed $0
 * @property mixed $1
 * @property mixed $2
 * @method static Builder<static>|Volumes whereEnd($value)
 * @method static Builder<static>|Volumes whereStart($value)
 * @mixin Builder
 */
class Volumes extends Model
{
    /** @use HasFactory<\Database\Factories\VolumesFactory> */
    use HasFactory, UuidTrait;
    protected $guarded = [];

    protected $casts = ['user_id', 'volume', 'sales', 'start', 'end' ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Database\Factories\VolumesFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property-read User|null $user
 * @method static VolumesFactory factory($count = null, $state = [])
 * @method static Builder|Volume newModelQuery()
 * @method static Builder|Volume newQuery()
 * @method static Builder|Volume query()
 * @mixin Builder
 * @property string $id
 * @property string $user_id
 * @property float $volume
 * @property float $sales
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Volume whereCreatedAt($value)
 * @method static Builder|Volume whereId($value)
 * @method static Builder|Volume whereSales($value)
 * @method static Builder|Volume whereUpdatedAt($value)
 * @method static Builder|Volume whereUserId($value)
 * @method static Builder|Volume whereVolume($value)
 * @mixin Builder
 * @property string $start
 * @property string $end
 * @property mixed $0
 * @property mixed $1
 * @property mixed $2
 * @method static Builder<static>|Volume whereEnd($value)
 * @method static Builder<static>|Volume whereStart($value)
 * @mixin Builder
 */
class Volume extends Model
{
    /** @use HasFactory<VolumesFactory> */
    use HasFactory, UuidTrait;
    protected $guarded = [];

    protected $casts = ['user_id', 'volume', 'sales', 'start', 'end' ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \Database\Factories\TreesFactory factory($count = null, $state = [])
 * @method static Builder|Trees newModelQuery()
 * @method static Builder|Trees newQuery()
 * @method static Builder|Trees query()
 * @mixin Builder
 * @property string $id
 * @property string $user_id
 * @property array<array-key, mixed>|null $user_full_tree
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static Builder|Trees whereCreatedAt($value)
 * @method static Builder|Trees whereId($value)
 * @method static Builder|Trees whereUpdatedAt($value)
 * @method static Builder|Trees whereUserFullTree($value)
 * @method static Builder|Trees whereUserId($value)
 * @mixin Builder
 */

class Trees extends Model
{
    /** @use HasFactory<\Database\Factories\TreesFactory> */
    use HasFactory, UuidTrait;

    protected $guarded = [];

    protected $casts = [
        'user_full_tree' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Database\Factories\PackageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property float $price
 * @property string|null $benefits
 * @property int $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static PackageFactory factory($count = null, $state = [])
 * @method static Builder<static>|Package newModelQuery()
 * @method static Builder<static>|Package newQuery()
 * @method static Builder<static>|Package query()
 * @method static Builder<static>|Package whereActive($value)
 * @method static Builder<static>|Package whereBenefits($value)
 * @method static Builder<static>|Package whereCreatedAt($value)
 * @method static Builder<static>|Package whereId($value)
 * @method static Builder<static>|Package whereName($value)
 * @method static Builder<static>|Package wherePrice($value)
 * @method static Builder<static>|Package whereUpdatedAt($value)
 * @mixin Builder
 */
class Package extends Model
{
    use HasFactory, UuidTrait;

    /** @var array  */
    protected $guarded = [];

    /** @var string[]  */
    protected $casts = [
        'id' => 'string',
        'price' => 'float',
    ];

}

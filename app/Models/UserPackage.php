<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPackage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPackage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPackage query()
 * @mixin Builder
 */
class UserPackage extends Model
{
    protected $guarded = [];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function package(){
        return $this->belongsTo(Package::class);
    }
}

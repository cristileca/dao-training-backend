<?php
namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Wallet;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string|null $referral_id
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static Builder<static>|User newModelQuery()
 * @method static Builder<static>|User newQuery()
 * @method static Builder<static>|User query()
 * @method static Builder<static>|User whereCreatedAt($value)
 * @method static Builder<static>|User whereEmail($value)
 * @method static Builder<static>|User whereEmailVerifiedAt($value)
 * @method static Builder<static>|User whereId($value)
 * @method static Builder<static>|User whereName($value)
 * @method static Builder<static>|User wherePassword($value)
 * @method static Builder<static>|User whereReferralId($value)
 * @method static Builder<static>|User whereRememberToken($value)
 * @method static Builder<static>|User whereUpdatedAt($value)
 * @mixin Builder
 */
class User extends Authenticatable
{
    use UuidTrait, Notifiable, HasFactory;

    /** @var array  */
    protected $guarded = [];

    /** @var string[]  */
    protected $hidden = ['password'];

    protected $casts = [
      'id' => 'string',
      'user_tree' => 'array',
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referral_id');
    }

    public function referrals(): HasMany
    {
       return  $this->hasMany(User::class, 'referral_id');
    }

    public function volumeHistories(): HasMany
    {
        return $this->hasMany(VolumeHistory::class);
    }

    /**
     * @return User|HasOne
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class());
    }

    public function tree(): HasOne
    {
        return $this->hasOne(Trees::class);
    }
    /**
     * @return User|HasMany
     */
    public function packages(){
        return $this->hasMany(UserPackage::class);
    }

    /**
     * @return User|HasMany
     */
    public function transactions(){
         return $this->hasMany(UserPackage::class);
    }





}


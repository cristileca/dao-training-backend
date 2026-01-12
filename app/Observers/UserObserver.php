<?php

namespace App\Observers;

use App\Models\Trees;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $isFirstUser = User::count() === 1;
        $userTree = [];

        if(!$isFirstUser){
            $upTreeUser = Trees::whereUserId($user->referral_id)->first();
            Log::info( "upTreeUser: " , [$upTreeUser->user_full_tree]);

            $userTree = $upTreeUser->user_full_tree;
        }

        $userTree[] = $user->id;

        Trees::create(["user_id" => $user->id, "user_full_tree" => $userTree]);

        if(count($userTree) > 9){
            $userTree = array_slice($userTree, 1);
        }

        $user->update([
            "user_tree" => $userTree,
        ]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}

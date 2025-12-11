<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Wallet;

class WalletController extends Controller
{
    /**
     * @param User $user
     * @return Model
     */
    public function createWallet(User $user)
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0],
        );
    }
}

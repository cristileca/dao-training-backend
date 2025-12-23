<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    /**
     * @return JsonResponse
     */

    public function index(User $user){
        $wallet = Wallet::where('user_id', $user->id)->first();
        Log::info("message",["wallet"=>$wallet]);;

        return response()->json($wallet);
    }

    public function createWallet(Request $request, User $user)
    {
        return Wallet::updateOrCreate(
            ['user_id' => $user->id],
            [
                'balance' => $request->balance,
                'address' => $request->address,
            ],
        );
    }
}

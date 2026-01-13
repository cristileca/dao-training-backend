<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CommissionController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     */
//    public function commissions(Request $request)
//    {
//
//        Log::info("commissions request", ["request" => $request->get("userId")]);
//
//        $userId = auth()->user()->id;
//
//        $commissions = Commission::query()->whereToUserId($userId)->get();
//
//        Log::info("commissions", ["commissions" => $commissions]);
//        $total = $commissions->sum('amount');
//
//        return response()->json([
//            'total' => $total,
//            'commissions' => $commissions,
//        ]);
//    }

    public function commissions(Request $request): JsonResponse{

        $userId = $request["userId"];

        $commissions = Commission::query()->whereToUserId($userId)->where('claimed',0)->get();

        return response()->json($commissions);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function claim(Request $request): JsonResponse
    {
        $user = $request->user();

        $commission = Commission::find($request->commission);

        if (!$commission) {
            return response()->json(['message' => 'Commission not found'], 404);
        }

        if ($commission->to_user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($commission->claimed) {
            return response()->json(['message' => 'Commission already claimed'], 400);
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        $wallet->increment('balance', $commission->amount);

        $commission->claimed = true;
        $commission->save();

        return response()->json([
            'message' => 'Commission claimed successfully',
            'wallet_balance' => $wallet->balance,
        ]);
    }

    protected function claimAll(Request $request):JsonResponse
    {
        $user = $request->user();
        $commissions = Commission::query()->whereToUserId($user->id)->whereClaimed(false)->get();

        if($commissions->isEmpty()){
            return response()->json(['message' => 'No commissions to claim'], 400);
        }

        $amount = $commissions->sum('amount');
        $wallet = Wallet::where('user_id', $user->id)->first();

        if(!$wallet){
            return response()->json(['message' => 'Wallet not found'], 400);
        }
        $wallet->increment('balance', $amount);
        foreach ($commissions as $commission) {
            $commission->claimed = true;
            $commission->save();
        }

        return response()->json([
            'message' => 'Commissions claimed successfully',
        ]);
    }

}

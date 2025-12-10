<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    /**
     * @param User $user
     * @return JsonResponse
     */
    public function commissions(Request $request, User $user)
    {
        sleep(3);
        //verificare status key
        $status = $request->get('status');

        $commissions = Commission::query()->whereToUserId($user->id);

        if ($status === 'pending') {
            $commissions = $commissions->whereClaimed(false);
        }

        if ($status === 'claimed') {
            $commissions = $commissions->whereClaimed(true);
        }

        $commissions = $commissions->get();

        $total = $commissions->sum('amount');

        return response()->json([
            'total' => $total,
            'commissions' => $commissions,
        ]);
    }
}

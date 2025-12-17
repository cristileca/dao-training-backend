<?php
namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Commission;

class MlmService
{
    public function getUpline(string $userId, int $maxLevel = 9):array
    {
        $levels = [];

        $current = User::find($userId);

        for($level = 1; $level <= $maxLevel; $level++)
        {
            if(!$current || !$current->referral_id){
                break;
            }

            $current = User::find($current->referral_id);


            if(!$current){
                break;
            };

            $levels[] = [
                'level' => $level,
                'user' => $current,
            ];
        }
        return $levels;
    }

    public function distributeCommissions(string $buyerId, float $packagePrice): void
    {
        $upline = $this->getUpline($buyerId, 9);

        $platformUser = \App\Models\User::orderBy('created_at')->first();
        $platformWallet = \App\Models\Wallet::firstOrCreate(
            ['user_id' => $platformUser->id],
            ['balance' => 0]
        );

        // 85% merge direct la platforma
        $platformWallet->increment('balance', $packagePrice * 0.85);

        // 15% pentru comisioane
        $commissionPool = $packagePrice * 0.15;

        // procente per nivel
        $percentages = [
            1 => 0.07,
            2 => 0.03,
            3 => 0.02,
            4 => 0.01,
            5 => 0.01,
            6 => 0.004,
            7 => 0.004,
            8 => 0.004,
            9 => 0.004,
        ];

        $distributed = 0;

        foreach ($upline as $data) {
            $level = $data['level'];

            if (!isset($percentages[$level])) {
                continue;
            }

            $weight = $percentages[$level] / 0.15; // ex: 0.03 / 0.15 = 20%
            $commission = $commissionPool * $weight;

            $distributed += $commission;

            Commission::create([
                'from_user_id' => $buyerId,
                'to_user_id'   => $data['user']->id,
                'level'        => $level,
                'amount'       => $commission,
                'claimed'      => false,
            ]);
        }

        // daca nu s-au distribuit toate comisioanele, restul merge la wallet aplicație
        $remaining = $commissionPool - $distributed;
        if ($remaining > 0) {
            $platformWallet->increment('balance', $remaining);
        }
    }



}
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
        $upline = $this->getUpline($buyerId, 9); // ia toți uplines disponibili

        if (empty($upline)) return;

        $commissionTotal = $packagePrice * 0.15;
        $perUpline = $commissionTotal / count($upline); // împărțim uniform

        foreach ($upline as $data) {
            Commission::create([
                'from_user_id' => $buyerId,
                'to_user_id' => $data['user']->id,
                'level' => $data['level'],
                'amount' => $perUpline,
                'claimed' => false,
            ]);

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $data['user']->id],
                ['balance' => 0]
            );
            $wallet->increment('balance', $perUpline);
        }

        // platforma ia restul
        $platformWallet = Wallet::firstOrCreate(
            ['user_id' => User::first()->id],
            ['balance' => 0]
        );
        $platformWallet->increment('balance', $packagePrice * 0.85);
    }


}
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

        if (empty($upline)) return;

        $percentages = [
            1 => 0.07,
            2 => 0.03,
            3 => 0.02,
            4 => 0.01,
            5 => 0.01,
            6 => 0.005,
            7 => 0.005,
            8 => 0.005,
            9 => 0.005,
        ];

        foreach ($upline as $data) {

            $level = $data['level'];

            // If level doesn't have a defined percentage → skip
            if (!isset($percentages[$level])) {
                continue;
            }

            $commission = $packagePrice * $percentages[$level];

            // Save commission
            Commission::create([
                'from_user_id' => $buyerId,
                'to_user_id' => $data['user']->id,
                'level' => $level,
                'amount' => $commission,
                'claimed' => false,
            ]);

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $data['user']->id],
                ['balance' => 0]
            );

            $wallet->increment('balance', $commission);
        }

        $platformCommission = $packagePrice - array_reduce(
                $upline,
                fn($sum, $data) => $sum + ($percentages[$data['level']] ?? 0) * $packagePrice,
                0
            );

        $platformWallet = Wallet::firstOrCreate(
            ['user_id' => User::first()->id],
            ['balance' => 0]
        );

        $platformWallet->increment('balance', $platformCommission);
    }



}
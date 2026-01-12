<?php

namespace App\Jobs;

use App\Models\Package;
use App\Models\Transactions;
use App\Models\Trees;
use App\Models\User;
use App\Models\Volumes;
use App\Services\BC;
use App\Services\MlmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ProcessPackagePurchase implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $user_id, public string $package_id)
    {
        $this->queue = 'purchases';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        /** @var Package $package */
        $package = Package::find($this->package_id);

        /** @var User $user */
        $user = User::find($this->user_id);

        /** @var Trees $tree */
        $tree = Trees::whereUserId($this->user_id)->first();

        /** @var Volumes $userVolume */
        $userVolume = Volumes::whereUserId($user->id)->where('start', '=', $startOfMonth)->firstOrCreate(['user_id' => $user->id],
            ['start' => $startOfMonth, 'end' => $endOfMonth],
        );
        Log::info("User tree", ["tree" => $tree->user_full_tree]);


        foreach ($tree->user_full_tree as $userId) {
            /** @var Volumes $volume */
            $volume = Volumes::whereUserId($userId)->where('start', '=', $startOfMonth)->firstOrCreate(['user_id' => $userId],
                ['start' => $startOfMonth, 'end' => $endOfMonth],
            );

            $oldVolume = $userVolume->volume;

            if ($userId !== $user->id) {
                Log::info("Main user and refferals: ", ["main" => $user->id, "referals"=> $userId]);

                $volume->update([
                    'sales' => $package->price + $volume->sales,
                ]);
                $userNewVolume = $package->price + $volume->sales;

                Transactions::create([
                    'user_id' => $userId,
                    'from_user_id' => $user->id,
                    'volume_id' => $volume->id,
                    'price' => $package->price,
                    'old' => $oldVolume,
                    'new' => $userNewVolume,
                ]);

            } else {
                $volume->update([
                    'volume' => $package->price + $volume->volume
                ]);

                $userNewVolume = $package->price + $volume->volume;

                Transactions::create([
                    'user_id' => $user->id,
                    'from_user_id' => $user->id,
                    'volume_id' => $volume->id,
                    'price' => $package->price,
                    'old' => $package->price,
                    'new' => $userNewVolume,
                ]);

            }

            Log::info("User volume", [ "new volume" => $userNewVolume]);

        }

        $mlmService =  new MlmService();
        $mlmService->distributeCommissions($user->id, $package->price);


        Log::info('JOB PROCESS PACKAGE PURCHASE', ['package_id' => $package->id, 'user_id' => $user->id, 'tree' => $tree->user_full_tree]);

    }
}

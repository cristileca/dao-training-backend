<?php

namespace App\Jobs;

use App\Models\Package;
use App\Models\Trees;
use App\Models\User;
use App\Models\VolumesHistory;
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

        $package = Package::find($this->package_id);
        $user = User::find($this->user_id);

        $tree = Trees::whereUserId($this->user_id)->first();

        $userVolume = $user["volume"];
        Log::info("User tree", ["tree" => $tree->user_full_tree]);


        foreach ($tree->user_full_tree as $userId) {
            $volume = Volumes::whereUserId($userId)->where('start', '=', $startOfMonth)->firstOrCreate(['user_id' => $userId],
                ['start' => $startOfMonth, 'end' => $endOfMonth],
            );

            if($userId !== $user->id){
                $volume->update([
                    'sales' => $volume->sales + $package->price,
                ]);
            }

            $volume->update([
                'volume' => $volume->volume + $package->price
            ]);
        }

        Log::info("User volume", ["volume" => $userVolume]);

        Log::info('JOB PROCESS PACKAGE PURCHASE', ['package_id' => $package->id, 'user_id' => $user->id, 'tree' => $tree->user_full_tree]);

    }
}

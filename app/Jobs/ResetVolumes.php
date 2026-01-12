<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Volumes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;

class ResetVolumes implements ShouldQueue
{
    use Queueable, InteractsWithQueue, Dispatchable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->queue = 'reset_volumes';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $volume = Volumes::where('id', $user->id)->delete();

            Volumes::update([
                'volume' => $volume + $user->volume,
                'sales' => $volume + $user->sales,
            ]);

            $active_user = User::where('id', $user->id);

            $active_user->update(
                [
                    'volume' => 0,
                    'sales' => 0,
                ]
            );
        }
    }
}

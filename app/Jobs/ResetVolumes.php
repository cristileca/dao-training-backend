<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Volume;
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
        Volume::chunk(500, function ($volumes) {
            foreach ($volumes as $volume) {

//                $volume = Volume::firstOrCreate(
//                    ['user_id' => $user->id],
//                    ['volume' => 0, 'sales' => 0]
//                );
//
//                $volume->increment('volume', $user->volume);
//                $volume->increment('sales', $user->sales);

                $volume->update([
                    'volume' => 0,
                    'sales' => 0,
                ]);
            }
        });
    }}

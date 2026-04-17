<?php

namespace App\Console\Commands;

use App\Jobs\ResetVolumes;
use Illuminate\Console\Command;

class TestConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-console';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new ResetVolumes());
    }

    protected function schedule($schedule): void
    {
        $schedule->job(new ResetVolumes)->everyMinute();
    }
}

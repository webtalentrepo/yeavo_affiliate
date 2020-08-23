<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        \App\Console\Commands\ScoutsDataInsert::class,
        \App\Console\Commands\ChildProductsInsert::class,
        \App\Console\Commands\ShareASaleInsert::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('scoutsdata:insert')
            ->everyFourHours()
            ->runInBackground();

        $schedule->command('childproduct:insert')
            ->daily()
            ->runInBackground();

        $schedule->command('shareasale:insert')
            ->weekly()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

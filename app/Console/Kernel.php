<?php

namespace App\Console;

use App\Console\Commands\ChildProductsInsert;
use App\Console\Commands\ScoutsDataInsert;
use App\Console\Commands\ShareASaleInsert;
use App\Console\Commands\GoogleAdsAuth;
use App\Console\Commands\GoogleAdsGetToken;
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
        ScoutsDataInsert::class,
        ChildProductsInsert::class,
        ShareASaleInsert::class,
        GoogleAdsGetToken::class,
        GoogleAdsAuth::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('scoutsdata:insert')
            ->daily()
            ->runInBackground();

        $schedule->command('childproduct:insert')
            ->weekly()
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

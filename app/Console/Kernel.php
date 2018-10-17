<?php

namespace App\Console;

use App\Console\Commands\ClearStorage;
use App\Console\Commands\CreateFirstUser;
use App\Console\Commands\FixDatasetErrors;
use App\Console\Commands\SeedBrowserTests;
use App\Console\Commands\SetupTestDatabase;
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
        CreateFirstUser::class,
        SeedBrowserTests::class,
        SetupTestDatabase::class,
        ClearStorage::class,
        FixDatasetErrors::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}

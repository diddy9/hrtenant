<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ResetLeaveBalances; 

class Kernel extends ConsoleKernel
{
    
    protected $commands = [
        ResetLeaveBalances::class, // <-- Register your command here
    ];

    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('leave:reset')->daily(); // or monthly
        $schedule->command('leave:reset')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
